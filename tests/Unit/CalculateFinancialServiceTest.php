<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CalculateFinancialService;

class CalculateFinancialServiceTest extends TestCase
{
    protected CalculateFinancialService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CalculateFinancialService();
    }

    /** UT-001: currentRatio - nilai normal */
    public function test_current_ratio_nilai_normal(): void
    {
        $result = $this->service->currentRatio(500000000, 250000000);
        $this->assertEqualsWithDelta(2.0, $result, 0.0001);
    }

    /** UT-002: currentRatio - currentLiabilities = 0 (guard division by zero) */
    public function test_current_ratio_liabilitas_nol(): void
    {
        $result = $this->service->currentRatio(500000000, 0);
        $this->assertEquals(0, $result);
    }

    /** UT-003: quickRatio - nilai normal */
    public function test_quick_ratio_nilai_normal(): void
    {
        $result = $this->service->quickRatio(500000000, 100000000, 250000000);
        $this->assertEqualsWithDelta(1.6, $result, 0.0001);
    }

    /** UT-004: cashRatio - currentLiabilities = 0 */
    public function test_cash_ratio_liabilitas_nol(): void
    {
        $result = $this->service->cashRatio(50000000, 0);
        $this->assertEquals(0, $result);
    }

    /** UT-005: netProfitMargin - revenue = 0 */
    public function test_net_profit_margin_revenue_nol(): void
    {
        $result = $this->service->netProfitMargin(10000000, 0);
        $this->assertEquals(0, $result);
    }

    /** UT-006 
     
     */
    public function test_return_on_equity_ekuitas_nol(): void
    {
        $result = $this->service->returnOnEquity(50000000, 0);
        $this->assertEquals(0, $result);
    }
    
    /** UT-007: returnOnEquity - totalEquity negatif (dihitung apa adanya) */
    public function test_return_on_equity_ekuitas_negatif_dihitung_apa_adanya(): void
    {
        $result = $this->service->returnOnEquity(50000000, -20000000);
        $this->assertEqualsWithDelta(-2.5, $result, 0.0001);
    }

    /** UT-008: financialLeverage - nilai normal */
    public function test_financial_leverage_nilai_normal(): void
    {
        $result = $this->service->financialLeverage(1000000000, 400000000);
        $this->assertEqualsWithDelta(2.5, $result, 0.0001);
    }

    /** UT-009: commonSizePercentage - baseValue = 0 */
    public function test_common_size_percentage_base_nol(): void
    {
        $result = $this->service->commonSizePercentage(5000000, 0);
        $this->assertEquals(0, $result);
    }

    // /** UT-019: returnOnAssets - nilai normal */
    // public function test_return_on_assets_nilai_normal(): void
    // {
    //     $result = $this->service->returnOnAssets(80000000, 1000000000);
    //     $this->assertEqualsWithDelta(0.08, $result, 0.0001);
    // }

    // /** UT-020: debtToEquity - totalEquity = 0 (guard division by zero) */
    // public function test_debt_to_equity_ekuitas_nol(): void
    // {
    //     $result = $this->service->debtToEquity(500000000, 0);
    //     $this->assertEquals(0, $result);
    // }

    // /** UT-021: debtToAsset - nilai normal */
    // public function test_debt_to_asset_nilai_normal(): void
    // {
    //     $result = $this->service->debtToAsset(500000000, 1000000000);
    //     $this->assertEqualsWithDelta(0.5, $result, 0.0001);
    // }

    // /** UT-022: totalAssetTurnover - totalAssets = 0 (guard division by zero) */
    // public function test_total_asset_turnover_aset_nol(): void
    // {
    //     $result = $this->service->totalAssetTurnover(800000000, 0);
    //     $this->assertEquals(0, $result);
    // }

    // /** UT-026: quickRatio - currentLiabilities = 0 (guard division by zero) */
    // public function test_quick_ratio_liabilitas_nol(): void
    // {
    //     $result = $this->service->quickRatio(500000000, 100000000, 0);
    //     $this->assertEquals(0, $result);
    // }

    // /** UT-027: returnOnAssets - totalAssets = 0 (guard division by zero) */
    // public function test_return_on_assets_aset_nol(): void
    // {
    //     $result = $this->service->returnOnAssets(80000000, 0);
    //     $this->assertEquals(0, $result);
    // }

    // /** UT-028: debtToAsset - totalAssets = 0 (guard division by zero) */
    // public function test_debt_to_asset_aset_nol(): void
    // {
    //     $result = $this->service->debtToAsset(500000000, 0);
    //     $this->assertEquals(0, $result);
    // }

    // /** UT-029: financialLeverage - totalEquity = 0 (guard division by zero) */
    // public function test_financial_leverage_ekuitas_nol(): void
    // {
    //     $result = $this->service->financialLeverage(1000000000, 0);
    //     $this->assertEquals(0, $result);
    // }
}