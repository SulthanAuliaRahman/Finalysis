<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Validation\ValidationException;
use App\Services\AnalysisFinancialService;
use App\Services\CalculateFinancialService;
use App\Models\Neraca;
use App\Models\LabaRugi;

class AnalysisFinancialServiceTest extends TestCase
{
    protected AnalysisFinancialService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AnalysisFinancialService(new CalculateFinancialService());
    }

    /** UT-010: validasiKelengkapanData - Neraca null */
    public function test_validasi_neraca_null_throws_exception(): void
    {
        $this->expectException(ValidationException::class);
        $labaRugi = LabaRugi::factory()->make();
        $this->service->validasiKelengkapanData(null, $labaRugi);
    }

    /** UT-011: validasiKelengkapanData - Neraca & LabaRugi dua-duanya null */
    public function test_validasi_neraca_dan_laba_rugi_null_throws_exception(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->validasiKelengkapanData(null, null);
    }

    /** UT-012: validasiKelengkapanData - keduanya valid, tidak melempar exception */
    public function test_validasi_keduanya_valid_tidak_exception(): void
    {
        $neraca = Neraca::factory()->make();
        $labaRugi = LabaRugi::factory()->make();

        $this->service->validasiKelengkapanData($neraca, $labaRugi);

        $this->assertTrue(true);    
    }
}