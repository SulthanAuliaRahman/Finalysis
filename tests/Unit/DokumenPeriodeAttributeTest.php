<?php

namespace Tests\Unit;

use App\Models\Dokumen;
use Tests\TestCase;

/**
 * Implementasi kode dari sheet "Unit testing" (UT-037 s.d. UT-040) pada
 * Test_Case.xlsx, khusus accessor Dokumen::getPeriodeAttribute().
 *
 * Extends Tests\TestCase karena Dokumen adalah Eloquent Model (pakai
 * HasUuids) — instantiasi objeknya aman tanpa DB (tidak dipanggil ->save()),
 * cukup isi atribut via constructor lalu akses accessor $dokumen->periode.
 */
class DokumenPeriodeAttributeTest extends TestCase
{
    /** UT-037: Accessor label periode - tipe annual */
    public function test_periode_tipe_annual(): void
    {
        $dokumen = new Dokumen([
            'periode_type' => 'annual',
            'tahun' => 2024,
        ]);

        // CATATAN: tidak di-cast ke string, tipe data hasilnya integer,
        // bukan string '2024' — beda dari Analisis::getPeriodeAttribute()
        // versi lama yang sudah tidak ada lagi di model Analisis sekarang.
        $this->assertSame(2024, $dokumen->periode);
    }

    /** UT-038: Accessor label periode - tipe quarterly */
    public function test_periode_tipe_quarterly(): void
    {
        $dokumen = new Dokumen([
            'periode_type' => 'quarterly',
            'quarter' => 2,
            'tahun' => 2024,
        ]);

        $this->assertSame('Q2 2024', $dokumen->periode);
    }

    /** UT-039: Accessor label periode - tipe monthly */
    public function test_periode_tipe_monthly(): void
    {
        $dokumen = new Dokumen([
            'periode_type' => 'monthly',
            'bulan' => 3,
            'tahun' => 2024,
        ]);

        $this->assertSame('Maret 2024', $dokumen->periode);
    }

    /**
     * UT-040: Accessor gagal - periode_type tidak dikenal/null.
     * BUG yang masih ada: match() di getPeriodeAttribute() tidak punya
     * default case, jadi periode_type kosong/rusak bikin aplikasi crash
     * dengan \UnhandledMatchError, bukan fallback aman seperti '-'.
     */
    public function test_periode_type_tidak_dikenal_melempar_unhandled_match_error(): void
    {
        $dokumen = new Dokumen([
            'periode_type' => null,
        ]);

        $this->expectException(\UnhandledMatchError::class);

        // Akses accessor memicu evaluasi match() di dalam getPeriodeAttribute().
        $dokumen->periode;
    }
}
