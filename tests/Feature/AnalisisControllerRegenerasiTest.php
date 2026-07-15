<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Perusahaan;
use App\Models\Analisis;
use App\Models\AnalisisLikuiditas;
use App\Neuron\RAG\LiquidityAnalystAgent;
use NeuronAI\Chat\Messages\UserMessage;
use Mockery;

class AnalisisControllerRegenerasiTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** IT-005 (Positive): Regenerasi section 'likuiditas' berhasil */
    // public function test_regenerasi_likuiditas_berhasil(): void
    // {
    //     // --- Rantai mock: LiquidityAnalystAgent::make()->chat($msg)->getMessage()->getContent() ---
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')
    //         ->once()
    //         ->andReturn('Narasi likuiditas dummy hasil AI.');

    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')
    //         ->once()
    //         ->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . LiquidityAnalystAgent::class);
    //     $agentMock->shouldReceive('make')
    //         ->once()
    //         ->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')
    //         ->once()
    //         ->with(Mockery::type(UserMessage::class))
    //         ->andReturn($responseMock);

    //     // --- Setup data ---
    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'role'          => 'user',
    //     ]);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'status'        => 'sudah dihitung',
    //     ]);

    //     $likuiditas = AnalisisLikuiditas::factory()->create([
    //         'analisis_id'   => $analisis->id,
    //         'current_ratio' => 2.00,
    //         'quick_ratio'   => 1.60,
    //         'cash_ratio'    => 0.20,
    //     ]);

    //     // --- Act ---
    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'likuiditas',
    //         ]);

    //     // --- Assert ---
    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();

    //     $likuiditas->refresh();
    //     $this->assertEquals('Narasi likuiditas dummy hasil AI.', $likuiditas->narasi_likuiditas_AI);
    //     // pastikan angka rasio TIDAK berubah (cuma narasi yang diupdate)
    //     $this->assertEquals(2.00, $likuiditas->current_ratio);
    //     $this->assertEquals(1.60, $likuiditas->quick_ratio);
    //     $this->assertEquals(0.20, $likuiditas->cash_ratio);

        
    // }

    // /** IT-006 (Negative): Regenerasi gagal - section tidak valid */
    // public function test_regenerasi_gagal_section_tidak_valid(): void
    // {
    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'role'          => 'user',
    //     ]);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'status'        => 'sudah dihitung',
    //     ]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'rasio_ajaib',
    //         ]);

    //     $response->assertSessionHasErrors('section');
    // }

    // /** IT-007 (Negative): Regenerasi gagal - user_prompt melebihi 1000 karakter */
    // public function test_regenerasi_gagal_user_prompt_terlalu_panjang(): void
    // {
    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'role'          => 'user',
    //     ]);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'status'        => 'sudah dihitung',
    //     ]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section'     => 'profitabilitas',
    //             'user_prompt' => str_repeat('a', 1050),
    //         ]);

    //     $response->assertSessionHasErrors('user_prompt');
    // }

    // /** IT-008 (Negative): Regenerasi gagal - status analisis masih 'belum dihitung' */
    // public function test_regenerasi_gagal_status_belum_dihitung(): void
    // {
    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'role'          => 'user',
    //     ]);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'status'        => 'belum dihitung',
    //     ]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'likuiditas',
    //         ]);

    //     $response->assertSessionHasErrors('message');
    // }

    // /** IT-009 (Positive): Regenerasi section 'trend_akun_utama' berhasil */
    // public function test_regenerasi_trend_akun_utama_berhasil(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')
    //         ->once()
    //         ->andReturn('Narasi trend akun utama dummy hasil AI.');

    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')
    //         ->once()
    //         ->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\TrendAkunUtamaAgent::class);
    //     $agentMock->shouldReceive('make')
    //         ->once()
    //         ->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')
    //         ->once()
    //         ->with(Mockery::type(UserMessage::class))
    //         ->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'role'          => 'user',
    //     ]);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2024,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //         'status'        => 'sudah dihitung',
    //     ]);

    //     $dokumen = \App\Models\Dokumen::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2024,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //     ]);

    //     \App\Models\Neraca::factory()->create([
    //         'dokumen_id'     => $dokumen->id,
    //         'total_assets'   => 1000000000,
    //         'total_equity'   => 400000000,
    //     ]);

    //     \App\Models\LabaRugi::factory()->create([
    //         'dokumen_id'  => $dokumen->id,
    //         'pendapatan'  => 800000000,
    //         'laba_bersih' => 80000000,
    //     ]);

    //     \App\Models\ArusKas::factory()->create([
    //         'dokumen_id' => $dokumen->id,
    //         'kas_masuk'  => 500000000,
    //         'kas_keluar' => 300000000,
    //     ]);

    //     // Pastikan belum ada baris analisis_trend sama sekali sebelum request
    //     $this->assertDatabaseMissing('analisis_trend', ['analisis_id' => $analisis->id]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'trend_akun_utama',
    //         ]);

    //     if ($response->exception) {
    //         dump($response->exception->getMessage());
    //         dump($response->exception->getFile() . ':' . $response->exception->getLine());
    //         dump($response->exception->getTraceAsString());
    //     }

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();

    //     $this->assertDatabaseHas('analisis_trend', [
    //         'analisis_id'                => $analisis->id,
    //         'narasi_trend_akun_utama_AI' => 'Narasi trend akun utama dummy hasil AI.',
    //     ]);

    //     $trend = $analisis->trend()->first();
    //     // narasi trend lain belum diproses -> harus tetap null
    //     $this->assertNull($trend->narasi_trend_rasio_AI);
    //     $this->assertNull($trend->narasi_trend_dupont_AI);
    //     $this->assertNull($trend->narasi_trend_commonsize_AI);
    //     $this->assertNull($trend->narasi_trend_arus_kas_AI);
    // }

    // /** IT-010 (Positive, REVISI): Regenerasi section 'summary' - narasi prasyarat lengkap, sukses */
    // public function test_regenerasi_summary_berhasil(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Executive summary dummy hasil AI.');

    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\SummaryAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')->once()->with(Mockery::type(UserMessage::class))->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'status'        => 'sudah dihitung',
    //     ]);

    //     // Prasyarat: 4 narasi rasio utama + narasi_trend_rasio_AI harus terisi (guard $hasNarasi)
    //     AnalisisLikuiditas::factory()->create([
    //         'analisis_id'          => $analisis->id,
    //         'narasi_likuiditas_AI' => 'Narasi likuiditas sudah ada.',
    //     ]);
    //     \App\Models\AnalisisProfitabilitas::factory()->create([
    //         'analisis_id'             => $analisis->id,
    //         'narasi_profitabilitas_AI' => 'Narasi profitabilitas sudah ada.',
    //     ]);
    //     \App\Models\AnalisisSolvabilitas::factory()->create([
    //         'analisis_id'           => $analisis->id,
    //         'narasi_solvabilitas_AI' => 'Narasi solvabilitas sudah ada.',
    //     ]);
    //     \App\Models\AnalisisAktivitas::factory()->create([
    //         'analisis_id'        => $analisis->id,
    //         'narasi_aktivitas_AI' => 'Narasi aktivitas sudah ada.',
    //     ]);
    //     $analisis->trend()->create([
    //         'narasi_trend_rasio_AI' => 'Narasi trend rasio sudah ada.',
    //     ]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'summary',
    //         ]);

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertEquals('Executive summary dummy hasil AI.', $analisis->fresh()->AI_summary_insight);
    // }

    // /** IT-011 (Negative, tambahan): Regenerasi gagal - section 'summary' tapi narasi prasyarat belum lengkap */
    // public function test_regenerasi_summary_gagal_narasi_belum_lengkap(): void
    // {
    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'status'        => 'sudah dihitung',
    //     ]);

    //     // Sengaja TIDAK isi narasi apapun -> $hasNarasi harus false
    //     AnalisisLikuiditas::factory()->create(['analisis_id' => $analisis->id]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'summary',
    //         ]);

    //     $response->assertSessionHasErrors('message');
    //     $this->assertNull($analisis->fresh()->AI_summary_insight);
    // }
    // /** IT-012 (Negative): Regenerasi gagal - Agent AI melempar exception (provider tidak dapat diakses) */
    // public function test_regenerasi_gagal_agent_exception(): void
    // {
    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\LiquidityAnalystAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')
    //         ->once()
    //         ->with(Mockery::type(UserMessage::class))
    //         ->andThrow(new \Exception('Provider tidak dapat diakses'));

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'status'        => 'sudah dihitung',
    //     ]);

    //     $likuiditas = AnalisisLikuiditas::factory()->create([
    //         'analisis_id'          => $analisis->id,
    //         'narasi_likuiditas_AI' => 'Narasi lama sebelum error.',
    //     ]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'likuiditas',
    //         ]);

    //     $response->assertStatus(500);

    //     $likuiditas->refresh();
    //     $this->assertEquals('Narasi lama sebelum error.', $likuiditas->narasi_likuiditas_AI);
    // }

    // /** IT-013 (Negative): Regenerasi gagal - relasi dupont belum ada di DB meski status sudah sesuai */
    // public function test_regenerasi_gagal_dupont_relasi_belum_ada(): void
    // {
    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     // Status diset manual jadi 'sudah dihitung' TANPA hitung-rasio -> analisis_dupont belum ada
    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'status'        => 'sudah dihitung',
    //     ]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'dupont',
    //         ]);

    //     $response->assertStatus(500);
    //     $this->assertDatabaseMissing('analisis_dupont', ['analisis_id' => $analisis->id]);
    // }

    // /** IT-016 (Positive): Regenerasi Tren Akun Utama sukses - 2 periode data lengkap */
    // public function test_regenerasi_trend_akun_utama_dua_periode(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Narasi trend akun utama 2 periode dummy.');

    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\TrendAkunUtamaAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')->once()->with(Mockery::type(UserMessage::class))->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     // --- Periode lama (2023) ---
    //     Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2023,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //         'status'        => 'sudah dihitung',
    //     ]);
    //     $dokumenLama = \App\Models\Dokumen::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2023,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //     ]);
    //     \App\Models\Neraca::factory()->create([
    //         'dokumen_id'   => $dokumenLama->id,
    //         'total_assets' => 800000000,
    //         'total_equity' => 300000000,
    //     ]);
    //     \App\Models\LabaRugi::factory()->create([
    //         'dokumen_id'  => $dokumenLama->id,
    //         'pendapatan'  => 600000000,
    //         'laba_bersih' => 50000000,
    //     ]);
    //     \App\Models\ArusKas::factory()->create([
    //         'dokumen_id' => $dokumenLama->id,
    //         'kas_masuk'  => 400000000,
    //         'kas_keluar' => 250000000,
    //     ]);

    //     // --- Periode terbaru (2024) ---
    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2024,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //         'status'        => 'sudah dihitung',
    //     ]);
    //     $dokumen = \App\Models\Dokumen::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2024,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //     ]);
    //     \App\Models\Neraca::factory()->create([
    //         'dokumen_id'   => $dokumen->id,
    //         'total_assets' => 1000000000,
    //         'total_equity' => 400000000,
    //     ]);
    //     \App\Models\LabaRugi::factory()->create([
    //         'dokumen_id'  => $dokumen->id,
    //         'pendapatan'  => 800000000,
    //         'laba_bersih' => 80000000,
    //     ]);
    //     \App\Models\ArusKas::factory()->create([
    //         'dokumen_id' => $dokumen->id,
    //         'kas_masuk'  => 500000000,
    //         'kas_keluar' => 300000000,
    //     ]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'trend_akun_utama',
    //         ]);

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertDatabaseHas('analisis_trend', [
    //         'analisis_id'                => $analisis->id,
    //         'narasi_trend_akun_utama_AI' => 'Narasi trend akun utama 2 periode dummy.',
    //     ]);
    // }
    // /** IT-017 (Positive): Regenerasi Tren Rasio sukses - 2 periode */
    // public function test_regenerasi_trend_rasio_berhasil(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Narasi trend rasio dummy hasil AI.');

    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\TrendRasioAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')->once()->with(Mockery::type(UserMessage::class))->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisisLama = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2023,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //         'status'        => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisLikuiditas::factory()->create(['analisis_id' => $analisisLama->id]);
    //     \App\Models\AnalisisProfitabilitas::factory()->create(['analisis_id' => $analisisLama->id]);
    //     \App\Models\AnalisisSolvabilitas::factory()->create(['analisis_id' => $analisisLama->id]);
    //     \App\Models\AnalisisAktivitas::factory()->create(['analisis_id' => $analisisLama->id]);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2024,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //         'status'        => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisLikuiditas::factory()->create(['analisis_id' => $analisis->id]);
    //     \App\Models\AnalisisProfitabilitas::factory()->create(['analisis_id' => $analisis->id]);
    //     \App\Models\AnalisisSolvabilitas::factory()->create(['analisis_id' => $analisis->id]);
    //     \App\Models\AnalisisAktivitas::factory()->create(['analisis_id' => $analisis->id]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'trend_rasio',
    //         ]);

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertDatabaseHas('analisis_trend', [
    //         'analisis_id'           => $analisis->id,
    //         'narasi_trend_rasio_AI' => 'Narasi trend rasio dummy hasil AI.',
    //     ]);
    // }

    // /** IT-018 (Positive): Regenerasi Tren DuPont sukses - 2 periode */
    // public function test_regenerasi_trend_dupont_berhasil(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Narasi trend dupont dummy hasil AI.');

    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\TrendDupontAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')->once()->with(Mockery::type(UserMessage::class))->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisisLama = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2023,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //         'status'        => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisDupont::factory()->create(['analisis_id' => $analisisLama->id]);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2024,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //         'status'        => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisDupont::factory()->create(['analisis_id' => $analisis->id]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'trend_dupont',
    //         ]);

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertDatabaseHas('analisis_trend', [
    //         'analisis_id'            => $analisis->id,
    //         'narasi_trend_dupont_AI' => 'Narasi trend dupont dummy hasil AI.',
    //     ]);
    // }

    // /** IT-019 (Positive): Regenerasi Tren Common-size sukses - 2 periode */
    // public function test_regenerasi_trend_commonsize_berhasil(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Narasi trend commonsize dummy hasil AI.');

    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\TrendCommonsizeAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')->once()->with(Mockery::type(UserMessage::class))->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisisLama = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2023,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //         'status'        => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisCommonsize::factory()->create(['analisis_id' => $analisisLama->id]);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2024,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //         'status'        => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisCommonsize::factory()->create(['analisis_id' => $analisis->id]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'trend_commonsize',
    //         ]);

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertDatabaseHas('analisis_trend', [
    //         'analisis_id'                => $analisis->id,
    //         'narasi_trend_commonsize_AI' => 'Narasi trend commonsize dummy hasil AI.',
    //     ]);
    // }

    // /** IT-020 (Positive): Regenerasi Tren Arus Kas sukses - 2 periode */
    // public function test_regenerasi_trend_arus_kas_berhasil(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Narasi trend arus kas dummy hasil AI.');

    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\TrendArusKasAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')->once()->with(Mockery::type(UserMessage::class))->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisisLama = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2023,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //         'status'        => 'sudah dihitung',
    //     ]);
    //     $dokumenLama = \App\Models\Dokumen::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2023,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //     ]);
    //     \App\Models\ArusKas::factory()->create([
    //         'dokumen_id' => $dokumenLama->id,
    //         'kas_masuk'  => 400000000,
    //         'kas_keluar' => 250000000,
    //     ]);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2024,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //         'status'        => 'sudah dihitung',
    //     ]);
    //     $dokumen = \App\Models\Dokumen::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2024,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //     ]);
    //     \App\Models\ArusKas::factory()->create([
    //         'dokumen_id' => $dokumen->id,
    //         'kas_masuk'  => 500000000,
    //         'kas_keluar' => 300000000,
    //     ]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'trend_arus_kas',
    //         ]);

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertDatabaseHas('analisis_trend', [
    //         'analisis_id'               => $analisis->id,
    //         'narasi_trend_arus_kas_AI'  => 'Narasi trend arus kas dummy hasil AI.',
    //     ]);
    // }

    // /** IT-021 (Positive): Regenerasi trend tetap diproses backend walau hanya 1 periode data (bypass UI) */
    // public function test_regenerasi_trend_akun_utama_satu_periode_bypass_ui(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Narasi trend 1 periode dummy.');

    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\TrendAkunUtamaAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')->once()->with(Mockery::type(UserMessage::class))->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     // HANYA 1 Analisis (1 periode) untuk perusahaan ini
    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2024,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //         'status'        => 'sudah dihitung',
    //     ]);

    //     $dokumen = \App\Models\Dokumen::factory()->create([
    //         'perusahaan_id' => $perusahaan->id,
    //         'periode_type'  => 'annual',
    //         'tahun'         => 2024,
    //         'quarter'       => null,
    //         'bulan'         => null,
    //     ]);
    //     \App\Models\Neraca::factory()->create(['dokumen_id' => $dokumen->id]);
    //     \App\Models\LabaRugi::factory()->create(['dokumen_id' => $dokumen->id]);
    //     \App\Models\ArusKas::factory()->create(['dokumen_id' => $dokumen->id]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'trend_akun_utama',
    //         ]);

    //     // Tidak ada validasi minimal periode di backend -> tetap sukses
    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertDatabaseHas('analisis_trend', [
    //         'analisis_id'                => $analisis->id,
    //         'narasi_trend_akun_utama_AI' => 'Narasi trend 1 periode dummy.',
    //     ]);
    // }

    // /** IT-022 (Positive): Regenerasi trend dengan gap data di periode tengah */
    // public function test_regenerasi_trend_rasio_dengan_gap_data(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Narasi trend rasio dengan gap.');

    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\TrendRasioAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     // Assert isi prompt benar-benar menyertakan penanda gap
    //     $agentMock->shouldReceive('chat')
    //         ->once()
    //         ->with(Mockery::on(function (UserMessage $msg) {
    //             return str_contains($msg->getContent(), 'namun ada periode dengan data tidak lengkap');
    //         }))
    //         ->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     // Periode 1 (2022) - lengkap
    //     $analisis1 = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
    //         'tahun' => 2022, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisLikuiditas::factory()->create(['analisis_id' => $analisis1->id]);
    //     \App\Models\AnalisisProfitabilitas::factory()->create(['analisis_id' => $analisis1->id]);
    //     \App\Models\AnalisisSolvabilitas::factory()->create(['analisis_id' => $analisis1->id]);
    //     \App\Models\AnalisisAktivitas::factory()->create(['analisis_id' => $analisis1->id]);

    //     // Periode 2 (2023) - GAP, sengaja tidak diisi relasi rasio sama sekali
    //     Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
    //         'tahun' => 2023, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
    //     ]);

    //     // Periode 3 (2024) - lengkap, ini yang jadi target regenerasi
    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
    //         'tahun' => 2024, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisLikuiditas::factory()->create(['analisis_id' => $analisis->id]);
    //     \App\Models\AnalisisProfitabilitas::factory()->create(['analisis_id' => $analisis->id]);
    //     \App\Models\AnalisisSolvabilitas::factory()->create(['analisis_id' => $analisis->id]);
    //     \App\Models\AnalisisAktivitas::factory()->create(['analisis_id' => $analisis->id]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'trend_rasio',
    //         ]);

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertDatabaseHas('analisis_trend', [
    //         'analisis_id'           => $analisis->id,
    //         'narasi_trend_rasio_AI' => 'Narasi trend rasio dengan gap.',
    //     ]);
    // }

    // /** IT-023 (Positive): Regenerasi trend section yang sama 2x berturut - update bukan duplikat */
    // public function test_regenerasi_trend_akun_utama_dua_kali_tidak_duplikat(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->twice()->andReturn('Narasi trend akun utama versi baru.');

    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->twice()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\TrendAkunUtamaAgent::class);
    //     $agentMock->shouldReceive('make')->twice()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')->twice()->with(Mockery::type(UserMessage::class))->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
    //         'tahun' => 2024, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
    //     ]);

    //     $dokumen = \App\Models\Dokumen::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
    //         'tahun' => 2024, 'quarter' => null, 'bulan' => null,
    //     ]);
    //     \App\Models\Neraca::factory()->create(['dokumen_id' => $dokumen->id]);
    //     \App\Models\LabaRugi::factory()->create(['dokumen_id' => $dokumen->id]);
    //     \App\Models\ArusKas::factory()->create(['dokumen_id' => $dokumen->id]);

    //     // Baris analisis_trend sudah ada, narasi_trend_rasio_AI sudah terisi dari proses lain
    //     $analisis->trend()->create([
    //         'narasi_trend_rasio_AI' => 'Narasi trend rasio dari proses sebelumnya.',
    //     ]);

    //     // Kirim 2x berturut-turut
    //     $this->actingAs($user)->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //         'section' => 'trend_akun_utama',
    //     ]);
    //     $this->actingAs($user)->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //         'section' => 'trend_akun_utama',
    //     ]);

    //     // Tetap cuma 1 baris di analisis_trend untuk analisis_id ini
    //     $this->assertEquals(1, \App\Models\AnalisisTrend::where('analisis_id', $analisis->id)->count());

    //     $trend = $analisis->trend()->first();
    //     $this->assertEquals('Narasi trend akun utama versi baru.', $trend->narasi_trend_akun_utama_AI);
    //     // narasi_trend_rasio_AI yang sudah ada sebelumnya TIDAK ikut ter-reset
    //     $this->assertEquals('Narasi trend rasio dari proses sebelumnya.', $trend->narasi_trend_rasio_AI);
    // }

    // /** IT-024 (Positive): Regenerasi trend dengan instruksi tambahan custom */
    // public function test_regenerasi_trend_arus_kas_dengan_user_prompt(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Narasi trend arus kas fokus defisit.');

    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\TrendArusKasAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')
    //         ->once()
    //         ->with(Mockery::on(function (UserMessage $msg) {
    //             $content = $msg->getContent();
    //             return str_contains($content, 'narasi hasil generate sebelumnya')
    //                 && str_contains($content, 'Fokuskan pada periode dengan defisit kas');
    //         }))
    //         ->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisisLama = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
    //         'tahun' => 2023, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
    //     ]);
    //     $dokumenLama = \App\Models\Dokumen::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
    //         'tahun' => 2023, 'quarter' => null, 'bulan' => null,
    //     ]);
    //     \App\Models\ArusKas::factory()->create(['dokumen_id' => $dokumenLama->id]);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
    //         'tahun' => 2024, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
    //     ]);
    //     $dokumen = \App\Models\Dokumen::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
    //         'tahun' => 2024, 'quarter' => null, 'bulan' => null,
    //     ]);
    //     \App\Models\ArusKas::factory()->create(['dokumen_id' => $dokumen->id]);

    //     // narasi_trend_arus_kas_AI sudah ada sebelumnya
    //     $analisis->trend()->create([
    //         'narasi_trend_arus_kas_AI' => 'Narasi arus kas versi lama.',
    //     ]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section'     => 'trend_arus_kas',
    //             'user_prompt' => 'Fokuskan pada periode dengan defisit kas',
    //         ]);

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertDatabaseHas('analisis_trend', [
    //         'analisis_id'              => $analisis->id,
    //         'narasi_trend_arus_kas_AI' => 'Narasi trend arus kas fokus defisit.',
    //     ]);
    // }

    // /** IT-025 (Negative): Regenerasi trend gagal - Agent AI melempar exception */
    // public function test_regenerasi_trend_dupont_gagal_agent_exception(): void
    // {
    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\TrendDupontAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')
    //         ->once()
    //         ->with(Mockery::type(UserMessage::class))
    //         ->andThrow(new \Exception('Provider tidak dapat diakses'));

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisisLama = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
    //         'tahun' => 2023, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisDupont::factory()->create(['analisis_id' => $analisisLama->id]);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
    //         'tahun' => 2024, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisDupont::factory()->create(['analisis_id' => $analisis->id]);

    //     $analisis->trend()->create([
    //         'narasi_trend_dupont_AI' => 'Narasi dupont sebelum error.',
    //     ]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'trend_dupont',
    //         ]);

    //     $response->assertStatus(500);
    //     $this->assertDatabaseHas('analisis_trend', [
    //         'analisis_id'            => $analisis->id,
    //         'narasi_trend_dupont_AI' => 'Narasi dupont sebelum error.', // tidak berubah, rollback
    //     ]);
    // }

    /** IT-034 (Positive): Regenerasi profitabilitas - benchmark sektor Jasa */
    // public function test_regenerasi_profitabilitas_benchmark_sektor_jasa(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Narasi profitabilitas jasa.');
    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\ProfitabilityAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')
    //         ->once()
    //         ->with(Mockery::on(fn (UserMessage $m) => str_contains($m->getContent(), 'Jasa (umumnya > 10%)')))
    //         ->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create(['sektor' => 'Jasa Konsultasi']);
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'status' => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisProfitabilitas::factory()->create(['analisis_id' => $analisis->id]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'profitabilitas',
    //         ]);

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertDatabaseHas('analisis_profitabilitas', [
    //         'analisis_id'               => $analisis->id,
    //         'narasi_profitabilitas_AI'  => 'Narasi profitabilitas jasa.',
    //     ]);
    // }

    // /** IT-035 (Positive): Regenerasi profitabilitas - benchmark sektor Dagang/Ritel */
    // public function test_regenerasi_profitabilitas_benchmark_sektor_ritel(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Narasi profitabilitas ritel.');
    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\ProfitabilityAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')
    //         ->once()
    //         ->with(Mockery::on(fn (UserMessage $m) => str_contains($m->getContent(), 'Dagang/Ritel (umumnya 2-5%)')))
    //         ->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create(['sektor' => 'Ritel Elektronik']);
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'status' => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisProfitabilitas::factory()->create(['analisis_id' => $analisis->id]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'profitabilitas',
    //         ]);

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertDatabaseHas('analisis_profitabilitas', [
    //         'analisis_id'              => $analisis->id,
    //         'narasi_profitabilitas_AI' => 'Narasi profitabilitas ritel.',
    //     ]);
    // }

    // /** IT-036 (Positive): Regenerasi profitabilitas - benchmark sektor Manufaktur */
    // public function test_regenerasi_profitabilitas_benchmark_sektor_manufaktur(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Narasi profitabilitas manufaktur.');
    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\ProfitabilityAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')
    //         ->once()
    //         ->with(Mockery::on(fn (UserMessage $m) => str_contains($m->getContent(), 'Manufaktur (umumnya 5-10%)')))
    //         ->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create(['sektor' => 'Manufaktur']);
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'status' => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisProfitabilitas::factory()->create(['analisis_id' => $analisis->id]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'profitabilitas',
    //         ]);

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertDatabaseHas('analisis_profitabilitas', [
    //         'analisis_id'              => $analisis->id,
    //         'narasi_profitabilitas_AI' => 'Narasi profitabilitas manufaktur.',
    //     ]);
    // }

    // /** IT-037 (Edge): Regenerasi profitabilitas - sektor tidak dikenal/kosong -> fallback benchmark */
    // public function test_regenerasi_profitabilitas_sektor_kosong_fallback(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Narasi profitabilitas fallback.');
    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\ProfitabilityAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')
    //         ->once()
    //         ->with(Mockery::on(fn (UserMessage $m) => str_contains($m->getContent(), 'Umum/tidak teridentifikasi')))
    //         ->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create(['sektor' => null]);
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'status' => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisProfitabilitas::factory()->create(['analisis_id' => $analisis->id]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'profitabilitas',
    //         ]);

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertDatabaseHas('analisis_profitabilitas', [
    //         'analisis_id'              => $analisis->id,
    //         'narasi_profitabilitas_AI' => 'Narasi profitabilitas fallback.',
    //     ]);
    // }

    // /** IT-038 (Positive): Regenerasi solvabilitas berhasil */
    // public function test_regenerasi_solvabilitas_berhasil(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Narasi solvabilitas dummy.');
    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\SolvencyAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')->once()->with(Mockery::type(UserMessage::class))->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'status' => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisSolvabilitas::factory()->create([
    //         'analisis_id'    => $analisis->id,
    //         'debt_to_equity' => 1.25,
    //         'debt_to_asset'  => 0.55,
    //     ]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'solvabilitas',
    //         ]);

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertDatabaseHas('analisis_solvabilitas', [
    //         'analisis_id'            => $analisis->id,
    //         'narasi_solvabilitas_AI' => 'Narasi solvabilitas dummy.',
    //     ]);
    // }

    // /** IT-039 (Positive): Regenerasi aktivitas berhasil */
    // public function test_regenerasi_aktivitas_berhasil(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Narasi aktivitas dummy.');
    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\ActivityAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')->once()->with(Mockery::type(UserMessage::class))->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'status' => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisAktivitas::factory()->create([
    //         'analisis_id'          => $analisis->id,
    //         'total_asset_turnover' => 0.8,
    //     ]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'aktivitas',
    //         ]);

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertDatabaseHas('analisis_aktivitas', [
    //         'analisis_id'         => $analisis->id,
    //         'narasi_aktivitas_AI' => 'Narasi aktivitas dummy.',
    //     ]);
    // }

    // /** IT-040 (Positive): Regenerasi DuPont (1 periode) berhasil */
    // public function test_regenerasi_dupont_satu_periode_berhasil(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Narasi dupont dummy.');
    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\DupontAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')->once()->with(Mockery::type(UserMessage::class))->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'status' => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisDupont::factory()->create([
    //         'analisis_id'          => $analisis->id,
    //         'net_profit_margin'    => 10.0,
    //         'total_asset_turnover' => 0.8,
    //         'leverage_multiplier'  => 2.5,
    //         'roe'                  => 20.0,
    //     ]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'dupont',
    //         ]);

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertDatabaseHas('analisis_dupont', [
    //         'analisis_id'      => $analisis->id,
    //         'narasi_dupont_AI' => 'Narasi dupont dummy.',
    //     ]);
    // }

    // /** IT-041 (Positive): Regenerasi Common-size (1 periode) berhasil */
    // public function test_regenerasi_commonsize_satu_periode_berhasil(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Narasi commonsize dummy.');
    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\CommonsizeAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')->once()->with(Mockery::type(UserMessage::class))->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'status' => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisCommonsize::factory()->create(['analisis_id' => $analisis->id]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section' => 'commonsize',
    //         ]);

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertDatabaseHas('analisis_commonsize', [
    //         'analisis_id'          => $analisis->id,
    //         'narasi_commonsize_AI' => 'Narasi commonsize dummy.',
    //     ]);
    // }

    // /** IT-042 (Positive): Regenerasi ulang section non-trend yang sudah punya narasi lama + user_prompt */
    // public function test_regenerasi_likuiditas_ulang_dengan_narasi_lama_dan_prompt(): void
    // {
    //     $messageMock = Mockery::mock();
    //     $messageMock->shouldReceive('getContent')->once()->andReturn('Narasi likuiditas 1 paragraf singkat.');
    //     $responseMock = Mockery::mock();
    //     $responseMock->shouldReceive('getMessage')->once()->andReturn($messageMock);

    //     $agentMock = Mockery::mock('alias:' . \App\Neuron\RAG\LiquidityAnalystAgent::class);
    //     $agentMock->shouldReceive('make')->once()->andReturn($agentMock);
    //     $agentMock->shouldReceive('chat')
    //         ->once()
    //         ->with(Mockery::on(function (UserMessage $msg) {
    //             $c = $msg->getContent();
    //             return str_contains($c, 'narasi hasil generate sebelumnya')
    //                 && str_contains($c, 'Narasi likuiditas versi panjang lama.')
    //                 && str_contains($c, 'Perpendek jadi 1 paragraf saja');
    //         }))
    //         ->andReturn($responseMock);

    //     $perusahaan = Perusahaan::factory()->create();
    //     $user = User::factory()->create(['perusahaan_id' => $perusahaan->id, 'role' => 'user']);

    //     $analisis = Analisis::factory()->create([
    //         'perusahaan_id' => $perusahaan->id, 'status' => 'sudah dihitung',
    //     ]);
    //     \App\Models\AnalisisLikuiditas::factory()->create([
    //         'analisis_id'          => $analisis->id,
    //         'narasi_likuiditas_AI' => 'Narasi likuiditas versi panjang lama.',
    //     ]);

    //     $response = $this->actingAs($user)
    //         ->post(route('perusahaan.analisis.regenerasi', [$perusahaan, $analisis]), [
    //             'section'     => 'likuiditas',
    //             'user_prompt' => 'Perpendek jadi 1 paragraf saja',
    //         ]);

    //     $response->assertRedirect();
    //     $response->assertSessionDoesntHaveErrors();
    //     $this->assertDatabaseHas('analisis_likuiditas', [
    //         'analisis_id'          => $analisis->id,
    //         'narasi_likuiditas_AI' => 'Narasi likuiditas 1 paragraf singkat.',
    //     ]);
    // }

    /** IT-047 (Negative): Detail - akses Analisis milik Perusahaan lain ditolak (404) */
    public function test_detail_akses_analisis_milik_perusahaan_lain_ditolak(): void
    {
        $perusahaanA = Perusahaan::factory()->create();
        $perusahaanB = Perusahaan::factory()->create();

        $user = User::factory()->create(['perusahaan_id' => $perusahaanA->id, 'role' => 'user']);

        $analisisMilikB = Analisis::factory()->create([
            'perusahaan_id' => $perusahaanB->id,
            'status'        => 'sudah dihitung',
        ]);

        $response = $this->actingAs($user)
            ->get("/perusahaan/{$perusahaanA->id}/analisis/{$analisisMilikB->id}");

        $response->assertStatus(404);
    }
}