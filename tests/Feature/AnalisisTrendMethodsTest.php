<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Perusahaan;
use App\Models\Analisis;
use App\Models\Dokumen;
use App\Models\Neraca;
use App\Models\LabaRugi;
use App\Models\ArusKas;
use App\Models\AnalisisLikuiditas;
use App\Models\AnalisisProfitabilitas;
use App\Models\AnalisisSolvabilitas;
use App\Models\AnalisisAktivitas;
use App\Models\AnalisisDupont;
use App\Models\AnalisisCommonsize;

class AnalisisTrendMethodsTest extends TestCase
{
    use RefreshDatabase;

    /** IT-026 (Positive): Limit 5 periode terbaru untuk periode_type annual */
    public function test_limit_5_periode_terbaru_annual(): void
    {
        $perusahaan = Perusahaan::factory()->create();

        $analisisList = [];
        foreach (range(2018, 2024) as $tahun) {
            $a = Analisis::factory()->create([
                'perusahaan_id' => $perusahaan->id,
                'periode_type'  => 'annual',
                'tahun'         => $tahun,
                'quarter'       => null,
                'bulan'         => null,
                'status'        => 'sudah dihitung',
            ]);
            AnalisisLikuiditas::factory()->create(['analisis_id' => $a->id]);
            $analisisList[$tahun] = $a;
        }

        $trend = $analisisList[2024]->getRasioTrend();

        $this->assertCount(5, $trend['periode_data']);

        $tahunTerambil = collect($trend['periode_data'])->pluck('analisis.tahun')->sort()->values()->all();
        $this->assertEquals([2020, 2021, 2022, 2023, 2024], $tahunTerambil);
    }

