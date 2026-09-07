<?php

namespace Tests\Feature;

use App\Jobs\GenerateAnalisisJob;
use App\Models\Analisis;
use App\Models\Dokumen;
use App\Models\Perusahaan;
use App\Services\AnalysisFinancialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateAnalisisJobTest extends TestCase
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

    // -------------------------------------------------------------
    // IT: full run - periode pertama -> semua TREND_SECTIONS auto 'tidak_berlaku'
    // -------------------------------------------------------------

    public function test_full_run_periode_pertama_menandai_trend_sections_tidak_berlaku(): void
    {
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumen($perusahaan, ['tahun' => 2024]); // tidak ada dokumen tahun < 2024 -> periode pertama
        $analisis = $this->buatAnalisis($dokumen);

        // Mock service: setiap method proses* (non-trend, non-summary) sukses tanpa efek DB nyata
        $mock = $this->mock(AnalysisFinancialService::class, function ($mock) {
            $mock->shouldReceive('prosesLikuiditas')->once();
            $mock->shouldReceive('prosesProfitabilitas')->once();
            $mock->shouldReceive('prosesSolvabilitas')->once();
            $mock->shouldReceive('prosesAktivitas')->once();
            $mock->shouldReceive('prosesDupont')->once();
            $mock->shouldReceive('prosesCommonsize')->once();
        });

        $job = new GenerateAnalisisJob($analisis);
        $job->handle($mock);

        $analisis->refresh();

        foreach (Analisis::TREND_SECTIONS as $section) {
            $this->assertSame('tidak_berlaku', $analisis->sectionStatus($section)['status']);
        }

        $this->assertSame('selesai', $analisis->sectionStatus('likuiditas')['status']);
        $this->assertSame('selesai', $analisis->sectionStatus('profitabilitas')['status']);
        $this->assertSame(11, $analisis->progress_total);
        $this->assertSame(11, $analisis->progress_current);
    }

    // -------------------------------------------------------------
    // IT: full run - satu section AI melempar exception -> status_generate akhir 'gagal',
    // TAPI section lain tetap lanjut diproses (tidak berhenti di tengah)
    // -------------------------------------------------------------

    public function test_full_run_satu_section_gagal_tidak_menghentikan_section_lain(): void
    {
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumen($perusahaan, ['tahun' => 2024]);
        $analisis = $this->buatAnalisis($dokumen);

        $mock = $this->mock(AnalysisFinancialService::class, function ($mock) {
            $mock->shouldReceive('prosesLikuiditas')->once();
            $mock->shouldReceive('prosesProfitabilitas')
                ->once()
                ->andThrow(new \RuntimeException('AI provider tidak dapat diakses'));
            $mock->shouldReceive('prosesSolvabilitas')->once();
            $mock->shouldReceive('prosesAktivitas')->once();
            $mock->shouldReceive('prosesDupont')->once();
            $mock->shouldReceive('prosesCommonsize')->once();
        });

        $job = new GenerateAnalisisJob($analisis);
        $job->handle($mock);

        $analisis->refresh();

        $this->assertSame('selesai', $analisis->sectionStatus('likuiditas')['status']);
        $this->assertSame('gagal', $analisis->sectionStatus('profitabilitas')['status']);
        $this->assertSame('AI provider tidak dapat diakses', $analisis->sectionStatus('profitabilitas')['error_message']);
        $this->assertSame('selesai', $analisis->sectionStatus('solvabilitas')['status']); // lanjut, tidak berhenti

        // Karena ada section 'gagal', semuaSelesai() false -> status_generate akhir 'gagal'
        $this->assertSame('gagal', $analisis->status_generate);
        $this->assertSame(11, $analisis->progress_current); // progress tetap jalan sampai akhir meski ada yang gagal
    }

    // -------------------------------------------------------------
    // IT: regenerasi 1 section (onlySection) - TIDAK menyentuh progress_current/total/status_generate
    // -------------------------------------------------------------

    public function test_regenerasi_satu_section_tidak_mengubah_progress_dan_status_generate_global(): void
    {
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumen($perusahaan, ['tahun' => 2024]);

        $sectionStatusLengkap = collect(Analisis::SECTIONS)
            ->mapWithKeys(fn ($s) => [$s => ['status' => 'selesai', 'error_message' => null]])
            ->toArray();

        $analisis = $this->buatAnalisis($dokumen, [
            'status_generate'  => 'selesai',
            'progress_current' => 11,
            'progress_total'   => 11,
            'section_status'   => $sectionStatusLengkap,
        ]);

        $mock = $this->mock(AnalysisFinancialService::class, function ($mock) {
            $mock->shouldReceive('prosesLikuiditas')->once()->with(\Mockery::any(), 'Perpendek jadi 1 paragraf');
        });

        $job = new GenerateAnalisisJob($analisis, 'likuiditas', 'Perpendek jadi 1 paragraf');
        $job->handle($mock);

        $analisis->refresh();

        $this->assertSame('selesai', $analisis->sectionStatus('likuiditas')['status']);
        // Tidak full run -> progress & status_generate global tidak disentuh sama sekali
        $this->assertSame(11, $analisis->progress_current);
        $this->assertSame('selesai', $analisis->status_generate);
    }

    // -------------------------------------------------------------
    // IT: regenerasi section 'summary' - guard siapUntukSummary() di dalam job
    // -------------------------------------------------------------

    public function test_regenerasi_summary_gagal_jika_section_wajib_belum_lengkap(): void
    {
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumen($perusahaan, ['tahun' => 2024]);

        // Section wajib (selain trend) belum 'selesai' semua -> siapUntukSummary() false
        $analisis = $this->buatAnalisis($dokumen, [
            'status_generate' => 'selesai',
            'section_status'  => [
                'likuiditas' => ['status' => 'selesai', 'error_message' => null],
            ],
        ]);

        // prosesSummaryAnalisis TIDAK BOLEH terpanggil sama sekali
        $mock = $this->mock(AnalysisFinancialService::class, function ($mock) {
            $mock->shouldNotReceive('prosesSummaryAnalisis');
        });

        $job = new GenerateAnalisisJob($analisis, 'summary');
        $job->handle($mock);

        $analisis->refresh();

        $this->assertSame('gagal', $analisis->sectionStatus('summary')['status']);
        $this->assertStringContainsString('belum lengkap', $analisis->sectionStatus('summary')['error_message']);
    }

    // -------------------------------------------------------------
    // IT: failed() handler - full run vs onlySection
    // -------------------------------------------------------------

    public function test_failed_handler_full_run_set_status_generate_gagal(): void
    {
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumen($perusahaan, ['tahun' => 2024]);
        $analisis = $this->buatAnalisis($dokumen, ['status_generate' => 'processing']);

        $job = new GenerateAnalisisJob($analisis);
        $job->failed(new \RuntimeException('Timeout queue worker'));

        $analisis->refresh();

        $this->assertSame('gagal', $analisis->status_generate);
        $this->assertSame('Timeout queue worker', $analisis->error_message);
    }

    public function test_failed_handler_onlysection_hanya_set_section_status(): void
    {
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumen($perusahaan, ['tahun' => 2024]);
        $analisis = $this->buatAnalisis($dokumen, ['status_generate' => 'selesai']);

        $job = new GenerateAnalisisJob($analisis, 'dupont');
        $job->failed(new \RuntimeException('Agent AI down'));

        $analisis->refresh();

        $this->assertSame('gagal', $analisis->sectionStatus('dupont')['status']);
        $this->assertSame('Agent AI down', $analisis->sectionStatus('dupont')['error_message']);
        // status_generate GLOBAL tidak ikut berubah untuk kasus onlySection
        $this->assertSame('selesai', $analisis->status_generate);
    }
}