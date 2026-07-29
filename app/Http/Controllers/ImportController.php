<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpParser\Node\Expr\Cast\Void_;

class ImportController extends Controller
{
    /**
     * Nama sheet yang dibaca dari template Excel.
     * Kunci = kategori internal, Value = nama sheet persis di file Excel.
     */
    private const SHEET_NERACA = 'Laporan Posisi Keuangan';
    private const SHEET_LABA_RUGI = 'Laporan Laba Rugi';

    /**
     * Mapping "Kelompok Akun" (raw dari Excel) ke key ringkasan/total.
     * Key di sini dinormalisasi lowercase + trim untuk matching.
     */
    private const KELOMPOK_AKUN_MAP = [
        'asset lancar' => 'total_asset_lancar',
        'asset tetap' => 'total_asset_tetap',
        'liabilitas' => 'total_liabilitas',
        'ekuitas' => 'total_ekuitas',
        'pendapatan' => 'total_pendapatan',
        'beban' => 'total_beban',
    ];

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
            ],
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $neracaSheet = $spreadsheet->getSheetByName(self::SHEET_NERACA);
        $labaRugiSheet = $spreadsheet->getSheetByName(self::SHEET_LABA_RUGI);

        if (! $neracaSheet || ! $labaRugiSheet) {
            return back()->withErrors([
                'file' => sprintf(
                    'Sheet "%s" dan/atau "%s" tidak ditemukan di file Excel. Pastikan nama sheet sesuai template.',
                    self::SHEET_NERACA,
                    self::SHEET_LABA_RUGI,
                ),
            ]);
        }

        // formatData = false -> nilai numerik dikembalikan mentah (bukan string terformat Rp/koma)
        $neracaParsed = $this->parseSheet($neracaSheet->toArray(null, true, false, false));
        $labaRugiParsed = $this->parseSheet($labaRugiSheet->toArray(null, true, false, false));

        // Gabungkan seluruh baris akun dari kedua sheet, lalu kelompokkan sekaligus
        $semuaAkun = array_merge($neracaParsed['accounts'], $labaRugiParsed['accounts']);

        $grouped = $this->groupAkun($semuaAkun);

        $result = [
            'ringkasan' => $grouped['ringkasan'],
            'detail' => $grouped['detail'],
        ];

        dd($result);
    }

    /**
     * Parse satu sheet mentah (array 2D dari PhpSpreadsheet) menjadi
     * metadata perusahaan/tahun + list akun (kelompok_akun, nama_akun, nilai).
     *
     * Struktur baku template:
     * baris 1 = Nama Perusahaan
     * baris 2 = Tahun / Periode
     * baris 3 = Judul laporan
     * baris 4 = Header (Kelompok Akun, Nama Akun, Nilai)
     * baris 5+ = Data
     */
    private function parseSheet(array $sheet): array
    {
        $company = $sheet[0][1] ?? null;
        $year = $sheet[1][1] ?? null;

        $rows = array_slice($sheet, 4);

        $accounts = [];

        foreach ($rows as $index => $row) {
            $kelompokAkun = trim((string) ($row[0] ?? ''));
            $namaAkun = trim((string) ($row[1] ?? ''));

            if ($kelompokAkun === '' && $namaAkun === '') {
                continue;
            }

            $accounts[] = [
                'row' => $index + 5,
                'kelompok_akun' => $kelompokAkun,
                'nama_akun' => $namaAkun,
                'nilai' => $this->parseNilai($row[2] ?? null),
            ];
        }

        return [
            'company' => $company,
            'year' => $year,
            'accounts' => $accounts,
        ];
    }

    private function parseNilai($rawNilai): float
    {
        if (is_numeric($rawNilai)) {
            return (float) $rawNilai;
        }

        $cleaned = str_replace(['Rp', ',', ' '], '', (string) $rawNilai);

        return is_numeric($cleaned) ? (float) $cleaned : 0.0;
    }

    /**
     * Kelompokkan list akun berdasarkan Kelompok Akun menjadi:
     * - ringkasan: total per kelompok (Total Asset Lancar, Total Asset Tetap, dst)
     * - detail: list akun mentah per kelompok (untuk ditampilkan di bawah ringkasan)
     */
    private function groupAkun(array $accounts): array
    {
        $ringkasan = array_fill_keys(array_values(self::KELOMPOK_AKUN_MAP), 0.0);
        $detail = array_fill_keys(array_keys(self::KELOMPOK_AKUN_MAP), []);

        $takDikenal = []; // Untuk Fall Back Kelompok akun tidak di kenal (mungkin disempen tapi mungkin kagak)

        foreach ($accounts as $akun) {
            $normalizedKelompok = strtolower(trim($akun['kelompok_akun']));

            if (! isset(self::KELOMPOK_AKUN_MAP[$normalizedKelompok])) {
                $takDikenal[] = $akun;
                continue;
            }

            $ringkasanKey = self::KELOMPOK_AKUN_MAP[$normalizedKelompok];
            $ringkasan[$ringkasanKey] += $akun['nilai'];
            $detail[$normalizedKelompok][] = $akun;
        }

        if (! empty($takDikenal)) {
            $detail['tidak_dikenal'] = $takDikenal;
        }

        return [
            'ringkasan' => $ringkasan,
            'detail' => $detail,
        ];
    }

    private function searchLabel(Worksheet $sheet, string $label): ?string
    {
        // Iterasi setiap baris di sheet tersebut
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();

            // Abaikan sel kosong untuk mempercepat pencarian
            $cellIterator->setIterateOnlyExistingCells(true);

            // Iterasi setiap sel di dalam baris
            foreach ($cellIterator as $cell) {
                // Ambil nilai cell sebagai string
                $cellValue = trim((string) $cell->getValue());

                // stripos mengabaikan huruf besar/kecil dan mencari potongan kata
                if (stripos($cellValue, $label) !== false) {
                    // kalau mengikuti Sturktur harus nya "Value itu ada Di sebelah nya
                    return $cell->getCoordinate();
                }
            }
        }
        return null;
    }

    private function hitungTotalKelompok(){

        // Neraca
        // Total Kas (Butuh kah?)
        // Total asset Lancar
        // Total asset Tetap
        // Total Asset
        // Total Liabilitas
        // Total Modal
        //

        // Laba Rugi
        // Total Pendapatan usaha
        // Total HPP (normal nya negatif)
        // Total Beban Usaha (normal nya negatif)
        // Total Pendapatan lain lain
        // Total Biaya Lain lain (normal nya negatif)

    }

    private function hitungAgregasi(){
        // laba kotor = Pendapatan usaha + HPP
        // Laba Usaha
        // laba bersih sebelum pajak
        // laba bersih setelah pajak
    }
}
