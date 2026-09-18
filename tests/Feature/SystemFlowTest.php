<?php

namespace Tests\Feature;

use App\Jobs\GenerateAnalisisJob;
use App\Models\Analisis;
use App\Models\Dokumen;
use App\Models\LabaRugi;
use App\Models\Neraca;
use App\Models\Perusahaan;
use App\Models\User;
use App\Services\AnalysisFinancialService;
use App\Services\CalculateFinancialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Implementasi kode dari sheet "System testing" (ST-001 s.d. ST-007) pada
 * Test_Case.xlsx.
 *
 * CATATAN PENTING soal strategi testing:
 * - ST-002, ST-003, ST-007 murni menguji GUARD di controller SEBELUM job
 *   dijalankan -> pakai Queue::fake() + HTTP request biasa, job TIDAK
 *   benar-benar dieksekusi.
 * - ST-001, ST-004, ST-005, ST-006 menguji ORKESTRASI di dalam
 *   GenerateAnalisisJob::handle() (loop section, progress, try/catch,
 *   status_generate) -> job dieksekusi LANGSUNG lewat pemanggilan method
 *   ->handle($fakeService), BUKAN lewat dispatch queue asli. Ini supaya
 *   test tidak benar-benar memanggil AI/LLM (mahal, lambat, flaky).
 *   $fakeService adalah FakeAnalysisFinancialService (lihat bawah file
 *   ini) yang menulis narasi dummy langsung ke DB tanpa manggil Agent
 *   apapun, tapi tetap menjaga logic asli GenerateAnalisisJob 100% utuh
 *   (yang diuji adalah job-nya, bukan service-nya -- service sudah
 *   dites terpisah di Unit/Integration Test).
 *
 * ASUMSI:
 * - database/factories/UserFactory.php ada & bisa isi kolom 'role' dan
 *   'perusahaan_id' (dipakai DashboardController). Kalau nama kolom beda,
 *   sesuaikan buatUser() di bawah.
 * - RefreshDatabase aman dipakai (phpunit.xml sudah arahkan ke DB testing).
 */
class SystemFlowTest extends TestCase
{
    use RefreshDatabase;

    private function buatPerusahaan(string $nama = 'PT Contoh Jasa'): Perusahaan
    {
        return Perusahaan::create(['nama' => $nama, 'sektor' => 'Jasa']);
    }

