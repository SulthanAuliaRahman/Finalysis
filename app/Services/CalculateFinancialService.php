<?php

namespace App\Services;

use App\Models\Analisis;
use App\Models\Neraca;
use App\Models\LabaRugi;

class CalculateFinancialService
{
    // =====================================================================
    // RUMUS MENTAH
    // =====================================================================

    public function currentRatio(float $currentAssets, float $currentLiabilities): float
    {
        return $currentLiabilities == 0 ? 0 : $currentAssets / $currentLiabilities;
    }

    public function cashRatio(float $cash, float $currentLiabilities): float
    {
        return $currentLiabilities == 0 ? 0 : $cash / $currentLiabilities;
    }

    public function netProfitMargin(float $netProfit, float $revenue): float
    {
        return $revenue == 0 ? 0 : $netProfit / $revenue;
    }

    public function returnOnAssets(float $netProfit, float $totalAssets): float
    {
        return $totalAssets == 0 ? 0 : $netProfit / $totalAssets;
    }

    public function returnOnEquity(float $netProfit, float $totalEquity): float
    {
        return $totalEquity == 0 ? 0 : $netProfit / $totalEquity;
    }

    public function debtToEquity(float $totalLiabilities, float $totalEquity): float
    {
        return $totalEquity == 0 ? 0 : $totalLiabilities / $totalEquity;
    }

    public function debtToAsset(float $totalLiabilities, float $totalAssets): float
    {
        return $totalAssets == 0 ? 0 : $totalLiabilities / $totalAssets;
    }

    public function totalAssetTurnover(float $revenue, float $totalAssets): float
    {
        return $totalAssets == 0 ? 0 : $revenue / $totalAssets;
    }

    // Financial Leverage = Rata-rata Total Aset / Rata-rata Total Ekuitas (rumus II.8-II.10).
    // Nilai yang diterima BISA rata-rata (kalau ada periode sebelumnya) ATAU nilai
    // periode berjalan langsung (laporan pertama) — keputusan itu dilakukan di
    // hitungSolvabilitas()/hitungDupont(), bukan di sini.
    public function financialLeverage(float $totalAssets, float $totalEquity): float
    {
        return $totalEquity == 0 ? 0 : $totalAssets / $totalEquity;
    }

    // Modal Kerja = Aset Lancar - Liabilitas Jangka Pendek.
    public function workingCapital(float $currentAssets, float $currentLiabilities): float
    {
        return $currentAssets - $currentLiabilities;
    }

    // Working Capital Turnover = Pendapatan / Rata-rata Modal Kerja (rumus II.12).
    public function workingCapitalTurnover(float $revenue, float $avgWorkingCapital): float
    {
        return $avgWorkingCapital == 0 ? 0 : $revenue / $avgWorkingCapital;
    }

    // Fixed Asset Turnover = Pendapatan / Rata-rata Aset Tetap (rumus II.13).
    public function fixedAssetTurnover(float $revenue, float $avgFixedAssets): float
    {
        return $avgFixedAssets == 0 ? 0 : $revenue / $avgFixedAssets;
    }

    public function commonSizePercentage(float $accountValue, float $baseValue): float
    {
        return $baseValue == 0 ? 0 : ($accountValue / $baseValue) * 100;
    }

    // Rata-rata dua nilai (awal periode + akhir periode) / 2 — Financial Leverage, WCT, FAT.
    private function rataRataDuaPeriode(float $awal, float $akhir): float
    {
        return ($awal + $akhir) / 2;
    }

    // =====================================================================
    // ORKESTRASI HITUNG + SIMPAN
    // =====================================================================

    // $neracaSebelumnya: Neraca periode tepat sebelumnya, atau null kalau ini
    // laporan pertama yang diupload untuk perusahaan ini. Kalau null, Financial
    // Leverage/WCT/FAT dihitung langsung dari nilai akhir periode (tanpa rata-rata).
    public function hitungSemuaRasio(
        Analisis $analisis,
        Neraca $neraca,
        LabaRugi $labaRugi,
        ?Neraca $neracaSebelumnya = null
    ): void {
        $this->hitungLikuiditas($analisis, $neraca);
        $this->hitungProfitabilitas($analisis, $neraca, $labaRugi);
        $this->hitungSolvabilitas($analisis, $neraca, $neracaSebelumnya);
        $this->hitungAktivitas($analisis, $neraca, $labaRugi, $neracaSebelumnya);
        $this->hitungDupont($analisis, $neraca, $labaRugi, $neracaSebelumnya);
        $this->hitungCommonsize($analisis, $neraca, $labaRugi);
    }