    /** IT-027 (Negative/Edge): has_gap = true jika ada periode tanpa data rasio */
    public function test_has_gap_true_jika_ada_periode_tanpa_data_rasio(): void
    {
        $perusahaan = Perusahaan::factory()->create();

        // Periode lama, SENGAJA tanpa relasi rasio apapun
        Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2023, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
        ]);

        $analisis = Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2024, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
        ]);
        AnalisisLikuiditas::factory()->create(['analisis_id' => $analisis->id]);
        AnalisisProfitabilitas::factory()->create(['analisis_id' => $analisis->id]);
        AnalisisSolvabilitas::factory()->create(['analisis_id' => $analisis->id]);
        AnalisisAktivitas::factory()->create(['analisis_id' => $analisis->id]);

        $trend = $analisis->getRasioTrend();
        $this->assertTrue($trend['has_gap']);
    }

    /** IT-028 (Edge): Growth null pada periode pertama (tidak ada data pembanding) */
    public function test_growth_null_pada_periode_pertama(): void
    {
        $perusahaan = Perusahaan::factory()->create();

        $analisis = Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2024, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
        ]);
        $dokumen = Dokumen::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2024, 'quarter' => null, 'bulan' => null,
        ]);
        Neraca::factory()->create(['dokumen_id' => $dokumen->id]);
        LabaRugi::factory()->create(['dokumen_id' => $dokumen->id, 'pendapatan' => 800000000]);
        ArusKas::factory()->create(['dokumen_id' => $dokumen->id]);

        $trend = $analisis->getAkunUtamaTrend();

        $this->assertCount(1, $trend['periode_data']);
        $this->assertNull($trend['periode_data'][0]['growth_pendapatan']);
    }

    /** IT-029 (Positive): Growth dihitung benar antar 2 periode */
    public function test_growth_dihitung_benar_antar_2_periode(): void
    {
        $perusahaan = Perusahaan::factory()->create();

        $dokumenLama = Dokumen::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2023, 'quarter' => null, 'bulan' => null,
        ]);
        Neraca::factory()->create(['dokumen_id' => $dokumenLama->id]);
        LabaRugi::factory()->create(['dokumen_id' => $dokumenLama->id, 'pendapatan' => 500000000]);
        ArusKas::factory()->create(['dokumen_id' => $dokumenLama->id]);
        Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2023, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
        ]);

        $analisis = Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2024, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
        ]);
        $dokumen = Dokumen::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2024, 'quarter' => null, 'bulan' => null,
        ]);
        Neraca::factory()->create(['dokumen_id' => $dokumen->id]);
        LabaRugi::factory()->create(['dokumen_id' => $dokumen->id, 'pendapatan' => 750000000]);
        ArusKas::factory()->create(['dokumen_id' => $dokumen->id]);

        $trend = $analisis->getAkunUtamaTrend();

        $this->assertCount(2, $trend['periode_data']);
        // growth = (750jt - 500jt) / 500jt * 100 = 50%
        $this->assertEqualsWithDelta(50.0, $trend['periode_data'][1]['growth_pendapatan'], 0.01);
    }

    /** IT-030 (Edge): Growth null jika nilai periode sebelumnya = 0 */
    public function test_growth_null_jika_periode_sebelumnya_nol(): void
    {
        $perusahaan = Perusahaan::factory()->create();

        $dokumenLama = Dokumen::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2023, 'quarter' => null, 'bulan' => null,
        ]);
        Neraca::factory()->create(['dokumen_id' => $dokumenLama->id]);
        LabaRugi::factory()->create(['dokumen_id' => $dokumenLama->id, 'pendapatan' => 0]);
        ArusKas::factory()->create(['dokumen_id' => $dokumenLama->id]);
        Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2023, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
        ]);

        $analisis = Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2024, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
        ]);
        $dokumen = Dokumen::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2024, 'quarter' => null, 'bulan' => null,
        ]);
        Neraca::factory()->create(['dokumen_id' => $dokumen->id]);
        LabaRugi::factory()->create(['dokumen_id' => $dokumen->id, 'pendapatan' => 750000000]);
        ArusKas::factory()->create(['dokumen_id' => $dokumen->id]);

        $trend = $analisis->getAkunUtamaTrend();

        $this->assertNull($trend['periode_data'][1]['growth_pendapatan']);
    }

    /** IT-031 (Edge): has_gap = true jika ada periode tanpa data DuPont */
    public function test_has_gap_true_jika_tanpa_data_dupont(): void
    {
        $perusahaan = Perusahaan::factory()->create();

        Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2023, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
        ]); // tanpa dupont

        $analisis = Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2024, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
        ]);
        AnalisisDupont::factory()->create(['analisis_id' => $analisis->id]);

        $trend = $analisis->getDupontTrend();
        $this->assertTrue($trend['has_gap']);
    }

    /** IT-032 (Edge): has_gap = true jika ada periode tanpa data Common-size */
    public function test_has_gap_true_jika_tanpa_data_commonsize(): void
    {
        $perusahaan = Perusahaan::factory()->create();

        Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2023, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
        ]); // tanpa commonsize

        $analisis = Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2024, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
        ]);
        AnalisisCommonsize::factory()->create(['analisis_id' => $analisis->id]);

        $trend = $analisis->getCommonsizeTrend();
        $this->assertTrue($trend['has_gap']);
    }

    /** IT-033 (Edge): has_gap = true jika ada periode tanpa Dokumen ArusKas */
    public function test_has_gap_true_jika_tanpa_dokumen_aruskas(): void
    {
        $perusahaan = Perusahaan::factory()->create();

        Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2023, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
        ]); // tanpa Dokumen sama sekali

        $analisis = Analisis::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2024, 'quarter' => null, 'bulan' => null, 'status' => 'sudah dihitung',
        ]);
        $dokumen = Dokumen::factory()->create([
            'perusahaan_id' => $perusahaan->id, 'periode_type' => 'annual',
            'tahun' => 2024, 'quarter' => null, 'bulan' => null,
        ]);
        ArusKas::factory()->create(['dokumen_id' => $dokumen->id]);

        $trend = $analisis->getArusKasTrend();
        $this->assertTrue($trend['has_gap']);
    }
}