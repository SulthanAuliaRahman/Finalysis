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
use App\Models\AnalisisProfitabilitas;
use App\Models\AnalisisSolvabilitas;
use App\Models\AnalisisAktivitas;
use App\Models\AnalisisDupont;
use App\Models\AnalisisCommonsize;

class AnalisisControllerIndexDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** IT-043 (Positive): Index - grouping dokumen per periode berhasil */
    public function test_index_grouping_dokumen_per_periode(): void
    {
        $perusahaan = Perusahaan::factory()->create();
        $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

        Dokumen::factory()->count(2)->create([
            'perusahaan_id' => $perusahaan->id,
            'periode_type'  => 'annual',
            'tahun'         => 2024,
            'quarter'       => null,
            'bulan'         => null,
        ]);
        Dokumen::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'periode_type'  => 'annual',
            'tahun'         => 2023,
            'quarter'       => null,
            'bulan'         => null,
        ]);

        $response = $this->actingAs($user)->get("/perusahaan/{$perusahaan->id}/analisis");

        // --- DEBUG SEMENTARA ---
        dump('STATUS: ' . $response->getStatusCode());
        if ($response->exception) {
            dump('EXCEPTION: ' . $response->exception->getMessage());
            dump($response->exception->getFile() . ':' . $response->exception->getLine());
        } else {
            dump('CONTENT (500 char pertama): ' . substr($response->getContent(), 0, 500));
        }
        // --- END DEBUG ---

        $response->assertInertia(fn ($page) => $page
            ->component('Perusahaan/Analisis/Index')
            ->has('analisisList', 2)
            ->where('analisisList.0.tahun', 2024)
            ->where('analisisList.0.jumlah_dokumen', 2)
            ->where('analisisList.1.tahun', 2023)
            ->where('analisisList.1.jumlah_dokumen', 1)
        );
    }

    /** IT-044 (Positive): Index - Analisis baru otomatis dibuat via firstOrCreate */
    public function test_index_analisis_baru_otomatis_dibuat(): void
    {
        $perusahaan = Perusahaan::factory()->create();
        $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

        Dokumen::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'periode_type'  => 'annual',
            'tahun'         => 2025,
            'quarter'       => null,
            'bulan'         => null,
        ]);

        $this->assertDatabaseMissing('analisis', [
            'perusahaan_id' => $perusahaan->id,
            'tahun'         => 2025,
        ]);

        $response = $this->actingAs($user)->get("/perusahaan/{$perusahaan->id}/analisis");

        $response->assertInertia(fn ($page) => $page
            ->component('Perusahaan/Analisis/Index')
            ->has('analisisList', 1)
            ->where('analisisList.0.status', 'belum dihitung')
        );

        $this->assertDatabaseHas('analisis', [
            'perusahaan_id' => $perusahaan->id,
            'tahun'         => 2025,
            'status'        => 'belum dihitung',
        ]);
    }

    /** IT-045 (Positive): Index - status otomatis berubah 'Terjadi Perubahan Data!' saat dokumen diedit setelah analisis selesai */
    public function test_index_status_berubah_terjadi_perubahan_data(): void
    {
        $perusahaan = Perusahaan::factory()->create();
        $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

        $t0 = now()->subHours(2);

        $dokumen = Dokumen::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'periode_type'  => 'annual',
            'tahun'         => 2024,
            'quarter'       => null,
            'bulan'         => null,
        ]);
        $dokumen->timestamps = false;
        $dokumen->updated_at = $t0;
        $dokumen->save();

        $analisis = Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'periode_type'  => 'annual',
            'tahun'         => 2024,
            'quarter'       => null,
            'bulan'         => null,
            'status'        => 'sudah dihitung',
        ]);
        $analisis->timestamps = false;
        $analisis->updated_at = $t0;
        $analisis->save();

        // Dokumen diedit SETELAH analisis (updated_at lebih baru)
        $dokumen->timestamps = false;
        $dokumen->updated_at = $t0->copy()->addHour();
        $dokumen->save();

        $response = $this->actingAs($user)->get("/perusahaan/{$perusahaan->id}/analisis");

        $response->assertInertia(fn ($page) => $page
            ->component('Perusahaan/Analisis/Index')
            ->where('analisisList.0.status', 'Terjadi Perubahan Data!')
        );

        $this->assertEquals('Terjadi Perubahan Data!', $analisis->fresh()->status);
    }

    /** IT-046 (Positive): Detail - response berisi seluruh data rasio + 5 kategori trend + neraca/labaRugi terbaru */
    public function test_detail_response_lengkap(): void
    {
        $perusahaan = Perusahaan::factory()->create();
        $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

        // Periode lama (2023) - untuk memastikan trend punya >=2 periode
        $analisisLama = Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2023, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
        ]);
        $dokumenLama = Dokumen::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2023, 'quarter' => null, 'bulan' => null,
        ]);
        Neraca::factory()->create(['dokumen_id' => $dokumenLama->id]);
        LabaRugi::factory()->create(['dokumen_id' => $dokumenLama->id]);
        \App\Models\ArusKas::factory()->create(['dokumen_id' => $dokumenLama->id]);
        AnalisisLikuiditas::factory()->create(['analisis_id' => $analisisLama->id]);
        AnalisisProfitabilitas::factory()->create(['analisis_id' => $analisisLama->id]);
        AnalisisSolvabilitas::factory()->create(['analisis_id' => $analisisLama->id]);
        AnalisisAktivitas::factory()->create(['analisis_id' => $analisisLama->id]);
        AnalisisDupont::factory()->create(['analisis_id' => $analisisLama->id]);
        AnalisisCommonsize::factory()->create(['analisis_id' => $analisisLama->id]);

        // Periode target (2024)
        $analisis = Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2024, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
        ]);
        $dokumen = Dokumen::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2024, 'quarter' => null, 'bulan' => null,
        ]);
        Neraca::factory()->create(['dokumen_id' => $dokumen->id]);
        LabaRugi::factory()->create(['dokumen_id' => $dokumen->id]);
        \App\Models\ArusKas::factory()->create(['dokumen_id' => $dokumen->id]);
        AnalisisLikuiditas::factory()->create(['analisis_id' => $analisis->id]);
        AnalisisProfitabilitas::factory()->create(['analisis_id' => $analisis->id]);
        AnalisisSolvabilitas::factory()->create(['analisis_id' => $analisis->id]);
        AnalisisAktivitas::factory()->create(['analisis_id' => $analisis->id]);
        AnalisisDupont::factory()->create(['analisis_id' => $analisis->id]);
        AnalisisCommonsize::factory()->create(['analisis_id' => $analisis->id]);

        $response = $this->actingAs($user)
            ->get("/perusahaan/{$perusahaan->id}/analisis/{$analisis->id}");

        $response->assertInertia(fn ($page) => $page
            ->component('Perusahaan/Analisis/Detail')
            ->has('likuiditas')
            ->has('profitabilitas')
            ->has('solvabilitas')
            ->has('aktivitas')
            ->has('dupont')
            ->has('commonsize')
            ->has('trendAkunUtama.periode_data', 2)
            ->has('trendRasio.periode_data', 2)
            ->has('trendDupont.periode_data', 2)
            ->has('trendCommonsize.periode_data', 2)
            ->has('trendArusKas.periode_data', 2)
            ->has('neraca')
            ->has('labaRugi')
            ->where('neraca.id', $dokumen->neraca->id ?? null)
        );
    }
}