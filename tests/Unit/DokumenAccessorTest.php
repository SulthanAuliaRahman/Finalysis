<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Dokumen;

class DokumenAccessorTest extends TestCase
{
    /** UT-033: getPeriodeAttribute - tipe annual (tidak di-cast ke string) */
    public function test_periode_label_annual(): void
    {
        $dokumen = new Dokumen([
            'periode_type' => 'annual',
            'tahun'        => 2024,
        ]);

        $this->assertEquals(2024, $dokumen->periode);
    }

    /** UT-034: getPeriodeAttribute - tipe quarterly */
    public function test_periode_label_quarterly(): void
    {
        $dokumen = new Dokumen([
            'periode_type' => 'quarterly',
            'tahun'        => 2024,
            'quarter'      => 2,
        ]);

        $this->assertEquals('Q2 2024', $dokumen->periode);
    }

    /** UT-035: getPeriodeAttribute - tipe monthly */
    public function test_periode_label_monthly(): void
    {
        $dokumen = new Dokumen([
            'periode_type' => 'monthly',
            'tahun'        => 2024,
            'bulan'        => 3,
        ]);

        $this->assertEquals('Maret 2024', $dokumen->periode);
    }

    /**
     * UT-036: getPeriodeAttribute - periode_type null/tidak dikenal
     * Expected: melempar \UnhandledMatchError karena tidak ada default case.
     */
    public function test_periode_label_tipe_tidak_dikenal_melempar_error(): void
    {
        $this->expectException(\UnhandledMatchError::class);

        $dokumen = new Dokumen([
            'periode_type' => null,
            'tahun'        => 2024,
        ]);

        $dokumen->periode; // trigger accessor
    }
}