<?php

namespace App\Services;

class FinancialService
{
    // =====================================================================
    // RASIO LIKUIDITAS
    // =====================================================================

    public function currentRatio(float $currentAssets, float $currentLiabilities): float
    {
        return $currentLiabilities > 0 ? $currentAssets / $currentLiabilities : 0;
    }

    public function quickRatio(float $currentAssets, float $inventory, float $currentLiabilities): float
    {
        return $currentLiabilities > 0 ? ($currentAssets - $inventory) / $currentLiabilities : 0;
    }

    public function cashRatio(float $cash, float $currentLiabilities): float
    {
        return $currentLiabilities > 0 ? $cash / $currentLiabilities : 0;
    }

    // =====================================================================
    // RASIO PROFITABILITAS
    // =====================================================================

    public function netProfitMargin(float $netProfit, float $revenue): float
    {
        return $revenue > 0 ? $netProfit / $revenue : 0;
    }

    public function returnOnAssets(float $netProfit, float $totalAssets): float
    {
        return $totalAssets > 0 ? $netProfit / $totalAssets : 0;
    }

    public function returnOnEquity(float $netProfit, float $totalEquity): float
    {
        return $totalEquity > 0 ? $netProfit / $totalEquity : 0;
    }

    // =====================================================================
    // RASIO SOLVABILITAS
    // =====================================================================

    public function debtToEquity(float $totalLiabilities, float $totalEquity): float
    {
        return $totalEquity > 0 ? $totalLiabilities / $totalEquity : 0;
    }

    public function debtToAsset(float $totalLiabilities, float $totalAssets): float
    {
        return $totalAssets > 0 ? $totalLiabilities / $totalAssets : 0;
    }

    // =====================================================================
    // RASIO AKTIVITAS
    // =====================================================================

    public function totalAssetTurnover(float $revenue, float $totalAssets): float
    {
        return $totalAssets > 0 ? $revenue / $totalAssets : 0;
    }

    // =====================================================================
    // DUPONT DECOMPOSITION
    // =====================================================================

    public function financialLeverage(float $totalAssets, float $totalEquity): float
    {
        return $totalEquity > 0 ? $totalAssets / $totalEquity : 0;
    }

    // =====================================================================
    // COMMON-SIZE ANALYSIS (Vertical % Calculation)
    // =====================================================================

    public function commonSizePercentage(float $accountValue, float $baseValue): float
    {
        return $baseValue > 0 ? ($accountValue / $baseValue) * 100 : 0;
    }
}