<?php

namespace App\Services;

use App\Models\Dokumen;
use App\Models\LabaRugi;
use App\Models\Neraca;
use App\Models\ChartOfAccount;
use App\Models\Perusahaan;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Exception;

class DokumenService
{
    // Kata kunci judul laporan yang dicari di dalam cell (bukan nama sheet)
    private const KEYWORDS_NERACA = [
        'Laporan Neraca',
        'Laporan Posisi Keuangan',
    ];

    private const KEYWORDS_LABA_RUGI = [
        'Laporan Laba Rugi',
        'Laporan Laba/Rugi',
    ];

    // Label generik dipakai sebagai fallback kalau sub-kelompok detail tidak ditemukan
    private const LABEL_ASET_GENERIK = ['ASET', 'Aset'];
    private const LABEL_LIABILITAS_GENERIK = ['LIABILITAS', 'Liabilitas', 'Kewajiban'];

    // Dipakai sebagai "rambu berhenti" saat fallback generik membaca ke bawah,
    // supaya tidak ikut membaca section lain yang sudah ditemukan lewat pencarian spesifik.
    private const SEMUA_LABEL_HEADER = [
        'Aset Lancar', 'Aset Tetap', 'ASET', 'Aset',
        'Liabilitas Jangka Pendek', 'Liabilitas Lancar',
        'Liabilitas Jangka Panjang', 'Liabilitas Tidak Lancar',
        'LIABILITAS', 'Liabilitas', 'Kewajiban',
        'Ekuitas', 'Pendapatan', 'Beban',
    ];

    public function importExcel(Perusahaan $perusahaan, array $data): Dokumen
    {
        // Load File
        $file = $data['file'];

        $spreadsheet = IOFactory::load($file->getRealPath());

        [$sheetNeraca, $sheetLabaRugi] = $this->resolveSheets($spreadsheet);

        // Ekstraksi Aset (Aset Lancar & Aset Tetap dicek independen, masing-masing punya fallback sendiri)
        $asetGrouped = $this->extractAsetDenganFallback($sheetNeraca);

        // Ekstraksi Liabilitas (Jk. Pendek & Jk. Panjang dicek independen)
        $liabilitasGrouped = $this->extractLiabilitasDenganFallback($sheetNeraca);

        $neracaGrouped = array_merge(
            $asetGrouped,
            $liabilitasGrouped,
            $this->extract($sheetNeraca, ['Ekuitas'], 'ekuitas', 'ekuitas')
        );

        $labaRugiGrouped = array_merge(
            $this->extract($sheetLabaRugi, ['Pendapatan'], 'pendapatan', 'pendapatan'),
            $this->extract($sheetLabaRugi, ['Beban'], 'beban', 'beban'),
            $this->extractSingleRow($sheetLabaRugi, ['Beban pajak penghasilan', 'Beban pajak', 'Pajak Penghasilan'], 'beban', 'beban_pajak')
        );

        // Validasi kelengkapan: Aset, Liabilitas, Ekuitas, Pendapatan, Beban wajib ada isinya
        $this->validateKelengkapanData($neracaGrouped, $labaRugiGrouped);

        $totalNeraca = $this->hitungTotalKelompokNeraca($neracaGrouped);
        $totalLabaRugi = $this->hitungTotalKelompokLabaRugi($labaRugiGrouped);

        return DB::transaction(function () use ($perusahaan, $data, $file, $neracaGrouped, $labaRugiGrouped, $totalNeraca, $totalLabaRugi) {
            $dokumen = Dokumen::create([
                'perusahaan_id' => $perusahaan->id,
                'nama_file'     => $file->getClientOriginalName(),
                'storage_path'  => $file->store('dokumen-import', 'public'),
                'periode_type'  => $data['periode_type'],
                'tahun'         => $data['tahun'],
                'quarter'       => $data['periode_type'] === 'quarterly' ? $data['quarter'] : null,
                'bulan'         => $data['periode_type'] === 'monthly' ? $data['bulan'] : null,
            ]);

            Neraca::create(array_merge(['dokumen_id' => $dokumen->id], $totalNeraca));
            LabaRugi::create(array_merge(['dokumen_id' => $dokumen->id], $totalLabaRugi));

            $seluruhAkun = array_merge($neracaGrouped, $labaRugiGrouped);

            foreach ($seluruhAkun as $akun) {
                ChartOfAccount::create([
                    'dokumen_id'        => $dokumen->id,
                    'nama_akun'         => $akun['nama_akun'],
                    'kelompok_akun'     => $akun['kelompok_akun'],
                    'sub_kelompok_akun' => $akun['sub_kelompok_akun'],
                    'nilai_akun'        => $akun['nilai'],
                ]);
            }

            return $dokumen;
        });
    }


