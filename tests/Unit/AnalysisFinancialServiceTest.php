<?php

namespace Tests\Unit;

use App\Models\LabaRugi;
use App\Models\Neraca;
use App\Services\AnalysisFinancialService;
use App\Services\CalculateFinancialService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Implementasi kode dari sheet "Unit testing" (UT-028 s.d. UT-030) pada
 * Test_Case.xlsx, khusus method validasiKelengkapanData() yang murni
 * mengecek null tanpa menyentuh DB.
 *
 * Extends Tests\TestCase (bukan PHPUnit\Framework\TestCase) karena Neraca
 * dan LabaRugi adalah Eloquent Model — instantiasi objeknya aman tanpa DB
 * (tidak dipanggil ->save()), tapi tetap butuh container Laravel ter-boot
 * untuk trait HasUuids dsb.
 */
class AnalysisFinancialServiceTest extends TestCase
{
    private AnalysisFinancialService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AnalysisFinancialService(new CalculateFinancialService());
    }

    /** UT-028: Validasi gagal - Neraca null */
    public function test_validasi_gagal_neraca_null(): void
    {
        $labaRugi = new LabaRugi([
            'total_pendapatan' => 800000000,
            'laba_bersih_sesudah_pajak' => 80000000,
        ]);

        try {
            $this->service->validasiKelengkapanData(null, $labaRugi);
            $this->fail('Seharusnya ValidationException dilempar.');
        } catch (ValidationException $e) {
            $errors = $e->errors();

            $this->assertArrayHasKey('hitung_rasio', $errors);
            $this->assertSame(
                'Data Neraca dan Laba Rugi harus lengkap untuk menghitung seluruh rasio.',
                $errors['hitung_rasio'][0]
            );
        }
    }

    /** UT-029: Validasi gagal - Neraca & LabaRugi null */
    public function test_validasi_gagal_neraca_dan_laba_rugi_null(): void
    {
        try {
            $this->service->validasiKelengkapanData(null, null);
            $this->fail('Seharusnya ValidationException dilempar.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('hitung_rasio', $e->errors());
        }
    }

    /** UT-030: Validasi lolos - data lengkap (tidak ada exception dilempar) */
    public function test_validasi_lolos_data_lengkap(): void
    {
        $neraca = new Neraca([
            'total_asset_lancar' => 500000000,
            'total_liabilities_pendek' => 250000000,
            'total_asset' => 1000000000,
            'total_equitas' => 400000000,
        ]);

        $labaRugi = new LabaRugi([
            'total_pendapatan' => 800000000,
            'laba_bersih_sesudah_pajak' => 80000000,
        ]);

        // Tidak boleh melempar exception apapun.
        $this->service->validasiKelengkapanData($neraca, $labaRugi);

        $this->assertTrue(true);
    }
}
