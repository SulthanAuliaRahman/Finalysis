<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Perusahaan;
use App\Models\Dokumen;
use App\Models\Analisis;
use App\Models\Neraca;
use App\Models\LabaRugi;
use App\Models\AnalisisLikuiditas;

class AnalisisControllerHitungRasioTest extends TestCase
{
    use RefreshDatabase;

    /** IT-001 (Positive): Hitung rasio sukses - data Neraca & LabaRugi lengkap */
    public function test_hitung_rasio_sukses(): void
    {
        $perusahaan = Perusahaan::factory()->create();
        $user = User::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'role'          => 'user',
        ]);

        $dokumen = Dokumen::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'periode_type'  => 'annual',
            'tahun'         => 2024,
            'quarter'       => null,
            'bulan'         => null,
        ]);

        Neraca::factory()->create([
            'dokumen_id'          => $dokumen->id,
            'current_assets'      => 500000000,
            'current_liabilities' => 250000000,
            'total_assets'        => 1000000000,
            'total_equity'        => 400000000,
            'total_liabilities'   => 500000000,
        ]);

        LabaRugi::factory()->create([
            'dokumen_id'  => $dokumen->id,
            'pendapatan'  => 800000000,
            'laba_bersih' => 80000000,
        ]);

        $analisis = Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'periode_type'  => 'annual',
            'tahun'         => 2024,
            'quarter'       => null,
            'bulan'         => null,
            'status'        => 'belum dihitung',
        ]);

        $response = $this->actingAs($user)
            ->post(route('perusahaan.analisis.hitung-rasio', [$perusahaan, $analisis]));

        $response->assertRedirect();
        $this->assertDatabaseHas('analisis_likuiditas', ['analisis_id' => $analisis->id]);
        $this->assertDatabaseHas('analisis_profitabilitas', ['analisis_id' => $analisis->id]);
        $this->assertDatabaseHas('analisis_solvabilitas', ['analisis_id' => $analisis->id]);
        $this->assertDatabaseHas('analisis_aktivitas', ['analisis_id' => $analisis->id]);
        $this->assertDatabaseHas('analisis_dupont', ['analisis_id' => $analisis->id]);
        $this->assertDatabaseHas('analisis_commonsize', ['analisis_id' => $analisis->id]);
        $this->assertEquals('sudah dihitung', $analisis->fresh()->status);
    }

    /** IT-002 (Negative): Hitung rasio gagal - Neraca tidak ditemukan */
    public function test_hitung_rasio_gagal_neraca_tidak_ada(): void
    {
        $perusahaan = Perusahaan::factory()->create();
        $user = User::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'role'          => 'user',
        ]);

        $dokumen = Dokumen::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'periode_type'  => 'annual',
            'tahun'         => 2024,
        ]);

        LabaRugi::factory()->create(['dokumen_id' => $dokumen->id]);

        $analisis = Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'periode_type'  => 'annual',
            'tahun'         => 2024,
            'status'        => 'belum dihitung',
        ]);

        $response = $this->actingAs($user)
            ->post(route('perusahaan.analisis.hitung-rasio', [$perusahaan, $analisis]));

        $response->assertSessionHasErrors('hitung_rasio');
        $this->assertDatabaseMissing('analisis_likuiditas', ['analisis_id' => $analisis->id]);
    }

    /** IT-003 (Positive): Hitung ulang rasio pada status 'sudah dihitung' - data terupdate, status tetap */
    public function test_hitung_ulang_rasio_status_sudah_dihitung(): void
    {
        $perusahaan = Perusahaan::factory()->create();
        $user = User::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'role'          => 'user',
        ]);

        $dokumen = Dokumen::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'periode_type'  => 'annual',
            'tahun'         => 2024,
        ]);

        Neraca::factory()->create([
            'dokumen_id'          => $dokumen->id,
            'current_assets'      => 600000000,
            'current_liabilities' => 250000000,
        ]);

        LabaRugi::factory()->create(['dokumen_id' => $dokumen->id]);

        $analisis = Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'periode_type'  => 'annual',
            'tahun'         => 2024,
            'status'        => 'sudah dihitung',
        ]);

        AnalisisLikuiditas::factory()->create([
            'analisis_id'   => $analisis->id,
            'current_ratio' => 2.0,
        ]);

        $this->actingAs($user)
            ->post(route('perusahaan.analisis.hitung-rasio', [$perusahaan, $analisis]));

        $this->assertEquals(2.4, AnalisisLikuiditas::where('analisis_id', $analisis->id)->first()->current_ratio);
        $this->assertEquals('sudah dihitung', $analisis->fresh()->status);
    }

    /** IT-004 (Positive): Hitung ulang rasio pada status 'Terjadi Perubahan Data!' - status kembali 'sudah dihitung' */
    public function test_hitung_ulang_rasio_status_terjadi_perubahan_data(): void
    {
        $perusahaan = Perusahaan::factory()->create();
        $user = User::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'role'          => 'user',
        ]);

        $dokumen = Dokumen::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'periode_type'  => 'annual',
            'tahun'         => 2024,
        ]);

        Neraca::factory()->create(['dokumen_id' => $dokumen->id]);
        LabaRugi::factory()->create(['dokumen_id' => $dokumen->id]);

        $analisis = Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'periode_type'  => 'annual',
            'tahun'         => 2024,
            'status'        => 'Terjadi Perubahan Data!',
        ]);

        $this->actingAs($user)
            ->post(route('perusahaan.analisis.hitung-rasio', [$perusahaan, $analisis]));

        $this->assertEquals('sudah dihitung', $analisis->fresh()->status);
    }
}