    private function buatUser(Perusahaan $perusahaan): User
    {
        return User::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'role'          => 'user',
        ]);
    }

    private function buatDokumenDenganLaporan(
        Perusahaan $perusahaan,
        string $periodeType,
        int $tahun,
        ?int $quarter = null,
        ?int $bulan = null
    ): Dokumen {
        $dokumen = Dokumen::create([
            'perusahaan_id' => $perusahaan->id,
            'nama_file'     => 'dummy.xlsx',
            'storage_path'  => 'dummy/path.xlsx',
            'periode_type'  => $periodeType,
            'tahun'         => $tahun,
            'quarter'       => $quarter,
            'bulan'         => $bulan,
        ]);

        Neraca::create([
            'dokumen_id'                => $dokumen->id,
            'total_kas_setara_kas'      => 200000000,
            'total_asset_lancar'        => 500000000,
            'total_asset_tetap'         => 500000000,
            'total_asset'               => 1000000000,
            'total_liabilities_pendek'  => 250000000,
            'total_liabilities_panjang' => 100000000,
            'total_liabilities'         => 350000000,
            'total_equitas'             => 400000000,
        ]);

        LabaRugi::create([
            'dokumen_id'                => $dokumen->id,
            'total_beban'               => -700000000,
            'total_biaya_pajak'         => -20000000,
            'total_pendapatan'          => 800000000,
            'laba_bersih_sebelum_pajak' => 100000000,
            'laba_bersih_sesudah_pajak' => 80000000,
        ]);

        return $dokumen;
    }

    private function buatAnalisisSiapGenerate(Dokumen $dokumen): Analisis
    {
        $analisis = Analisis::create(['dokumen_id' => $dokumen->id]);

        // hitungSemuaRasio() beneran dijalankan supaya kolom rasio terisi
        // (bukan wajib buat orkestrasi job, tapi lebih representatif).
        (new AnalysisFinancialService(new CalculateFinancialService()))
            ->hitungSemuaRasio($analisis, $dokumen->neraca, $dokumen->labaRugi);

        return $analisis->refresh();
    }

    // =====================================================================
    // ST-001: Happy path end-to-end
    // =====================================================================
    public function test_st001_generate_seluruh_section_berhasil(): void
    {
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumenDenganLaporan($perusahaan, 'annual', 2024);
        $analisis = $this->buatAnalisisSiapGenerate($dokumen);

        $job = new GenerateAnalisisJob($analisis);
        $job->handle(new FakeAnalysisFinancialService());

        $analisis->refresh();

        $this->assertSame('selesai', $analisis->status_generate);
        $this->assertSame($analisis->progress_total, $analisis->progress_current);
        $this->assertTrue($analisis->semuaSelesai());
        $this->assertNotEmpty($analisis->ringkasan_laporan);

        foreach (Analisis::SECTIONS as $section) {
            $this->assertContains(
                $analisis->sectionStatus($section)['status'],
                ['selesai', 'tidak_berlaku'],
                "Section '{$section}' seharusnya 'selesai' atau 'tidak_berlaku'."
            );
        }

        // Dokumen ini periode pertama utk perusahaannya -> trend section
        // seharusnya 'tidak_berlaku' (di-skip), bukan 'selesai'.
        foreach (Analisis::TREND_SECTIONS as $trendSection) {
            $this->assertSame('tidak_berlaku', $analisis->sectionStatus($trendSection)['status']);
        }
    }

    // =====================================================================
    // ST-002: Guard generate ganda (double submit)
    // =====================================================================
    public function test_st002_generate_ditolak_saat_masih_processing(): void
    {
        Queue::fake();

        $perusahaan = $this->buatPerusahaan();
        $user = $this->buatUser($perusahaan);
        $dokumen = $this->buatDokumenDenganLaporan($perusahaan, 'annual', 2024);
        $analisis = Analisis::create([
            'dokumen_id'      => $dokumen->id,
            'status_generate' => 'processing',
        ]);

        $response = $this->actingAs($user)
            ->post(route('analisis.generate', [$perusahaan->id, $analisis->id]));

        $response->assertRedirect();
        $response->assertSessionHasErrors('message');
        Queue::assertNotPushed(GenerateAnalisisJob::class);
    }

    // =====================================================================
    // ST-003: Guard regenerasi sebelum semuaSelesai()
    // =====================================================================
    public function test_st003_regenerasi_ditolak_sebelum_semua_selesai(): void
    {
        Queue::fake();

        $perusahaan = $this->buatPerusahaan();
        $user = $this->buatUser($perusahaan);
        $dokumen = $this->buatDokumenDenganLaporan($perusahaan, 'annual', 2024);
        $analisis = Analisis::create(['dokumen_id' => $dokumen->id]); // section_status default -> semuaSelesai() = false

        $response = $this->actingAs($user)
            ->post(route('perusahaan.analisis.regenerasi', [$perusahaan->id, $analisis->id]), [
                'section' => 'likuiditas',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('message');
        Queue::assertNotPushed(GenerateAnalisisJob::class);
    }

    // =====================================================================
    // ST-004 (regression): trend TETAP jalan utk quarterly, periode sama tahun
    // =====================================================================
    public function test_st004_trend_tetap_jalan_untuk_quarterly_tahun_sama(): void
    {
        $perusahaan = $this->buatPerusahaan();

        // Dokumen Q1 2024 cukup ada (tidak perlu Analisis lengkap) -- yang
        // dites cuma Analisis::apakahPeriodePertama() lihat KEBERADAAN
        // Dokumen periode_type sama yang lebih lama, bukan status generate-nya.
        $this->buatDokumenDenganLaporan($perusahaan, 'quarterly', 2024, quarter: 1);

        $dokumenQ2 = $this->buatDokumenDenganLaporan($perusahaan, 'quarterly', 2024, quarter: 2);
        $analisisQ2 = $this->buatAnalisisSiapGenerate($dokumenQ2);

        $this->assertFalse($analisisQ2->apakahPeriodePertama());

        (new GenerateAnalisisJob($analisisQ2))->handle(new FakeAnalysisFinancialService());
        $analisisQ2->refresh();

        foreach (Analisis::TREND_SECTIONS as $trendSection) {
            $this->assertSame(
                'selesai',
                $analisisQ2->sectionStatus($trendSection)['status'],
                "Section '{$trendSection}' seharusnya 'selesai' (ada Q1 sbg pembanding), BUKAN 'tidak_berlaku'."
            );
        }
        $this->assertNotNull($analisisQ2->trend);
        $this->assertNotEmpty($analisisQ2->trend->narasi_trend_rasio_AI);
    }

    // =====================================================================
    // ST-005: Ketahanan proses - 1 section gagal, section lain tetap jalan
    // =====================================================================
    public function test_st005_satu_section_gagal_tidak_menghentikan_yang_lain(): void
    {
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumenDenganLaporan($perusahaan, 'annual', 2024);
        $analisis = $this->buatAnalisisSiapGenerate($dokumen);

        $fakeService = new FakeAnalysisFinancialService(failSections: ['profitabilitas']);
        (new GenerateAnalisisJob($analisis))->handle($fakeService);

        $analisis->refresh();

        $this->assertSame('gagal', $analisis->sectionStatus('profitabilitas')['status']);
        $this->assertNotNull($analisis->sectionStatus('profitabilitas')['error_message']);

        // Section lain (bukan profitabilitas, bukan summary yg ikut gagal
        // karena profitabilitas wajib buat siapUntukSummary()) tetap selesai.
        foreach (['likuiditas', 'solvabilitas', 'aktivitas', 'dupont', 'commonsize'] as $section) {
            $this->assertSame('selesai', $analisis->sectionStatus($section)['status']);
        }

        $this->assertSame('gagal', $analisis->status_generate);
        $this->assertFalse($analisis->semuaSelesai());
    }

    // =====================================================================
    // ST-006: Guard summary ditunda kalau section wajib belum lengkap
    // =====================================================================
    public function test_st006_summary_ditunda_jika_commonsize_belum_lengkap(): void
    {
        $perusahaan = $this->buatPerusahaan();
        $dokumen = $this->buatDokumenDenganLaporan($perusahaan, 'annual', 2024);
        $analisis = $this->buatAnalisisSiapGenerate($dokumen);

        $fakeService = new FakeAnalysisFinancialService(failSections: ['commonsize']);
        (new GenerateAnalisisJob($analisis))->handle($fakeService);

        $analisis->refresh();

        $this->assertSame('gagal', $analisis->sectionStatus('commonsize')['status']);
        $this->assertSame('gagal', $analisis->sectionStatus('summary')['status']);
        $this->assertStringContainsString(
            'Section wajib',
            $analisis->sectionStatus('summary')['error_message'] ?? ''
        );
        $this->assertEmpty($analisis->ringkasan_laporan);
    }

    // =====================================================================
    // ST-007: Celah otorisasi lintas-perusahaan (EXPECTED TO FAIL SAAT INI)
    // =====================================================================
    /**
     * Test ini sengaja ditulis mengikuti PERILAKU YANG BENAR (analisis milik
     * perusahaan lain seharusnya ditolak). Sampai celah otorisasi di
     * AnalisisController diperbaiki, test ini akan GAGAL (merah) -- itu
     * ekspektasi yang benar, bukan test yang salah tulis. Begitu di-fix,
     * test ini akan lolos (hijau) tanpa perlu diubah lagi.
     */
    public function test_st007_analisis_lintas_perusahaan_seharusnya_ditolak(): void
    {
        Queue::fake();

        $perusahaanA = $this->buatPerusahaan('PT A');
        $perusahaanB = $this->buatPerusahaan('PT B');
        $userA = $this->buatUser($perusahaanA);

        $dokumenB = $this->buatDokumenDenganLaporan($perusahaanB, 'annual', 2024);
        $analisisB = Analisis::create(['dokumen_id' => $dokumenB->id]);

        $response = $this->actingAs($userA)
            ->post(route('analisis.generate', [$perusahaanA->id, $analisisB->id]));

        $response->assertStatus(404);
        Queue::assertNotPushed(GenerateAnalisisJob::class);
    }
}

/**
 * Test double untuk AnalysisFinancialService -- override semua method
 * prosesXxx() supaya menulis narasi dummy langsung ke DB tanpa memanggil
 * Agent/AI apapun. Dipakai HANYA di SystemFlowTest untuk menguji orkestrasi
 * GenerateAnalisisJob (loop, progress, try/catch), bukan isi narasinya.
 */
class FakeAnalysisFinancialService extends AnalysisFinancialService
{
    /** @param string[] $failSections Section yang sengaja dibuat throw Exception. */
    public function __construct(private array $failSections = [])
    {
        parent::__construct(new CalculateFinancialService());
    }

    private function tulisNarasiDummy(Analisis $analisis, string $section, string $relasi, string $kolom): void
    {
        if (in_array($section, $this->failSections, true)) {
            throw new \RuntimeException("Simulasi kegagalan AI pada section '{$section}'.");
        }

        $analisis->{$relasi}()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [$kolom => "Narasi dummy - {$section}."]
        );
    }

    public function prosesLikuiditas(Analisis $analisis, ?string $userPrompt = null): void
    {
        $this->tulisNarasiDummy($analisis, 'likuiditas', 'likuiditas', 'narasi_likuiditas_AI');
    }

    public function prosesProfitabilitas(Analisis $analisis, ?string $userPrompt = null): void
    {
        $this->tulisNarasiDummy($analisis, 'profitabilitas', 'profitabilitas', 'narasi_profitabilitas_AI');
    }

    public function prosesSolvabilitas(Analisis $analisis, ?string $userPrompt = null): void
    {
        $this->tulisNarasiDummy($analisis, 'solvabilitas', 'solvabilitas', 'narasi_solvabilitas_AI');
    }

    public function prosesAktivitas(Analisis $analisis, ?string $userPrompt = null): void
    {
        $this->tulisNarasiDummy($analisis, 'aktivitas', 'aktivitas', 'narasi_aktivitas_AI');
    }

    public function prosesDupont(Analisis $analisis, ?string $userPrompt = null): void
    {
        $this->tulisNarasiDummy($analisis, 'dupont', 'dupont', 'narasi_dupont_AI');
    }

    public function prosesCommonsize(Analisis $analisis, ?string $userPrompt = null): void
    {
        $this->tulisNarasiDummy($analisis, 'commonsize', 'commonsize', 'narasi_commonsize_AI');
    }

    public function prosesTrendAkunUtama(Analisis $analisis, ?string $userPrompt = null): void
    {
        $this->tulisNarasiDummy($analisis, 'trend_akun_utama', 'trend', 'narasi_trend_akun_utama_AI');
    }

    public function prosesTrendRasio(Analisis $analisis, ?string $userPrompt = null): void
    {
        $this->tulisNarasiDummy($analisis, 'trend_rasio', 'trend', 'narasi_trend_rasio_AI');
    }

    public function prosesTrendDupont(Analisis $analisis, ?string $userPrompt = null): void
    {
        $this->tulisNarasiDummy($analisis, 'trend_dupont', 'trend', 'narasi_trend_dupont_AI');
    }

    public function prosesTrendCommonsize(Analisis $analisis, ?string $userPrompt = null): void
    {
        $this->tulisNarasiDummy($analisis, 'trend_commonsize', 'trend', 'narasi_trend_commonsize_AI');
    }

    public function prosesSummaryAnalisis(Analisis $analisis, ?string $userPrompt = null): void
    {
        if (in_array('summary', $this->failSections, true)) {
            throw new \RuntimeException("Simulasi kegagalan AI pada section 'summary'.");
        }

        $analisis->update(['ringkasan_laporan' => 'Executive Summary dummy.']);
    }
}