    public function hitungLikuiditas(Analisis $analisis, Neraca $neraca): void
    {
        $cr  = $this->currentRatio((float) $neraca->total_asset_lancar, (float) $neraca->total_liabilities_pendek);
        $csr = $this->cashRatio((float) $neraca->total_kas_setara_kas, (float) $neraca->total_liabilities_pendek);

        $analisis->likuiditas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'current_ratio' => round($cr, 2),
                'cash_ratio'    => round($csr, 2),
            ]
        );
    }

    public function hitungProfitabilitas(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        $labaBersih = (float) $labaRugi->laba_bersih_sesudah_pajak;

        $npm = $this->netProfitMargin($labaBersih, (float) $labaRugi->total_pendapatan);
        $roa = $this->returnOnAssets($labaBersih, (float) $neraca->total_asset);
        $roe = $this->returnOnEquity($labaBersih, (float) $neraca->total_equitas);

        $analisis->profitabilitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'net_profit_margin' => round($npm * 100, 2),
                'ROA'               => round($roa * 100, 2),
                'ROE'               => round($roe * 100, 2),
            ]
        );
    }

    public function hitungSolvabilitas(Analisis $analisis, Neraca $neraca, ?Neraca $neracaSebelumnya = null): void
    {
        $dte = $this->debtToEquity((float) $neraca->total_liabilities, (float) $neraca->total_equitas);
        $dta = $this->debtToAsset((float) $neraca->total_liabilities, (float) $neraca->total_asset);

        [$totalAssetsUntukLeverage, $totalEquityUntukLeverage] = $this->nilaiUntukRataRata(
            (float) $neraca->total_asset,
            (float) $neraca->total_equitas,
            $neracaSebelumnya
        );

        $leverage = $this->financialLeverage($totalAssetsUntukLeverage, $totalEquityUntukLeverage);

        // Kolom di migration bernama `leverage_multiplier`, bukan `financial_leverage`.
        $analisis->solvabilitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'debt_to_equity'      => round($dte, 2),
                'debt_to_asset'       => round($dta, 2),
                'leverage_multiplier' => round($leverage, 2),
            ]
        );
    }

    public function hitungAktivitas(
        Analisis $analisis,
        Neraca $neraca,
        LabaRugi $labaRugi,
        ?Neraca $neracaSebelumnya = null
    ): void {
        $revenue = (float) $labaRugi->total_pendapatan;

        // TATO tidak pakai rata-rata (rumus II.11: Pendapatan / Total Aset akhir periode).
        $tato = $this->totalAssetTurnover($revenue, (float) $neraca->total_asset);

        $modalKerjaSekarang = $this->workingCapital(
            (float) $neraca->total_asset_lancar,
            (float) $neraca->total_liabilities_pendek
        );

        if ($neracaSebelumnya === null) {
            $modalKerjaUntukWct = $modalKerjaSekarang;
            $asetTetapUntukFat  = (float) $neraca->total_asset_tetap;
        } else {
            $modalKerjaSebelumnya = $this->workingCapital(
                (float) $neracaSebelumnya->total_asset_lancar,
                (float) $neracaSebelumnya->total_liabilities_pendek
            );

            $modalKerjaUntukWct = $this->rataRataDuaPeriode($modalKerjaSebelumnya, $modalKerjaSekarang);
            $asetTetapUntukFat  = $this->rataRataDuaPeriode(
                (float) $neracaSebelumnya->total_asset_tetap,
                (float) $neraca->total_asset_tetap
            );
        }

        $wct = $this->workingCapitalTurnover($revenue, $modalKerjaUntukWct);
        $fat = $this->fixedAssetTurnover($revenue, $asetTetapUntukFat);

        $analisis->aktivitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'total_asset_turnover'     => round($tato, 2),
                'working_capital_turnover' => round($wct, 2),
                'fixed_asset_turnover'     => round($fat, 2),
            ]
        );
    }

    public function hitungDupont(
        Analisis $analisis,
        Neraca $neraca,
        LabaRugi $labaRugi,
        ?Neraca $neracaSebelumnya = null
    ): void {
        $npm  = $this->netProfitMargin((float) $labaRugi->laba_bersih_sesudah_pajak, (float) $labaRugi->total_pendapatan);
        $tato = $this->totalAssetTurnover((float) $labaRugi->total_pendapatan, (float) $neraca->total_asset);

        [$totalAssetsUntukLeverage, $totalEquityUntukLeverage] = $this->nilaiUntukRataRata(
            (float) $neraca->total_asset,
            (float) $neraca->total_equitas,
            $neracaSebelumnya
        );

        $leverage  = $this->financialLeverage($totalAssetsUntukLeverage, $totalEquityUntukLeverage);
        $roeDupont = $npm * $tato * $leverage;

        // Tabel analisis_dupont hanya menyimpan roe_dupont — NPM/TATO/Leverage
        // sudah tersimpan masing-masing di analisis_profitabilitas, analisis_aktivitas,
        // dan analisis_solvabilitas, jadi tidak perlu diduplikasi di sini.
        $analisis->dupont()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            ['roe_dupont' => round($roeDupont * 100, 2)]
        );
    }

    public function hitungCommonsize(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        $pendapatan = (float) $labaRugi->total_pendapatan;
        // Beban gabungan (beban usaha + beban pajak), karena skema tidak memisahkan
        // Laba Kotor/HPP — Common-Size Laba Rugi di sini cuma 3 baris: Pendapatan,
        // Beban, Laba Bersih.
        $beban      = (float) $labaRugi->total_beban + (float) $labaRugi->total_biaya_pajak;
        $labaBersih = (float) $labaRugi->laba_bersih_sesudah_pajak;

        $pendapatanPersen = $this->commonSizePercentage($pendapatan, $pendapatan);
        $bebanPersen      = $this->commonSizePercentage($beban, $pendapatan);
        $labaBersihPersen = $this->commonSizePercentage($labaBersih, $pendapatan);

        $totalAsset = (float) $neraca->total_asset;

        $asetLancarPersen        = $this->commonSizePercentage((float) $neraca->total_asset_lancar, $totalAsset);
        $asetTetapPersen         = $this->commonSizePercentage((float) $neraca->total_asset_tetap, $totalAsset);
        $liabilitasPendekPersen  = $this->commonSizePercentage((float) $neraca->total_liabilities_pendek, $totalAsset);
        $liabilitasPanjangPersen = $this->commonSizePercentage((float) $neraca->total_liabilities_panjang, $totalAsset);
        $ekuitasPersen           = $this->commonSizePercentage((float) $neraca->total_equitas, $totalAsset);

        $analisis->commonsize()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'pendapatan_persen'         => round($pendapatanPersen, 2),
                'beban_persen'              => round($bebanPersen, 2),
                'laba_bersih_persen'        => round($labaBersihPersen, 2),
                'aset_lancar_persen'        => round($asetLancarPersen, 2),
                'aset_tetap_persen'         => round($asetTetapPersen, 2),
                'liabilitas_pendek_persen'  => round($liabilitasPendekPersen, 2),
                'liabilitas_panjang_persen' => round($liabilitasPanjangPersen, 2),
                'ekuitas_persen'            => round($ekuitasPersen, 2),
            ]
        );
    }

    // Helper bersama: tentukan nilai Total Aset & Total Ekuitas yang dipakai untuk
    // Financial Leverage — rata-rata kalau ada periode sebelumnya, langsung nilai
    // akhir periode kalau ini laporan pertama.
    private function nilaiUntukRataRata(float $totalAssetSekarang, float $totalEquitySekarang, ?Neraca $neracaSebelumnya): array
    {
        if ($neracaSebelumnya === null) {
            return [$totalAssetSekarang, $totalEquitySekarang];
        }

        return [
            $this->rataRataDuaPeriode((float) $neracaSebelumnya->total_asset, $totalAssetSekarang),
            $this->rataRataDuaPeriode((float) $neracaSebelumnya->total_equitas, $totalEquitySekarang),
        ];
    }
}