<?php

namespace Tests\Feature;

use App\Jobs\GenerateAnalisisJob;
use App\Models\Analisis;
use App\Models\Dokumen;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AnalisisControllerTest extends TestCase
{
    use RefreshDatabase;

    private function buatPerusahaan(): Perusahaan
    {
        return Perusahaan::create([
            'nama'   => 'PT Contoh Jasa',
            'sektor' => 'Jasa',
        ]);
    }

    private function buatDokumen(Perusahaan $perusahaan, array $overrides = []): Dokumen
    {
        return Dokumen::create(array_merge([
            'perusahaan_id' => $perusahaan->id,
            'nama_file'     => 'laporan.xlsx',
            'storage_path'  => 'dokumen-import/laporan.xlsx',
            'periode_type'  => 'annual',
            'tahun'         => 2024,
        ], $overrides));
    }

    private function buatAnalisis(Dokumen $dokumen, array $overrides = []): Analisis
    {
        return Analisis::create(array_merge([
            'dokumen_id' => $dokumen->id,
        ], $overrides));
    }

    private function login()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        return $user;
    }

    // -------------------------------------------------------------
    // IT: index - grouping & response dasar
    // -------------------------------------------------------------

    public function test_index_menampilkan_daftar_analisis_milik_perusahaan(): void
    {
        $this->withoutExceptionHandling();
        $this->login();
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumen($perusahaan);
        $this->buatAnalisis($dokumen);

        $response = $this->get("/perusahaan/{$perusahaan->id}/analisis");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Perusahaan/Analisis/Index')
            ->has('analisisList', 1)
        );
    }

    // -------------------------------------------------------------
    // IT: detail - 404 guard lintas perusahaan (perusahaan_id mismatch)
    // -------------------------------------------------------------

    public function test_detail_analisis_milik_perusahaan_lain_dapat_404(): void
    {
        $this->login();
        $perusahaanA = $this->buatPerusahaan();
        $perusahaanB = Perusahaan::create(['nama' => 'PT Lain', 'sektor' => 'Dagang']);

        $dokumenB = $this->buatDokumen($perusahaanB);
        $analisisB = $this->buatAnalisis($dokumenB);

        $response = $this->get("/perusahaan/{$perusahaanA->id}/analisis/{$analisisB->id}");

        $response->assertNotFound();
    }

    public function test_detail_analisis_milik_perusahaan_sendiri_berhasil(): void
    {
        
        $this->withoutExceptionHandling();
        $this->login();
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumen($perusahaan);
        $analisis = $this->buatAnalisis($dokumen);

        $response = $this->get("/perusahaan/{$perusahaan->id}/analisis/{$analisis->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Perusahaan/Analisis/Detail')
            ->where('analisis.id', $analisis->id)
        );
    }

    // -------------------------------------------------------------
    // IT: generateSeluruhAnalisis - guard status_generate & semuaSelesai, dispatch job
    // -------------------------------------------------------------

    public function test_generate_seluruh_analisis_dispatch_job_saat_status_idle(): void
    {
        Queue::fake();
        $this->login();
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumen($perusahaan);
        $analisis = $this->buatAnalisis($dokumen); // status_generate default 'idle'

        $response = $this->post("/perusahaan/{$perusahaan->id}/analisis/{$analisis->id}/generate");

        $response->assertRedirect();
        $response->assertSessionHas('success');
        Queue::assertPushed(GenerateAnalisisJob::class, function ($job) use ($analisis) {
            return $job->analisis->id === $analisis->id && $job->onlySection === null;
        });
    }

    public function test_generate_seluruh_analisis_ditolak_saat_sedang_processing(): void
    {
        Queue::fake();
        $this->login();
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumen($perusahaan);
        $analisis = $this->buatAnalisis($dokumen, ['status_generate' => 'processing']);

        $response = $this->post("/perusahaan/{$perusahaan->id}/analisis/{$analisis->id}/generate");

        $response->assertSessionHasErrors('message');
        Queue::assertNotPushed(GenerateAnalisisJob::class);
    }

    public function test_generate_seluruh_analisis_ditolak_saat_sudah_lengkap(): void
    {
        Queue::fake();
        $this->login();
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumen($perusahaan);

        // semuaSelesai() true kalau section_status semua 'selesai'/'tidak_berlaku' untuk 11 section
        $sectionStatusLengkap = collect(Analisis::SECTIONS)
            ->mapWithKeys(fn ($s) => [$s => ['status' => 'selesai', 'error_message' => null]])
            ->toArray();

        $analisis = $this->buatAnalisis($dokumen, [
            'status_generate' => 'selesai',
            'section_status'  => $sectionStatusLengkap,
        ]);

        $response = $this->post("/perusahaan/{$perusahaan->id}/analisis/{$analisis->id}/generate");

        $response->assertSessionHasErrors('message');
        Queue::assertNotPushed(GenerateAnalisisJob::class);
    }

    // -------------------------------------------------------------
    // IT: statusGenerate - JSON polling endpoint
    // -------------------------------------------------------------

    public function test_status_generate_mengembalikan_json_progress(): void
    {
        $this->login();
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumen($perusahaan);
        $analisis = $this->buatAnalisis($dokumen, [
            'status_generate'  => 'processing',
            'progress_current' => 3,
            'progress_total'   => 11,
        ]);

        $response = $this->get("/perusahaan/{$perusahaan->id}/analisis/{$analisis->id}/status");

        $response->assertOk();
        $response->assertJson([
            'status_generate'  => 'processing',
            'progress_current' => 3,
            'progress_total'   => 11,
        ]);
    }

    // -------------------------------------------------------------
    // IT: generateAnalisis (regenerasi 1 section) - validasi & guard
    // -------------------------------------------------------------

    public function test_regenerasi_section_ditolak_jika_belum_semua_selesai(): void
    {
        Queue::fake();
        $this->login();
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumen($perusahaan);
        $analisis = $this->buatAnalisis($dokumen); // section_status kosong -> semuaSelesai() false

        $response = $this->post(
            "/perusahaan/{$perusahaan->id}/analisis/{$analisis->id}/regenerasi",
            ['section' => 'likuiditas']
        );

        $response->assertSessionHasErrors('message');
        Queue::assertNotPushed(GenerateAnalisisJob::class);
    }

    public function test_regenerasi_section_tidak_valid_ditolak_validasi(): void
    {
        Queue::fake();
        $this->login();
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumen($perusahaan);
        $analisis = $this->buatAnalisis($dokumen);

        $response = $this->post(
            "/perusahaan/{$perusahaan->id}/analisis/{$analisis->id}/regenerasi",
            ['section' => 'trend_arus_kas'] // section ini sudah tidak ada di Analisis::SECTIONS
        );

        $response->assertSessionHasErrors('section');
        Queue::assertNotPushed(GenerateAnalisisJob::class);
    }

    public function test_regenerasi_section_valid_dispatch_job_saat_semua_selesai(): void
    {
        Queue::fake();
        $this->login();
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumen($perusahaan);

        $sectionStatusLengkap = collect(Analisis::SECTIONS)
            ->mapWithKeys(fn ($s) => [$s => ['status' => 'selesai', 'error_message' => null]])
            ->toArray();

        $analisis = $this->buatAnalisis($dokumen, [
            'status_generate' => 'selesai',
            'section_status'  => $sectionStatusLengkap,
        ]);

        $response = $this->post(
            "/perusahaan/{$perusahaan->id}/analisis/{$analisis->id}/regenerasi",
            ['section' => 'likuiditas', 'user_prompt' => 'Perpendek jadi 1 paragraf']
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');
        Queue::assertPushed(GenerateAnalisisJob::class, function ($job) use ($analisis) {
            return $job->analisis->id === $analisis->id
                && $job->onlySection === 'likuiditas'
                && $job->userPrompt === 'Perpendek jadi 1 paragraf';
        });
    }

    public function test_regenerasi_user_prompt_melebihi_1000_karakter_ditolak(): void
    {
        Queue::fake();
        $this->login();
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumen($perusahaan);

        $sectionStatusLengkap = collect(Analisis::SECTIONS)
            ->mapWithKeys(fn ($s) => [$s => ['status' => 'selesai', 'error_message' => null]])
            ->toArray();

        $analisis = $this->buatAnalisis($dokumen, [
            'status_generate' => 'selesai',
            'section_status'  => $sectionStatusLengkap,
        ]);

        $response = $this->post(
            "/perusahaan/{$perusahaan->id}/analisis/{$analisis->id}/regenerasi",
            ['section' => 'profitabilitas', 'user_prompt' => str_repeat('a', 1050)]
        );

        $response->assertSessionHasErrors('user_prompt');
        Queue::assertNotPushed(GenerateAnalisisJob::class);
    }
}