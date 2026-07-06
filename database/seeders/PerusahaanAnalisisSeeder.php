<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PerusahaanAnalisisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data lama sesuai urutan dependensi (anak dulu, baru induk)
        DB::table('chunks')->delete();
        DB::table('analisis_trend_periode')->delete();
        DB::table('analisis_trend')->delete();
        DB::table('analisis_commonsize')->delete();
        DB::table('analisis_dupont')->delete();
        DB::table('analisis_aktivitas')->delete();
        DB::table('analisis_solvabilitas')->delete();
        DB::table('analisis_profitabilitas')->delete();
        DB::table('analisis_likuiditas')->delete();
        DB::table('analisis')->delete();
        DB::table('arus_kas')->delete();
        DB::table('laba_rugi')->delete();
        DB::table('neraca')->delete();
        DB::table('dokumen')->delete();
        DB::table('perusahaan')->delete();

        $now = Carbon::now();

        // ---------- 1. Perusahaan ----------
        $perusahaanId = DB::table('perusahaan')->insertGetId([
            'nama'       => 'PT Bumi Sejahtera Tbk',
            'sektor'     => 'Manufaktur',
            'deskripsi'  => 'Perusahaan manufaktur alat berat dan komponen industri.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // ---------- 2. Data keuangan tetap (tidak random) ----------
        $periodeList = [
            [
                'periode_type' => 'annual', 'tahun' => 2023, 'quarter' => null, 'bulan' => null,
                'total_assets' => 45_000_000_000, 'current_assets' => 18_000_000_000,
                'persediaan' => 5_000_000_000, 'kas_setara_kas' => 4_000_000_000,
                'total_liabilities' => 22_000_000_000, 'current_liabilities' => 9_000_000_000,
                'pendapatan' => 52_000_000_000, 'laba_kotor' => 18_000_000_000, 'laba_bersih' => 4_500_000_000,
                'kas_masuk' => 53_000_000_000, 'kas_keluar' => 47_000_000_000,
            ],
            [
                'periode_type' => 'quarterly', 'tahun' => 2024, 'quarter' => 1, 'bulan' => null,
                'total_assets' => 46_500_000_000, 'current_assets' => 18_500_000_000,
                'persediaan' => 5_100_000_000, 'kas_setara_kas' => 4_100_000_000,
                'total_liabilities' => 22_200_000_000, 'current_liabilities' => 9_100_000_000,
                'pendapatan' => 13_500_000_000, 'laba_kotor' => 4_700_000_000, 'laba_bersih' => 1_100_000_000,
                'kas_masuk' => 13_800_000_000, 'kas_keluar' => 12_200_000_000,
            ],
            [
                'periode_type' => 'quarterly', 'tahun' => 2024, 'quarter' => 2, 'bulan' => null,
                'total_assets' => 47_200_000_000, 'current_assets' => 18_900_000_000,
                'persediaan' => 5_200_000_000, 'kas_setara_kas' => 4_200_000_000,
                'total_liabilities' => 22_400_000_000, 'current_liabilities' => 9_200_000_000,
                'pendapatan' => 14_200_000_000, 'laba_kotor' => 5_000_000_000, 'laba_bersih' => 1_250_000_000,
                'kas_masuk' => 14_500_000_000, 'kas_keluar' => 12_700_000_000,
            ],
            [
                'periode_type' => 'quarterly', 'tahun' => 2024, 'quarter' => 3, 'bulan' => null,
                'total_assets' => 48_100_000_000, 'current_assets' => 19_400_000_000,
                'persediaan' => 5_350_000_000, 'kas_setara_kas' => 4_350_000_000,
                'total_liabilities' => 22_700_000_000, 'current_liabilities' => 9_350_000_000,
                'pendapatan' => 14_900_000_000, 'laba_kotor' => 5_300_000_000, 'laba_bersih' => 1_350_000_000,
                'kas_masuk' => 15_200_000_000, 'kas_keluar' => 13_300_000_000,
            ],
            [
                'periode_type' => 'quarterly', 'tahun' => 2024, 'quarter' => 4, 'bulan' => null,
                'total_assets' => 49_500_000_000, 'current_assets' => 20_000_000_000,
                'persediaan' => 5_500_000_000, 'kas_setara_kas' => 4_500_000_000,
                'total_liabilities' => 23_000_000_000, 'current_liabilities' => 9_500_000_000,
                'pendapatan' => 15_400_000_000, 'laba_kotor' => 5_500_000_000, 'laba_bersih' => 1_500_000_000,
                'kas_masuk' => 15_500_000_000, 'kas_keluar' => 13_800_000_000,
            ],
            [
                'periode_type' => 'annual', 'tahun' => 2024, 'quarter' => null, 'bulan' => null,
                'total_assets' => 49_500_000_000, 'current_assets' => 20_000_000_000,
                'persediaan' => 5_500_000_000, 'kas_setara_kas' => 4_500_000_000,
                'total_liabilities' => 23_000_000_000, 'current_liabilities' => 9_500_000_000,
                'pendapatan' => 58_000_000_000, 'laba_kotor' => 20_500_000_000, 'laba_bersih' => 5_200_000_000,
                'kas_masuk' => 59_000_000_000, 'kas_keluar' => 52_000_000_000,
            ],
        ];

        // Menampung ringkasan tiap periode yang sudah diinsert, dipakai lagi
        // di pass kedua (trend) supaya tidak perlu query ulang ke DB.
        $riwayatAnalisis = [];

        foreach ($periodeList as $periode) {
            $labelPeriode = $this->labelPeriode($periode);

            $totalAssets        = $periode['total_assets'];
            $currentAssets      = $periode['current_assets'];
            $persediaan         = $periode['persediaan'];
            $kasSetaraKas       = $periode['kas_setara_kas'];
            $totalLiabilities   = $periode['total_liabilities'];
            $currentLiabilities = $periode['current_liabilities'];
            $totalEquity        = $totalAssets - $totalLiabilities;

            $pendapatan  = $periode['pendapatan'];
            $labaKotor   = $periode['laba_kotor'];
            $labaBersih  = $periode['laba_bersih'];

            $kasMasuk  = $periode['kas_masuk'];
            $kasKeluar = $periode['kas_keluar'];
            $netCashFlow = $kasMasuk - $kasKeluar;

            // ---------- 3. Dokumen ----------
            $dokumenId = DB::table('dokumen')->insertGetId([
                'perusahaan_id'    => $perusahaanId,
                'nama_file'        => "Laporan Keuangan {$labelPeriode} - PT Bumi Sejahtera Tbk.pdf",
                'storage_path'     => 'dokumen/' . $perusahaanId . '/' . str()->slug($labelPeriode) . '.pdf',
                'periode_type'     => $periode['periode_type'],
                'tahun'            => $periode['tahun'],
                'quarter'          => $periode['quarter'],
                'bulan'            => $periode['bulan'],
                'statement_types'  => json_encode(['balance_sheet', 'income_statement', 'cash_flow']),
                'ukuran_file'      => 1_250_000,
                'status'           => 'selesai',
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

            // ---------- 4. Neraca, Laba Rugi, Arus Kas ----------
            // PENTING: kolom neraca sekarang pakai nama Inggris (migration sudah diubah teman Anda)
            DB::table('neraca')->insert([
                'dokumen_id'          => $dokumenId,
                'cash_equivalent'     => $kasSetaraKas,   // dulu 'kas_setara_kas'
                'inventory'           => $persediaan,      // dulu 'persediaan'
                'total_equity'        => $totalEquity,
                'total_liabilities'   => $totalLiabilities,
                'current_liabilities' => $currentLiabilities,
                'total_assets'        => $totalAssets,
                'current_assets'      => $currentAssets,
                'found_at'            => json_encode(['halaman' => 3]),
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);

            DB::table('laba_rugi')->insert([
                'dokumen_id'  => $dokumenId,
                'pendapatan'  => $pendapatan,
                'laba_kotor'  => $labaKotor,
                'laba_bersih' => $labaBersih,
                'found_at'    => json_encode(['halaman' => 7]),
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            DB::table('arus_kas')->insert([
                'dokumen_id' => $dokumenId,
                'kas_masuk'  => $kasMasuk,
                'kas_keluar' => $kasKeluar,
                // cash_flow_from_operations/investing/financing dibiarkan null,
                // tidak dipakai di alur analisis kita saat ini.
                'found_at'   => json_encode(['halaman' => 11]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // ---------- 5. Analisis ----------
            $analisisId = DB::table('analisis')->insertGetId([
                'perusahaan_id'      => $perusahaanId,
                'periode_type'       => $periode['periode_type'],
                'tahun'              => $periode['tahun'],
                'quarter'            => $periode['quarter'],
                'bulan'              => $periode['bulan'],
                'status'             => 'sudah dianalisis',
                'AI_summary_insight' => "Secara umum, kinerja keuangan PT Bumi Sejahtera Tbk pada periode {$labelPeriode} menunjukkan kondisi yang "
                    . ($labaBersih > 0 ? 'positif dengan profitabilitas yang terjaga' : 'perlu perhatian pada sisi profitabilitas')
                    . ", didukung oleh struktur likuiditas dan solvabilitas yang relatif stabil dibandingkan periode sebelumnya.",
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // ---------- Likuiditas (semua dikali 100, satuan %) ----------
            $currentRatio = round(($currentAssets / $currentLiabilities) * 100, 2);
            $quickRatio   = round((($currentAssets - $persediaan) / $currentLiabilities) * 100, 2);
            $cashRatio    = round(($kasSetaraKas / $currentLiabilities) * 100, 2);

            DB::table('analisis_likuiditas')->insert([
                'analisis_id'          => $analisisId,
                'current_ratio'        => $currentRatio,
                'quick_ratio'          => $quickRatio,
                'cash_ratio'           => $cashRatio,
                'narasi_likuiditas_AI' => "Current ratio sebesar {$currentRatio}% menunjukkan kemampuan perusahaan dalam memenuhi kewajiban jangka pendek "
                    . ($currentRatio >= 150 ? 'sangat baik.' : 'cukup memadai namun perlu dipantau.'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // ---------- Profitabilitas (semua dikali 100, satuan %) ----------
            $roe             = round(($labaBersih / $totalEquity) * 100, 2);
            $roa             = round(($labaBersih / $totalAssets) * 100, 2);
            $netProfitMargin = round(($labaBersih / $pendapatan) * 100, 2);

            DB::table('analisis_profitabilitas')->insert([
                'analisis_id'              => $analisisId,
                'ROE'                      => $roe,
                'ROA'                      => $roa,
                'net_profit_margin'        => $netProfitMargin,
                'narasi_profitabilitas_AI' => "ROE sebesar {$roe}% dan net profit margin sebesar {$netProfitMargin}% mengindikasikan efisiensi perusahaan dalam menghasilkan laba dari modal dan penjualan.",
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // ---------- Solvabilitas (semua dikali 100, satuan %) ----------
            $debtToEquity = round(($totalLiabilities / $totalEquity) * 100, 2);
            $debtToAsset  = round(($totalLiabilities / $totalAssets) * 100, 2);

            DB::table('analisis_solvabilitas')->insert([
                'analisis_id'            => $analisisId,
                'debt_to_equity'         => $debtToEquity,
                'debt_to_asset'          => $debtToAsset,
                'narasi_solvabilitas_AI' => "Rasio debt to asset sebesar {$debtToAsset}% menunjukkan proporsi aset yang dibiayai oleh utang berada pada tingkat "
                    . ($debtToAsset <= 50 ? 'yang sehat.' : 'yang perlu diwaspadai.'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // ---------- Aktivitas — TATO satuan "x", TIDAK dikali 100 ----------
            $totalAssetTurnover = round($pendapatan / $totalAssets, 2);

            DB::table('analisis_aktivitas')->insert([
                'analisis_id'          => $analisisId,
                'total_asset_turnover' => $totalAssetTurnover,
                'narasi_aktivitas_AI'  => "Total asset turnover sebesar {$totalAssetTurnover}x mencerminkan efisiensi perusahaan dalam memanfaatkan asetnya untuk menghasilkan pendapatan.",
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // ---------- DuPont ----------
            // netProfitMargin di atas SUDAH dalam bentuk persen (mis. 9.74), bukan pecahan.
            // TATO & Leverage tetap satuan "x", tidak dikali 100.
            $leverageMultiplier = round($totalAssets / $totalEquity, 2);
            $roeDupont          = round($netProfitMargin * $totalAssetTurnover * $leverageMultiplier, 2);

            DB::table('analisis_dupont')->insert([
                'analisis_id'          => $analisisId,
                'net_profit_margin'    => $netProfitMargin,
                'total_asset_turnover' => $totalAssetTurnover,
                'leverage_multiplier'  => $leverageMultiplier,
                'roe'                  => $roeDupont,
                'narasi_dupont_AI'     => "ROE sebesar {$roeDupont}% dibentuk dari perkalian NPM {$netProfitMargin}%, TATO {$totalAssetTurnover}x, dan leverage {$leverageMultiplier}x. Singkatnya: performa ROE saat ini "
                    . ($totalAssetTurnover >= 1.0 ? "lebih banyak ditopang oleh kecepatan perputaran aset." : "lebih banyak ditopang oleh margin keuntungan, bukan kecepatan perputaran aset."),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // ---------- Common-Size ----------
            $hpp            = $pendapatan - $labaKotor;
            $bebanLainPajak = $labaKotor - $labaBersih;
            $asetTetap         = $totalAssets - $currentAssets;
            $liabilitasPanjang = $totalLiabilities - $currentLiabilities;

            $hppPersen            = round(($hpp / $pendapatan) * 100, 2);
            $labaKotorPersen      = round(($labaKotor / $pendapatan) * 100, 2);
            $bebanLainPajakPersen = round(($bebanLainPajak / $pendapatan) * 100, 2);
            $labaBersihPersen     = round(($labaBersih / $pendapatan) * 100, 2);
            $asetLancarPersen        = round(($currentAssets / $totalAssets) * 100, 2);
            $asetTetapPersen         = round(($asetTetap / $totalAssets) * 100, 2);
            $liabilitasLancarPersen  = round(($currentLiabilities / $totalAssets) * 100, 2);
            $liabilitasPanjangPersen = round(($liabilitasPanjang / $totalAssets) * 100, 2);
            $ekuitasPersen           = round(($totalEquity / $totalAssets) * 100, 2);

            DB::table('analisis_commonsize')->insert([
                'analisis_id'               => $analisisId,
                'hpp_persen'                => $hppPersen,
                'laba_kotor_persen'         => $labaKotorPersen,
                'beban_lain_pajak_persen'   => $bebanLainPajakPersen,
                'laba_bersih_persen'        => $labaBersihPersen,
                'aset_lancar_persen'        => $asetLancarPersen,
                'aset_tetap_persen'         => $asetTetapPersen,
                'liabilitas_lancar_persen'  => $liabilitasLancarPersen,
                'liabilitas_panjang_persen' => $liabilitasPanjangPersen,
                'ekuitas_persen'            => $ekuitasPersen,
                'narasi_commonsize_AI'      => "HPP menyerap {$hppPersen}% dari pendapatan, menyisakan Laba Kotor {$labaKotorPersen}%. "
                    . "Setelah beban lain-lain & pajak ({$bebanLainPajakPersen}%), Laba Bersih akhir {$labaBersihPersen}%. "
                    . "Struktur neraca didominasi oleh " . ($asetTetapPersen > $asetLancarPersen ? 'aset tetap' : 'aset lancar')
                    . " ({$asetTetapPersen}% vs {$asetLancarPersen}%), dengan ekuitas menopang {$ekuitasPersen}% dari total aset.",
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Simpan ringkasan untuk pass kedua (Trend)
            $riwayatAnalisis[] = [
                'analisis_id'    => $analisisId,
                'periode_type'   => $periode['periode_type'],
                'tahun'          => $periode['tahun'],
                'quarter'        => $periode['quarter'],
                'pendapatan'     => $pendapatan,
                'laba_kotor'     => $labaKotor,
                'laba_bersih'    => $labaBersih,
                'total_assets'   => $totalAssets,
                'kas_setara_kas' => $kasSetaraKas,
                'total_equity'   => $totalEquity,
                'net_cash_flow'  => $netCashFlow,

                // Rasio (untuk narasi Tren Rasio) — sudah dalam bentuk persen
                'current_ratio'  => $currentRatio,
                'roe'            => $roe,

                // DuPont (untuk narasi Tren DuPont) — sudah dalam bentuk persen
                'leverage_multiplier' => $leverageMultiplier,
                'roe_dupont'          => $roeDupont,

                // Common-size (untuk narasi Tren Common-size)
                'hpp_persen'         => $hppPersen,
                'laba_bersih_persen' => $labaBersihPersen,
            ];
        }

        // ---------- 6. Trend (pass kedua, butuh seluruh riwayat di atas) ----------
        foreach ($riwayatAnalisis as $index => $current) {
            $scopeData = array_filter($riwayatAnalisis, function ($item) use ($current) {
                if ($item['periode_type'] !== $current['periode_type']) {
                    return false;
                }
                if ($current['periode_type'] === 'quarterly') {
                    return $item['tahun'] === $current['tahun'] && $item['quarter'] <= $current['quarter'];
                }
                return $item['tahun'] <= $current['tahun'];
            });

            usort($scopeData, function ($a, $b) {
                if ($a['tahun'] !== $b['tahun']) {
                    return $a['tahun'] <=> $b['tahun'];
                }
                return ($a['quarter'] ?? 0) <=> ($b['quarter'] ?? 0);
            });

            $isIlustratif = count($scopeData) < 2;
            $awal = $scopeData[0];

            $narasiTrend = $isIlustratif
                ? "Periode ini belum punya data pembanding yang cukup, sehingga tren belum dapat dianalisis secara bermakna."
                : "Tren menunjukkan pergerakan pendapatan dan laba bersih yang konsisten " . ($current['laba_bersih'] > $awal['laba_bersih'] ? 'membaik' : 'melemah') . " dibandingkan awal periode dalam scope ini.";

            $narasiRasio = $isIlustratif
                ? "Periode ini belum punya data pembanding yang cukup, sehingga tren rasio belum dapat dianalisis secara bermakna."
                : "Return on Equity (ROE) bergerak dari " . round($awal['roe'], 2) . "% menjadi " . round($current['roe'], 2)
                    . "%, sementara Current Ratio " . ($current['current_ratio'] >= $awal['current_ratio'] ? 'menguat' : 'melemah')
                    . " dari " . round($awal['current_ratio'], 2) . "% menjadi " . round($current['current_ratio'], 2) . "% dibandingkan awal periode dalam scope ini.";

            $narasiDupont = $isIlustratif
                ? "Periode ini belum punya data pembanding yang cukup, sehingga tren DuPont belum dapat dianalisis secara bermakna."
                : "ROE hasil dekomposisi DuPont bergerak dari " . round($awal['roe_dupont'], 2) . "% menjadi " . round($current['roe_dupont'], 2)
                    . "%, dengan leverage multiplier " . ($current['leverage_multiplier'] >= $awal['leverage_multiplier'] ? 'meningkat' : 'menurun')
                    . " dari " . round($awal['leverage_multiplier'], 2) . "x menjadi " . round($current['leverage_multiplier'], 2) . "x.";

            $narasiCommonsize = $isIlustratif
                ? "Periode ini belum punya data pembanding yang cukup, sehingga tren common-size belum dapat dianalisis secara bermakna."
                : "Porsi Laba Bersih terhadap pendapatan bergerak dari " . $awal['laba_bersih_persen'] . "% menjadi " . $current['laba_bersih_persen']
                    . "%, sementara porsi HPP " . ($current['hpp_persen'] <= $awal['hpp_persen'] ? 'mengecil' : 'membesar')
                    . " dari " . $awal['hpp_persen'] . "% menjadi " . $current['hpp_persen'] . "%.";

            $trendId = DB::table('analisis_trend')->insertGetId([
                'analisis_id'          => $current['analisis_id'],
                'is_data_ilustratif'   => $isIlustratif,
                'narasi_trend_AI'      => $narasiTrend,
                'narasi_rasio_AI'      => $narasiRasio,
                'narasi_dupont_AI'     => $narasiDupont,
                'narasi_commonsize_AI' => $narasiCommonsize,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);

            $sebelumnya = null;
            foreach (array_values($scopeData) as $urutan => $titik) {
                $growth = function ($field) use ($titik, $sebelumnya) {
                    if (!$sebelumnya || (float) $sebelumnya[$field] <= 0) {
                        return null;
                    }
                    return round((($titik[$field] - $sebelumnya[$field]) / $sebelumnya[$field]) * 100, 6);
                };

                DB::table('analisis_trend_periode')->insert([
                    'analisis_trend_id'      => $trendId,
                    'analisis_id'            => $titik['analisis_id'],
                    'urutan'                 => $urutan + 1,
                    'pendapatan'             => $titik['pendapatan'],
                    'laba_kotor'             => $titik['laba_kotor'],
                    'laba_bersih'            => $titik['laba_bersih'],
                    'total_assets'           => $titik['total_assets'],
                    'kas_setara_kas'         => $titik['kas_setara_kas'],
                    'total_equity'           => $titik['total_equity'],
                    'net_cash_flow'          => $titik['net_cash_flow'],
                    'growth_pendapatan'      => $growth('pendapatan'),
                    'growth_laba_kotor'      => $growth('laba_kotor'),
                    'growth_laba_bersih'     => $growth('laba_bersih'),
                    'growth_total_assets'    => $growth('total_assets'),
                    'growth_kas_setara_kas'  => $growth('kas_setara_kas'),
                    'growth_total_equity'    => $growth('total_equity'),
                    'growth_net_cash_flow'   => $growth('net_cash_flow'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $sebelumnya = $titik;
            }
        }
    }

    private function labelPeriode(array $periode): string
    {
        if ($periode['periode_type'] === 'annual') {
            return "Tahunan {$periode['tahun']}";
        }
        if ($periode['periode_type'] === 'quarterly') {
            return "Q{$periode['quarter']} {$periode['tahun']}";
        }
        return "Bulan {$periode['bulan']} {$periode['tahun']}";
    }
}