    // Cari sheet Neraca & Laba Rugi berdasarkan isi cell (judul laporan),


    private function resolveSheets(Spreadsheet $spreadsheet): array
    {
        $sheetNeraca = $this->findSheetByKeywords($spreadsheet, self::KEYWORDS_NERACA);
        $sheetLabaRugi = $this->findSheetByKeywords($spreadsheet, self::KEYWORDS_LABA_RUGI);

        if (!$sheetNeraca || !$sheetLabaRugi) {
            $missing = [];
            if (!$sheetNeraca) $missing[] = 'Laporan Posisi Keuangan / Neraca';
            if (!$sheetLabaRugi) $missing[] = 'Laporan Laba Rugi';

            throw new Exception(
                'Sheet ' . implode(' dan ', $missing) . ' tidak ditemukan. '
                . 'Pastikan file berisi judul laporan yang sesuai (mis. "Laporan Posisi Keuangan" atau "Laporan Laba Rugi") di dalam salah satu sheet.'
            );
        }

        return [$sheetNeraca, $sheetLabaRugi];
    }

    private function findSheetByKeywords(Spreadsheet $spreadsheet, array $keywords): ?Worksheet
    {
        $normalizedKeywords = array_map(fn ($k) => $this->normalizeLabel($k), $keywords);

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $maxRow = min($sheet->getHighestDataRow(), 10); // judul laporan biasanya ada di baris-baris awal jadi cuman 10 baris pertama saja yang dicek
            $maxCol = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

            for ($baris = 1; $baris <= $maxRow; $baris++) {
                for ($kolom = 1; $kolom <= $maxCol; $kolom++) {
                    $nilaiSel = $sheet->getCellByColumnAndRow($kolom, $baris)->getValue();

                    if (!is_string($nilaiSel)) {
                        continue;
                    }

                    $normalizedCell = $this->normalizeLabel($nilaiSel);

                    foreach ($normalizedKeywords as $keyword) {
                        if ($keyword !== '' && str_contains($normalizedCell, $keyword)) {
                            return $sheet;
                        }
                    }
                }
            }
        }

