<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Analisis;

class AnalisisAccessorTest extends TestCase
{
    /** UT-023: getPeriodeAttribute - tipe quarterly */
    public function test_periode_label_quarterly(): void
    {
        $analisis = new Analisis([
            'periode_type' => 'quarterly',
            'tahun'        => 2024,
            'quarter'      => 2,
        ]);

        $this->assertEquals('Q2 2024', $analisis->periode);
    }

    /** UT-027: getPeriodeAttribute - tipe annual */
    public function test_periode_label_annual(): void
    {
        $analisis = new Analisis([
            'periode_type' => 'annual',
            'tahun'        => 2024,
        ]);

        $this->assertEquals('2024', $analisis->periode);
    }

    /** UT-028: getPeriodeAttribute - tipe monthly */
    public function test_periode_label_monthly(): void
    {
        $analisis = new Analisis([
            'periode_type' => 'monthly',
            'tahun'        => 2024,
            'bulan'        => 3,
        ]);

        $this->assertEquals('Maret 2024', $analisis->periode);
    }
}