<?php

namespace App\Services;

use App\Models\Analisis;
use App\Models\Neraca;
use App\Models\LabaRugi;

class CalculateFinancialService
{
    // =====================================================================
    // RUMUS MENTAH (tidak berubah dari FinancialService lama)
    // =====================================================================

    public function currentRatio(float $currentAssets, float $currentLiabilities): float
    {
        return $currentLiabilities  == 0 ? 0 : $currentAssets / $currentLiabilities;
    }

    public function quickRatio(float $currentAssets, float $inventory, float $currentLiabilities): float
    {
        return $currentLiabilities  == 0 ? 0 : ($currentAssets - $inventory) / $currentLiabilities;
    }

    public function cashRatio(float $cash, float $currentLiabilities): float
    {
        return $currentLiabilities  == 0 ? 0 : $cash / $currentLiabilities;
    }

    public function netProfitMargin(float $netProfit, float $revenue): float
    {
        return $revenue  == 0 ? 0 : $netProfit / $revenue;
    }

    public function returnOnAssets(float $netProfit, float $totalAssets): float
    {
        return $totalAssets  == 0 ? 0 : $netProfit / $totalAssets;
    }

    public function returnOnEquity(float $netProfit, float $totalEquity): float
    {
        return $totalEquity  == 0 ? 0 : $netProfit / $totalEquity;
    }

    public function debtToEquity(float $totalLiabilities, float $totalEquity): float
    {
        return $totalEquity  == 0 ? 0 : $totalLiabilities / $totalEquity;
    }

    public function debtToAsset(float $totalLiabilities, float $totalAssets): float
    {
        return $totalAssets  == 0 ? 0 : $totalLiabilities / $totalAssets;
    }

    public function totalAssetTurnover(float $revenue, float $totalAssets): float
    {
        return $totalAssets  == 0 ? 0 : $revenue / $totalAssets;
    }

    public function financialLeverage(float $totalAssets, float $totalEquity): float
    {
        return $totalEquity  == 0 ? 0 : $totalAssets / $totalEquity;
    }

    public function commonSizePercentage(float $accountValue, float $baseValue): float
    {
        return $baseValue  == 0 ? 0 : ($accountValue / $baseValue) * 100;
    }

    // =====================================================================
    // ORKESTRASI HITUNG + SIMPAN (dipindah dari AnalysisFinancialService)
    // =====================================================================

    public function hitungSemuaRasio(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        $this->hitungLikuiditas($analisis, $neraca);
        $this->hitungProfitabilitas($analisis, $neraca, $labaRugi);
        $this->hitungSolvabilitas($analisis, $neraca);
        $this->hitungAktivitas($analisis, $neraca, $labaRugi);
        $this->hitungDupont($analisis, $neraca, $labaRugi);
        $this->hitungCommonsize($analisis, $neraca, $labaRugi);

        if (in_array($analisis->status, ['belum dihitung', 'Terjadi Perubahan Data!'])) {
            $analisis->update(['status' => 'sudah dihitung']);
        }
    }

