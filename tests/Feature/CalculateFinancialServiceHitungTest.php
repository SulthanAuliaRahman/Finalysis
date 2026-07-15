<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Perusahaan;
use App\Models\Dokumen;
use App\Models\Analisis;
use App\Models\Neraca;
use App\Models\LabaRugi;
use App\Services\CalculateFinancialService;

class CalculateFinancialServiceHitungTest extends TestCase
{
    use RefreshDatabase;

    protected CalculateFinancialService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CalculateFinancialService();
    }

    /** IT-014 (Positive): hitungDupont - data lengkap */
    public function test_hitung_dupont_data_lengkap(): void
    {
        $perusahaan = Perusahaan::factory()->create();
        $dokumen = Dokumen::factory()->create(['perusahaan_id' => $perusahaan->id]);
        $analisis = Analisis::factory()->create(['perusahaan_id' => $perusahaan->id]);

        $neraca = Neraca::factory()->create([
            'dokumen_id'   => $dokumen->id,
            'total_assets' => 1000000000,
            'total_equity' => 400000000,
        ]);

        $labaRugi = LabaRugi::factory()->create([
            'dokumen_id'  => $dokumen->id,
            'pendapatan'  => 800000000,
            'laba_bersih' => 80000000,
        ]);

        $this->service->hitungDupont($analisis, $neraca, $labaRugi);

        $dupont = $analisis->dupont()->first();

        $this->assertNotNull($dupont);
        // NPM = 80jt/800jt = 0.1 -> *100 = 10.00
        $this->assertEquals(10.00, $dupont->net_profit_margin);
        // TATO = 800jt/1000jt = 0.8
        $this->assertEquals(0.80, $dupont->total_asset_turnover);
        // Leverage = 1000jt/400jt = 2.5
        $this->assertEquals(2.50, $dupont->leverage_multiplier);
        // ROE = NPM(0.1) x TATO(0.8) x Leverage(2.5) = 0.2 -> *100 = 20.00
        $this->assertEquals(20.00, $dupont->roe);

        // Konsistensi manual: roe tersimpan harus = npm% x tato x leverage / 100 ... dicek via rumus asli
        $this->assertEqualsWithDelta(
            round(($dupont->net_profit_margin / 100) * $dupont->total_asset_turnover * $dupont->leverage_multiplier * 100, 2),
            $dupont->roe,
            0.01
        );
    }

    /** IT-015 (Positive): hitungCommonsize - proporsi Balance Sheet total 100% */
    public function test_hitung_commonsize_proporsi_seratus_persen(): void
    {
        $perusahaan = Perusahaan::factory()->create();
        $dokumen = Dokumen::factory()->create(['perusahaan_id' => $perusahaan->id]);
        $analisis = Analisis::factory()->create(['perusahaan_id' => $perusahaan->id]);

        $neraca = Neraca::factory()->create([
            'dokumen_id'          => $dokumen->id,
            'total_assets'        => 1000000000,
            'current_assets'      => 600000000,
            'total_liabilities'   => 500000000,
            'current_liabilities' => 300000000,
            'total_equity'        => 500000000,
        ]);

        $labaRugi = LabaRugi::factory()->create([
            'dokumen_id'  => $dokumen->id,
            'pendapatan'  => 800000000,
            'laba_kotor'  => 300000000,
            'laba_bersih' => 80000000,
        ]);

        $this->service->hitungCommonsize($analisis, $neraca, $labaRugi);

        $commonsize = $analisis->commonsize()->first();
        $this->assertNotNull($commonsize);

        // Balance Sheet (basis Total Aset): Aset Lancar + Aset Tetap = 100%
        $totalAsetPersen = $commonsize->aset_lancar_persen + $commonsize->aset_tetap_persen;
        $this->assertEqualsWithDelta(100.0, $totalAsetPersen, 0.01);

        // Liabilitas Lancar + Liabilitas Jk. Panjang + Ekuitas = 100%
        $totalPasivaPersen = $commonsize->liabilitas_lancar_persen
            + $commonsize->liabilitas_panjang_persen
            + $commonsize->ekuitas_persen;
        $this->assertEqualsWithDelta(100.0, $totalPasivaPersen, 0.01);

        // Sanity check angka spesifik: Aset Lancar 600jt/1000jt = 60%
        $this->assertEquals(60.00, $commonsize->aset_lancar_persen);
        // Ekuitas 500jt/1000jt = 50%
        $this->assertEquals(50.00, $commonsize->ekuitas_persen);
    }
}