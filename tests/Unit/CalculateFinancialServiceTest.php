<?php

namespace Tests\Unit;

use App\Services\CalculateFinancialService;
use PHPUnit\Framework\TestCase;

/**
 * Implementasi kode dari sheet "Unit testing" (UT-001 s.d. UT-027) pada
 * Test_Case.xlsx. Setiap method test diberi komentar ID UT-xxx yang sesuai
 * supaya bisa ditelusuri balik ke baris test case di Excel.
 *
 * Tidak extends Laravel\TestCase karena CalculateFinancialService murni PHP
 * (tidak menyentuh Eloquent/DB), jadi cukup PHPUnit\Framework\TestCase biasa
 * — lebih cepat jalan, tidak perlu boot kernel aplikasi.
 *
 * CATATAN: assertSame(0.0, ...) dipakai (bukan assertSame(0, ...)) untuk
 * kasus guard division-by-zero, karena semua method di CalculateFinancialService
 * punya return type ": float" — literal `0` di dalam method otomatis
 * di-coerce PHP jadi `0.0` saat dikembalikan (file ini tidak pakai
 * declare(strict_types=1)). assertSame(0, ...) (integer) akan gagal karena
 * 0 !== 0.0 secara strict type.
 */
class CalculateFinancialServiceTest extends TestCase
{
    private CalculateFinancialService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CalculateFinancialService();
    }

    // =====================================================================
    // currentRatio — UT-001, UT-002
    // =====================================================================

    /** UT-001: Hitung Current Ratio - nilai normal */
    public function test_current_ratio_nilai_normal(): void
    {
        $result = $this->service->currentRatio(500000000, 250000000);

        $this->assertEqualsWithDelta(2.0, $result, 0.0001);
    }

    /** UT-002: Hitung Current Ratio - currentLiabilities = 0 (guard division by zero) */
    public function test_current_ratio_current_liabilities_nol(): void
    {
        $result = $this->service->currentRatio(500000000, 0);

        $this->assertSame(0.0, $result);
    }

    // =====================================================================
    // cashRatio — UT-003, UT-004
    // =====================================================================

    /** UT-003: Hitung Cash Ratio - nilai normal */
    public function test_cash_ratio_nilai_normal(): void
    {
        $result = $this->service->cashRatio(50000000, 25000000);

        $this->assertEqualsWithDelta(2.0, $result, 0.0001);
    }

    /** UT-004: Hitung Cash Ratio - currentLiabilities = 0 */
    public function test_cash_ratio_current_liabilities_nol(): void
    {
        $result = $this->service->cashRatio(50000000, 0);

        $this->assertSame(0.0, $result);
    }

    // =====================================================================
    // netProfitMargin — UT-005, UT-006
    // =====================================================================

    /** UT-005: Hitung NPM - revenue = 0 (bukan Infinity/NaN) */
    public function test_net_profit_margin_revenue_nol(): void
    {
        $result = $this->service->netProfitMargin(10000000, 0);

        $this->assertSame(0.0, $result);
    }

    /** UT-006: Hitung NPM - nilai normal */
    public function test_net_profit_margin_nilai_normal(): void
    {
        $result = $this->service->netProfitMargin(80000000, 800000000);

        $this->assertEqualsWithDelta(0.1, $result, 0.0001);
    }

    // =====================================================================
    // returnOnAssets — UT-007, UT-008
    // =====================================================================

    /** UT-007: Hitung ROA - nilai normal */
    public function test_return_on_assets_nilai_normal(): void
    {
        $result = $this->service->returnOnAssets(80000000, 1000000000);

        $this->assertEqualsWithDelta(0.08, $result, 0.0001);
    }

    /** UT-008: Hitung ROA - totalAssets = 0 */
    public function test_return_on_assets_total_assets_nol(): void
    {
        $result = $this->service->returnOnAssets(80000000, 0);

        $this->assertSame(0.0, $result);
    }

    // =====================================================================
    // returnOnEquity — UT-009, UT-010, UT-011
    // =====================================================================

    /** UT-009: Hitung ROE - nilai normal */
    public function test_return_on_equity_nilai_normal(): void
    {
        $result = $this->service->returnOnEquity(50000000, 400000000);

        $this->assertEqualsWithDelta(0.125, $result, 0.0001);
    }

    /** UT-010: Hitung ROE - totalEquity = 0 (division by zero guard) */
    public function test_return_on_equity_total_equity_nol(): void
    {
        $result = $this->service->returnOnEquity(50000000, 0);

        $this->assertSame(0.0, $result);
    }

    /**
     * UT-011: Hitung ROE - totalEquity negatif.
     * Guard '$totalEquity == 0 ? 0 : ...' HANYA menolkan saat totalEquity
     * TEPAT 0 — nilai negatif dihitung apa adanya (boleh hasil ROE negatif).
     */
    public function test_return_on_equity_total_equity_negatif(): void
    {
        $result = $this->service->returnOnEquity(50000000, -20000000);

        $this->assertEqualsWithDelta(-2.5, $result, 0.0001);
    }

    // =====================================================================
    // debtToEquity — UT-012, UT-013
    // =====================================================================

    /** UT-012: Hitung DER - nilai normal */
    public function test_debt_to_equity_nilai_normal(): void
    {
        $result = $this->service->debtToEquity(500000000, 400000000);

        $this->assertEqualsWithDelta(1.25, $result, 0.0001);
    }

    /** UT-013: Hitung DER - totalEquity = 0 (guard division by zero) */
    public function test_debt_to_equity_total_equity_nol(): void
    {
        $result = $this->service->debtToEquity(500000000, 0);

        $this->assertSame(0.0, $result);
    }

    // =====================================================================
    // debtToAsset — UT-014, UT-015
    // =====================================================================

    /**
     * UT-014: Hitung DAR - nilai normal.
     * PENTING: method mentah ini mengembalikan RASIO FRAKSI apa adanya
     * (0.5), BUKAN 50. Perkalian x100 untuk representasi persen dilakukan
     * terpisah di CalculateFinancialService::hitungSolvabilitas() saat
     * menyimpan ke DB — bukan tanggung jawab method debtToAsset() ini.
     */
    public function test_debt_to_asset_nilai_normal(): void
    {
        $result = $this->service->debtToAsset(500000000, 1000000000);

        $this->assertEqualsWithDelta(0.5, $result, 0.0001);
    }

    /** UT-015: Hitung DAR - totalAssets = 0 */
    public function test_debt_to_asset_total_assets_nol(): void
    {
        $result = $this->service->debtToAsset(500000000, 0);

        $this->assertSame(0.0, $result);
    }

    // =====================================================================
    // totalAssetTurnover — UT-016, UT-017
    // =====================================================================

    /** UT-016: Hitung TATO - nilai normal */
    public function test_total_asset_turnover_nilai_normal(): void
    {
        $result = $this->service->totalAssetTurnover(800000000, 1000000000);

        $this->assertEqualsWithDelta(0.8, $result, 0.0001);
    }

    /** UT-017: Hitung TATO - totalAssets = 0 (guard division by zero) */
    public function test_total_asset_turnover_total_assets_nol(): void
    {
        $result = $this->service->totalAssetTurnover(800000000, 0);

        $this->assertSame(0.0, $result);
    }

    // =====================================================================
    // financialLeverage — UT-018, UT-019
    // =====================================================================

    /** UT-018: Hitung Leverage Multiplier - nilai normal */
    public function test_financial_leverage_nilai_normal(): void
    {
        $result = $this->service->financialLeverage(1000000000, 400000000);

        $this->assertEqualsWithDelta(2.5, $result, 0.0001);
    }

    /** UT-019: Hitung Leverage Multiplier - totalEquity = 0 */
    public function test_financial_leverage_total_equity_nol(): void
    {
        $result = $this->service->financialLeverage(1000000000, 0);

        $this->assertSame(0.0, $result);
    }

    // =====================================================================
    // workingCapital — UT-020, UT-021
    // =====================================================================

    /** UT-020: Hitung Modal Kerja - hasil positif */
    public function test_working_capital_hasil_positif(): void
    {
        $result = $this->service->workingCapital(500000000, 250000000);

        $this->assertEqualsWithDelta(250000000.0, $result, 0.0001);
    }

    /**
     * UT-021: Hitung Modal Kerja - hasil negatif.
     * Method ini murni pengurangan (bukan pembagian), jadi TIDAK ada guard
     * division-by-zero yang relevan — nilai negatif valid apa adanya.
     */
    public function test_working_capital_hasil_negatif(): void
    {
        $result = $this->service->workingCapital(200000000, 300000000);

        $this->assertEqualsWithDelta(-100000000.0, $result, 0.0001);
    }

    // =====================================================================
    // workingCapitalTurnover — UT-022, UT-023
    // =====================================================================

    /** UT-022: Hitung WCT - nilai normal */
    public function test_working_capital_turnover_nilai_normal(): void
    {
        $result = $this->service->workingCapitalTurnover(800000000, 250000000);

        $this->assertEqualsWithDelta(3.2, $result, 0.0001);
    }

    /** UT-023: Hitung WCT - avgWorkingCapital = 0 (guard division by zero) */
    public function test_working_capital_turnover_avg_working_capital_nol(): void
    {
        $result = $this->service->workingCapitalTurnover(800000000, 0);

        $this->assertSame(0.0, $result);
    }

    // =====================================================================
    // fixedAssetTurnover — UT-024, UT-025
    // =====================================================================

    /** UT-024: Hitung FAT - nilai normal */
    public function test_fixed_asset_turnover_nilai_normal(): void
    {
        $result = $this->service->fixedAssetTurnover(800000000, 400000000);

        $this->assertEqualsWithDelta(2.0, $result, 0.0001);
    }

    /** UT-025: Hitung FAT - avgFixedAssets = 0 (guard division by zero) */
    public function test_fixed_asset_turnover_avg_fixed_assets_nol(): void
    {
        $result = $this->service->fixedAssetTurnover(800000000, 0);

        $this->assertSame(0.0, $result);
    }

    // =====================================================================
    // commonSizePercentage — UT-026, UT-027
    // =====================================================================

    /**
     * UT-026: Hitung Common-Size % - nilai normal.
     * Method ini SUDAH mengalikan hasilnya x100 secara internal — beda
     * dengan debtToAsset() yang mengembalikan fraksi mentah (lihat UT-014).
     */
    public function test_common_size_percentage_nilai_normal(): void
    {
        $result = $this->service->commonSizePercentage(250000000, 1000000000);

        $this->assertEqualsWithDelta(25.0, $result, 0.0001);
    }

    /** UT-027: Hitung Common-Size % - baseValue = 0 */
    public function test_common_size_percentage_base_value_nol(): void
    {
        $result = $this->service->commonSizePercentage(5000000, 0);

        $this->assertSame(0.0, $result);
    }
}