    public function hitungLikuiditas(Analisis $analisis, Neraca $neraca): void
    {
        $cr  = $this->currentRatio((float) $neraca->current_assets, (float) $neraca->current_liabilities);
        $qr  = $this->quickRatio((float) $neraca->current_assets, (float) $neraca->inventory, (float) $neraca->current_liabilities);
        $csr = $this->cashRatio((float) $neraca->cash_equivalent, (float) $neraca->current_liabilities);

        $analisis->likuiditas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'current_ratio' => round($cr, 2),
                'quick_ratio'   => round($qr, 2),
                'cash_ratio'    => round($csr, 2),
            ]
        );
    }

    public function hitungProfitabilitas(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        $npm = $this->netProfitMargin((float) $labaRugi->laba_bersih, (float) $labaRugi->pendapatan);
        $roa = $this->returnOnAssets((float) $labaRugi->laba_bersih, (float) $neraca->total_assets);
        $roe = $this->returnOnEquity((float) $labaRugi->laba_bersih, (float) $neraca->total_equity);

        $analisis->profitabilitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'net_profit_margin' => round($npm * 100, 2),
                'ROA'               => round($roa * 100, 2),
                'ROE'               => round($roe * 100, 2),
            ]
        );
    }

    public function hitungSolvabilitas(Analisis $analisis, Neraca $neraca): void
    {
        $dte = $this->debtToEquity((float) $neraca->total_liabilities, (float) $neraca->total_equity);
        $dta = $this->debtToAsset((float) $neraca->total_liabilities, (float) $neraca->total_assets);

        $analisis->solvabilitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'debt_to_equity' => round($dte, 2),
                'debt_to_asset'  => round($dta, 2),
            ]
        );
    }

    public function hitungAktivitas(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        $tato = $this->totalAssetTurnover((float) $labaRugi->pendapatan, (float) $neraca->total_assets);

        $analisis->aktivitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            ['total_asset_turnover' => round($tato, 2)]
        );
    }

    public function hitungDupont(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        $npm      = $this->netProfitMargin((float) $labaRugi->laba_bersih, (float) $labaRugi->pendapatan);
        $tato     = $this->totalAssetTurnover((float) $labaRugi->pendapatan, (float) $neraca->total_assets);
        $leverage = $this->financialLeverage((float) $neraca->total_assets, (float) $neraca->total_equity);
        $roe      = $npm * $tato * $leverage;

        $analisis->dupont()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'net_profit_margin'    => round($npm * 100, 2),
                'total_asset_turnover' => round($tato, 2),
                'leverage_multiplier'  => round($leverage, 2),
                'roe'                  => round($roe * 100, 2),
            ]
        );
    }

    public function hitungCommonsize(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        $pendapatan = (float) $labaRugi->pendapatan;
        $labaKotor  = (float) $labaRugi->laba_kotor;
        $labaBersih = (float) $labaRugi->laba_bersih;

        $hpp            = $pendapatan - $labaKotor;
        $bebanLainPajak = $labaKotor - $labaBersih;

        $hppPersen            = $this->commonSizePercentage($hpp, $pendapatan);
        $labaKotorPersen      = $this->commonSizePercentage($labaKotor, $pendapatan);
        $bebanLainPajakPersen = $this->commonSizePercentage($bebanLainPajak, $pendapatan);
        $labaBersihPersen     = $this->commonSizePercentage($labaBersih, $pendapatan);

        $asetTetap         = (float) $neraca->total_assets - (float) $neraca->current_assets;
        $liabilitasPanjang = (float) $neraca->total_liabilities - (float) $neraca->current_liabilities;

        $asetLancarPersen        = $this->commonSizePercentage((float) $neraca->current_assets, (float) $neraca->total_assets);
        $asetTetapPersen         = $this->commonSizePercentage($asetTetap, (float) $neraca->total_assets);
        $liabilitasLancarPersen  = $this->commonSizePercentage((float) $neraca->current_liabilities, (float) $neraca->total_assets);
        $liabilitasPanjangPersen = $this->commonSizePercentage($liabilitasPanjang, (float) $neraca->total_assets);
        $ekuitasPersen           = $this->commonSizePercentage((float) $neraca->total_equity, (float) $neraca->total_assets);

        $analisis->commonsize()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'hpp_persen'                => round($hppPersen, 2),
                'laba_kotor_persen'         => round($labaKotorPersen, 2),
                'beban_lain_pajak_persen'   => round($bebanLainPajakPersen, 2),
                'laba_bersih_persen'        => round($labaBersihPersen, 2),
                'aset_lancar_persen'        => round($asetLancarPersen, 2),
                'aset_tetap_persen'         => round($asetTetapPersen, 2),
                'liabilitas_lancar_persen'  => round($liabilitasLancarPersen, 2),
                'liabilitas_panjang_persen' => round($liabilitasPanjangPersen, 2),
                'ekuitas_persen'            => round($ekuitasPersen, 2),
            ]
        );
    }
}
