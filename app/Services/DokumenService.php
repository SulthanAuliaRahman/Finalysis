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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Exception;

class DokumenService
{
    private const SHEET_NERACA = 'Laporan Posisi Keuangan';
    private const SHEET_LABA_RUGI = 'Laporan Laba Rugi';

    public function importExcel(Perusahaan $perusahaan, array $data): Dokumen
    {
        // Load File
        $file = $data['file'];

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheetNeraca = $spreadsheet->getSheetByName(self::SHEET_NERACA);
        $sheetLabaRugi = $spreadsheet->getSheetByName(self::SHEET_LABA_RUGI);

        if (! $sheetNeraca || ! $sheetLabaRugi) {
            throw new Exception('Sheet "' . self::SHEET_NERACA . '" atau "' . self::SHEET_LABA_RUGI . '" tidak ditemukan.');
        }

        // Ekstraksi (Tree Traversal)
        $neracaGrouped = array_merge(
            $this->extract($sheetNeraca, ['Aset Lancar'], 'aset', 'aset_lancar'),
            $this->extract($sheetNeraca, ['Aset Tetap'], 'aset', 'aset_tetap'),
            $this->extract($sheetNeraca, ['Liabilitas Jangka Pendek', 'Liabilitas Lancar'], 'liabilitas', 'liabilitas_jangka_pendek'),
            $this->extract($sheetNeraca, ['Liabilitas Jangka Panjang', 'Liabilitas Tidak Lancar'], 'liabilitas', 'liabilitas_jangka_panjang'),
            $this->extract($sheetNeraca, ['Ekuitas'], 'ekuitas', 'ekuitas')
        );

        // dd($neracaGrouped);

        $labaRugiGrouped = array_merge(
            $this->extract($sheetLabaRugi, ['Pendapatan'], 'pendapatan', 'pendapatan'),
            $this->extract($sheetLabaRugi, ['Beban' ], 'beban', 'beban'),
            $this->extractSingleRow($sheetLabaRugi, ['Beban pajak penghasilan', 'Beban pajak', 'Pajak Penghasilan'], 'beban', 'beban_pajak')
        );

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


    private function extract(Worksheet $sheet, array $labelKelompokDicari, string $kelompok, string $subKelompok): array
    {
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

        // 3. Loop ke bawah sampai menunjuk cell kosong
        while ($baris <= $maxRow) {
            $namaAkunExcel = $sheet->getCellByColumnAndRow($kolomLabel, $baris)->getValue();

            if (empty(trim((string) $namaAkunExcel))) {
                // nama akun kosong keluar dari loop
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
        for ($baris = 1; $baris <= $maxRow; $baris++) {
            // dari baris paling atas ke bawah sampai batas maksimum baris data

            for ($kolom = 1; $kolom <= $maxCol; $kolom++) {
                // dari kolom paling kiri  ke kanan

                // Ambil nilai (value) dari cell pada koordinat kolom dan baris saat ini
                $nilaiSel = $sheet->getCellByColumnAndRow($kolom, $baris)->getValue();

                // Pengecekan kondisi:
                // 1. value cell adalah string (bukan angka/formula error)
                // 2. Gunakan stripos() untuk mengecek apakah $searchText ada di dalam $nilaiSel (case-insensitive / mengabaikan huruf besar-kecil)
                if (is_string($nilaiSel) && stripos(trim($nilaiSel), trim($searchText)) !== false) {

                    // teks cocok/ditemukan, return posisi baris & kolomnya
                    return ['row' => $baris, 'col' => $kolom];
                }
            }
        }

        // Teks Tidak ditemukan, return null
        return null;
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

    function normalizeAccountName(string $name): string
    {
        $name = strtolower(trim($name));
        // normalisasi "&" menjadi "dan"
        $name = str_replace('&', ' dan ', $name);

        // hilangkan karakter selain huruf/angka
        $name = preg_replace('/[^a-z0-9\s]/', ' ', $name);

        // rapikan whitespace
        $name = preg_replace('/\s+/', ' ', $name);

        return trim($name);
        //"Kas & bank" -> "kas dan bank"
        // teu butuh sih kayak nya
    }

    function classifyKas(array $akun): string
    {
        if ($akun['kelompok_akun'] !== 'aset' || $akun['sub_kelompok_akun'] !== 'aset_lancar') {
            return 'bukan_kas';
        }

        $nama = $this->normalizeAccountName($akun['nama_akun']);

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