        return null;
    }

    // Ekstrak Aset: Aset Lancar & Aset Tetap dicek INDEPENDEN.
    // Kalau salah satu (biasanya Aset Lancar) tidak ketemu lewat label spesifiknya,
    // baru fallback ke header generik "ASET" -> dianggap aset_lancar.

    private function extractAsetDenganFallback(Worksheet $sheetNeraca): array
    {
        $asetLancar = $this->extract($sheetNeraca, ['Aset Lancar'], 'aset', 'aset_lancar');
        $asetTetap  = $this->extract($sheetNeraca, ['Aset Tetap'], 'aset', 'aset_tetap');

        if (empty($asetLancar)) {
            $asetLancar = $this->extract(
                $sheetNeraca,
                self::LABEL_ASET_GENERIK,
                'aset',
                'aset_lancar',
                self::SEMUA_LABEL_HEADER
            );
        }

        // Catatan: kalau Aset Tetap tidak ketemu, TIDAK di-fallback ke generik, tidak semua perusahaan punya aset tetap, jadi kosong di sini valid (bukan error).

        return array_merge($asetLancar, $asetTetap);
    }


    // Ekstrak Liabilitas: Jk. Pendek & Jk. Panjang dicek INDEPENDEN.
    // Kalau Jk. Pendek tidak ketemu, fallback ke header generik "LIABILITAS" -> dianggap liabilitas_jangka_pendek.

    private function extractLiabilitasDenganFallback(Worksheet $sheetNeraca): array
    {
        $liabPendek  = $this->extract($sheetNeraca, ['Liabilitas Jangka Pendek', 'Liabilitas Lancar'], 'liabilitas', 'liabilitas_jangka_pendek');
        $liabPanjang = $this->extract($sheetNeraca, ['Liabilitas Jangka Panjang', 'Liabilitas Tidak Lancar'], 'liabilitas', 'liabilitas_jangka_panjang');

        if (empty($liabPendek)) {
            $liabPendek = $this->extract(
                $sheetNeraca,
                self::LABEL_LIABILITAS_GENERIK,
                'liabilitas',
                'liabilitas_jangka_pendek',
                self::SEMUA_LABEL_HEADER
            );
        }

        return array_merge($liabPendek, $liabPanjang);
    }

    // Pastikan kelima kelompok akun (Aset, Liabilitas, Ekuitas, Pendapatan, Beban) masing-masing punya minimal 1 baris ter-ekstrak. Kalau ada yang kosong, dianggap
    // data tidak lengkap dan proses import dibatalkan.

    private function validateKelengkapanData(array $neracaGrouped, array $labaRugiGrouped): void
    {
        $adaKelompok = function (array $daftarAkun, string $kelompok): bool {
            foreach ($daftarAkun as $akun) {
                if ($akun['kelompok_akun'] === $kelompok) {
                    return true;
                }
            }
            return false;
        };

        $kelompokWajib = [
            'Aset'       => $adaKelompok($neracaGrouped, 'aset'),
            // 'Liabilitas' => $adaKelompok($neracaGrouped, 'liabilitas'),  // suatu perusahaan bisa aja tidak punya liabilitas, jadi tidak wajib
            'Ekuitas'    => $adaKelompok($neracaGrouped, 'ekuitas'),  // kalem kalau usaha misal seluruhnya di danai dari utang(liabilitas) bisa tidak? Penghoetank handal misal nya
            'Pendapatan' => $adaKelompok($labaRugiGrouped, 'pendapatan'),
            'Beban'      => $adaKelompok($labaRugiGrouped, 'beban'),
        ];

        $kelompokKosong = array_keys(array_filter($kelompokWajib, fn ($ada) => !$ada));

        if (!empty($kelompokKosong)) {
            throw new Exception(
                'Data laporan keuangan tidak lengkap. Kelompok akun berikut tidak berhasil diekstrak: '
                . implode(', ', $kelompokKosong) . '. Periksa kembali format file yang diunggah.'
            );
        }
    }

    private function extract(
        Worksheet $sheet,
        array $labelKelompokDicari,
        string $kelompok,
        string $subKelompok,
        array $stopLabels = []
    ): array {
        $results = [];
        $maxRow = $sheet->getHighestDataRow();
        $maxCol = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        // 1. Cari cell tempat Label berada
        $startCell = null;

        foreach ($labelKelompokDicari as $label) {
            $startCell = $this->findCellByText($sheet, $label, $maxRow, $maxCol);

            if ($startCell != null) {
                // terdapat akun
                break;
            }
        }

        if (!$startCell) return [];

        // 2. Tentukan posisi mulai akun (Baris di bawahnya, dan kolom di kanannya) misal A5 -> B6
        $baris = $startCell['row'] + 1;
        $kolomLabel = $startCell['col'] + 1;

        // 3. Loop ke bawah sampai menunjuk cell kosong (atau menabrak header section lain)
        while ($baris <= $maxRow) {
            $namaAkunExcel = $sheet->getCellByColumnAndRow($kolomLabel, $baris)->getValue();

            if (empty(trim((string) $namaAkunExcel))) {
                // nama akun kosong keluar dari loop
                break;
            }

            // Ketemu header section lain (mis. "Aset Tetap") saat fallback generik -> berhenti
            if ($this->isHeaderLabel((string) $namaAkunExcel, $stopLabels)) {
                break;
            }

            // 4. Traversal ke kanan mencari angka (Nilai Akun)
            $nilaiAkun = 0;

            for ($kolomIndeks = $kolomLabel + 1; $kolomIndeks <= $maxCol; $kolomIndeks++) {
                $nilaiSel = $sheet->getCellByColumnAndRow($kolomIndeks, $baris)->getCalculatedValue();

                // nilainya > 100 menghindari angka catatan
                if (is_numeric($nilaiSel) && abs((float)$nilaiSel) > 100) {
                    // angka ketemu
                    $nilaiAkun = (float)$nilaiSel;
                    break;
                }
            }

            // map array klasifikasi akun
            $item = [
                'nama_akun'         => trim((string)$namaAkunExcel),
                'kelompok_akun'     => $kelompok,
                'sub_kelompok_akun' => $subKelompok,
                'nilai'             => $nilaiAkun,
            ];

            // klasifikasi ulang untuk aset_lancar menjadi kas_setara_kas dan aset_lancar_selain_kas
            if ($subKelompok === 'aset_lancar') {
                $item['sub_kelompok_akun'] = $this->classifyKas($item);
            }

            // Masukkan item yang sudah difilter ke dalam hasil
            $results[] = $item;

            $baris++;
        }

        return $results;
    }

    // Untuk Akun beban pajak beda sendiri soalnya kalau dalam format cuman ke kanan saja (kayak nya pasti 1 deh)
    private function extractSingleRow(Worksheet $sheet, array $labelKelompokDicari, string $kelompok, string $subKelompok): array
    {
        $maxRow = $sheet->getHighestDataRow();
        $maxCol = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        $startCell = null;
        foreach ($labelKelompokDicari as $label) {
            $startCell = $this->findCellByText($sheet, $label, $maxRow, $maxCol);

            if ($startCell != null) {
                // terdapat akun
                break;
            }
        }

        if (!$startCell) return [];

        $baris = $startCell['row'];
        $kolomLabel = $startCell['col'];
        $namaAkunExcel = $sheet->getCellByColumnAndRow($kolomLabel, $baris)->getValue();

        // Traversal ke kanan
        $nilaiAkun = 0;
        for ($kolomIndeks = $kolomLabel + 1; $kolomIndeks <= $maxCol; $kolomIndeks++) {
            $nilaiSel = $sheet->getCellByColumnAndRow($kolomIndeks, $baris)->getCalculatedValue();

            if (is_numeric($nilaiSel) && abs((float)$nilaiSel) > 100) { // x>100 biar menghindari angka catatan
                // ketemu angka numerik
                $nilaiAkun = (float)$nilaiSel;
                break;
            }
        }

        return [[
            'nama_akun'         => trim((string)$namaAkunExcel),
            'kelompok_akun'     => $kelompok,
            'sub_kelompok_akun' => $subKelompok,
            'nilai'             => $nilaiAkun,
        ]];
    }

    private function findCellByText(Worksheet $sheet, string $searchText, int $maxRow, int $maxCol): ?array
    {
        $normalizedSearch = $this->normalizeLabel($searchText);

        if ($normalizedSearch == '') {
            return null;
        }

        for ($baris = 1; $baris <= $maxRow; $baris++) {
            // dari baris paling atas ke bawah sampai batas maksimum baris data

            for ($kolom = 1; $kolom <= $maxCol; $kolom++) {
                // dari kolom paling kiri ke kanan

                $nilaiSel = $sheet->getCellByColumnAndRow($kolom, $baris)->getValue();

                if (!is_string($nilaiSel)) {
                    continue;
                }

                // Pencocokan case-insensitive & tidak sensitif spasi/tanda baca (lihat normalizeLabel)
                if (str_contains($this->normalizeLabel($nilaiSel), $normalizedSearch)) {
                    // teks cocok/ditemukan, return posisi baris & kolomnya
                    return ['row' => $baris, 'col' => $kolom];
                }
            }
        }

        // Teks Tidak ditemukan, return null
        return null;
    }

    private function isHeaderLabel(string $text, array $stopLabels): bool
    {
        if (empty($stopLabels)) {
            return false;
        }

        $normalizedText = $this->normalizeLabel($text);

        foreach ($stopLabels as $label) {
            if ($normalizedText === $this->normalizeLabel($label)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeLabel(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/\s+/', ' ', $text);   // rapikan spasi ganda/tab/newline
        $text = trim($text, " \t\n\r\0\x0B:.-");      // buang tanda baca umum di ujung
        return $text;
    }

    // AGREGASI
    private function sumWhere(array $daftarAkun, callable $kondisi): float
    {
        return array_sum(array_map(
            fn ($akun) => $akun['nilai'], array_filter($daftarAkun, fn ($akun) => $kondisi($akun))
        ));
    }

    private function hitungTotalKelompokNeraca(array $neracaGrouped): array
    {
        $totalKas = $this->sumWhere($neracaGrouped, fn ($akun) => $akun['sub_kelompok_akun'] === 'kas_setara_kas');

        // Total Aset Lancar gabungan subkelompok 'kas_setara_kas' dan 'aset_lancar_selain_kas'
        $totalAsetLancar = $this->sumWhere($neracaGrouped, fn ($akun) => in_array($akun['sub_kelompok_akun'], ['kas_setara_kas', 'aset_lancar_selain_kas']));

        $totalAsetTetap = $this->sumWhere($neracaGrouped, fn ($akun) => $akun['sub_kelompok_akun'] === 'aset_tetap');

        // Penjumlahan simpel, tinggal panggil string enum-nya
        $totalLiabPendek = $this->sumWhere($neracaGrouped, fn ($akun) => $akun['sub_kelompok_akun'] === 'liabilitas_jangka_pendek');
        $totalLiabPanjang = $this->sumWhere($neracaGrouped, fn ($akun) => $akun['sub_kelompok_akun'] === 'liabilitas_jangka_panjang');
        $totalEkuitas = $this->sumWhere($neracaGrouped, fn ($akun) => $akun['sub_kelompok_akun'] === 'ekuitas');

        return [
            'total_kas_setara_kas'      => $totalKas,
            'total_asset_lancar'        => $totalAsetLancar,
            'total_asset_tetap'         => $totalAsetTetap,
            'total_asset'               => $totalAsetLancar + $totalAsetTetap,
            'total_liabilities_pendek'  => $totalLiabPendek,
            'total_liabilities_panjang' => $totalLiabPanjang,
            'total_liabilities'         => $totalLiabPendek + $totalLiabPanjang,
            'total_equitas'             => $totalEkuitas,
        ];
    }

    private function hitungTotalKelompokLabaRugi(array $labaRugiGrouped): array
    {
        $totalPendapatan = $this->sumWhere($labaRugiGrouped, fn ($akun) => $akun['sub_kelompok_akun'] === 'pendapatan');
        $totalBeban = $this->sumWhere($labaRugiGrouped, fn ($akun) => $akun['sub_kelompok_akun'] === 'beban');
        $totalBebanPajak = $this->sumWhere($labaRugiGrouped, fn ($akun) => $akun['sub_kelompok_akun'] === 'beban_pajak');

        $labaBersihSebelumPajak = $totalPendapatan + $totalBeban; // Beban biasanya bernilai negatif dari Excel
        $labaBersihSesudahPajak = $labaBersihSebelumPajak + $totalBebanPajak;

        return [
            'total_beban'               => $totalBeban,
            'total_biaya_pajak'         => $totalBebanPajak,
            'total_pendapatan'          => $totalPendapatan,
            'laba_bersih_sebelum_pajak' => $labaBersihSebelumPajak,
            'laba_bersih_sesudah_pajak' => $labaBersihSesudahPajak,
        ];
    }

    function classifyKas(array $akun): string
    {
        if ($akun['kelompok_akun'] !== 'aset' || $akun['sub_kelompok_akun'] !== 'aset_lancar') {
            return 'bukan_kas';
        }

        $nama = $this->normalizeLabel($akun['nama_akun']);

        // Jika di dalam nama akun terdapat kata 'kas', 'bank', 'giro', 'deposito', atau 'tabungan'
        if (
            str_contains($nama, 'kas') || str_contains($nama, 'bank') ||
            str_contains($nama, 'giro') || str_contains($nama, 'deposito') ||
            str_contains($nama, 'tabungan')
        ) {
            return 'kas_setara_kas';
        }

        return 'aset_lancar_selain_kas';
    }
}
