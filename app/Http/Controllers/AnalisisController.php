<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use App\Models\Analisis;
use App\Models\Neraca;
use App\Models\LabaRugi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AnalysisFinancialService;
use Inertia\Inertia;

class AnalisisController extends Controller
{
    public function index(Perusahaan $perusahaan)
    {
        $dokumenList = $perusahaan->dokumen()
            ->select('id', 'periode_type', 'tahun', 'quarter', 'bulan', 'updated_at')
            ->get();

        $periodeGroups = $dokumenList->groupBy(function ($dokumen) {
            return $dokumen->periode_type . '|' . $dokumen->tahun . '|' . $dokumen->quarter . '|' . $dokumen->bulan;
        });

        $analisisList = $periodeGroups->map(function ($group) use ($perusahaan) {
            $referensi = $group->first();

            $analisis = Analisis::firstOrCreate(
                [
                    'perusahaan_id' => $perusahaan->id,
                    'periode_type'  => $referensi->periode_type,
                    'tahun'         => $referensi->tahun,
                    'quarter'       => $referensi->quarter,
                    'bulan'         => $referensi->bulan,
                ],
                [
                    'status' => 'belum dianalisis',
                ]
            );

            $dokumenTerbaru = $group->max('updated_at');

            if ($analisis->status === 'sudah dianalisis' && $dokumenTerbaru->gt($analisis->updated_at)) {
                $analisis->status = 'Terjadi Perubahan Data!';
                $analisis->save();
            }

            return [
                'id'             => $analisis->id,
                'periode_label'  => $analisis->periode,
                'periode_type'   => $analisis->periode_type,
                'tahun'          => $analisis->tahun,
                'jumlah_dokumen' => $group->count(),
                'status'         => $analisis->status,
            ];
        })
        ->sortBy([
            ['tahun', 'desc'],
            ['id', 'desc'],
        ])
        ->values();

        return Inertia::render('Perusahaan/Analisis/Index', [
            'perusahaan'  => $perusahaan,
            'analisisList' => $analisisList,
        ]);
    }

    public function analisis(Perusahaan $perusahaan, Analisis $analisis)
    {
        abort_if($analisis->perusahaan_id !== $perusahaan->id, 404);

        $analisis->load([
            'likuiditas',
            'profitabilitas',
            'solvabilitas',
            'aktivitas',
            'dupont',
            'commonsize',
            'trend.periodeData.analisis.likuiditas',
            'trend.periodeData.analisis.profitabilitas',
            'trend.periodeData.analisis.solvabilitas',
            'trend.periodeData.analisis.aktivitas',
            'trend.periodeData.analisis.dupont',
            'trend.periodeData.analisis.commonsize'
        ]);

        $dokumenPeriode = $perusahaan->dokumen()
            ->where('periode_type', $analisis->periode_type)
            ->where('tahun', $analisis->tahun)
            ->where('quarter', $analisis->quarter)
            ->where('bulan', $analisis->bulan)
            ->select('id', 'nama_file', 'periode_type', 'tahun', 'quarter', 'bulan', 'status', 'created_at')
            ->latest()
            ->get();

        $neraca = Neraca::whereHas('dokumen', function ($query) use ($perusahaan, $analisis) {
            $query->where('perusahaan_id', $perusahaan->id)
                ->where('periode_type', $analisis->periode_type)
                ->where('tahun', $analisis->tahun)
                ->where('quarter', $analisis->quarter)
                ->where('bulan', $analisis->bulan);
        })->latest()->first();

        $labaRugi = LabaRugi::whereHas('dokumen', function ($query) use ($perusahaan, $analisis) {
            $query->where('perusahaan_id', $perusahaan->id)
                ->where('periode_type', $analisis->periode_type)
                ->where('tahun', $analisis->tahun)
                ->where('quarter', $analisis->quarter)
                ->where('bulan', $analisis->bulan);
        })->latest()->first();


        // dd($analisis->solvabilitas, $analisis->aktivitas,);

        return Inertia::render('Perusahaan/Analisis/Detail', [
            'perusahaan'    => $perusahaan,
            'analisis'      => [
                'id'                 => $analisis->id,
                'periode_label'      => $analisis->periode,
                'status'             => $analisis->status,
                'ai_summary_insight' => $analisis->AI_summary_insight,
            ],
            'dokumenPeriode' => $dokumenPeriode,
            'likuiditas'     => $analisis->likuiditas,
            'profitabilitas' => $analisis->profitabilitas,
            'solvabilitas'   => $analisis->solvabilitas,
            'aktivitas'      => $analisis->aktivitas,
            'dupont'         => $analisis->dupont,
            'commonsize'     => $analisis->commonsize,
            'trend'          => $analisis->trend,
            'neraca'         => $neraca,
            'labaRugi'       => $labaRugi,
        ]);
    }

    public function hitungRasio(Request $request, Perusahaan $perusahaan, Analisis $analisis, AnalysisFinancialService $analysisFinancialService)
    {
        $neraca = Neraca::whereHas('dokumen', function ($query) use ($perusahaan, $analisis) {
            $query->where('perusahaan_id', $perusahaan->id)
                ->where('periode_type', $analisis->periode_type)
                ->where('tahun', $analisis->tahun)
                ->where('quarter', $analisis->quarter)
                ->where('bulan', $analisis->bulan);
        })->latest()->first();

        $labaRugi = LabaRugi::whereHas('dokumen', function ($query) use ($perusahaan, $analisis) {
            $query->where('perusahaan_id', $perusahaan->id)
                ->where('periode_type', $analisis->periode_type)
                ->where('tahun', $analisis->tahun)
                ->where('quarter', $analisis->quarter)
                ->where('bulan', $analisis->bulan);
        })->latest()->first();

        $analysisFinancialService->validasiKelengkapanData($neraca, $labaRugi);

        DB::transaction(function () use ($analisis, $neraca, $labaRugi, $analysisFinancialService) {
            $analysisFinancialService->hitungSemuaRasio($analisis, $neraca, $labaRugi);
        });

        return back();
    }

    public function regenerasi(Request $request, Perusahaan $perusahaan, Analisis $analisis, AnalysisFinancialService $analysisFinancialService)
    {
        $request->validate([
            'section'     => 'required|string|in:likuiditas,profitabilitas,solvabilitas,aktivitas,dupont,commonsize,trend,summary',
            'user_prompt' => 'nullable|string|max:1000',
        ]);

        $section    = $request->input('section');
        $userPrompt = $request->input('user_prompt');

        if (!in_array($analisis->status, ['rasio tersedia', 'sudah dianalisis'])) {
            return back()->withErrors(['message' => 'Silahkan Hitung Data Finansial terlebih dahulu.']);
        }



        DB::transaction(function () use ($section, $analisis, $neraca, $labaRugi, $analysisFinancialService, $userPrompt) {
            switch ($section) {
                case 'likuiditas':
                    $analysisFinancialService->prosesLikuiditas($analisis, $userPrompt);
                    break;
                case 'profitabilitas':
                    $analysisFinancialService->prosesProfitabilitas($analisis, $userPrompt);
                    break;
                case 'solvabilitas':
                    $analysisFinancialService->prosesSolvabilitas($analisis, $userPrompt);
                    break;
                case 'aktivitas':
                    $analysisFinancialService->prosesAktivitas($analisis, $userPrompt);
                    break;
                case 'dupont':
                    $analysisFinancialService->prosesDupont($analisis, $userPrompt);
                    break;
                case 'commonsize':
                    $analysisFinancialService->prosesCommonsize($analisis, $userPrompt);
                    break;
                case 'trend':
                    $analysisFinancialService->prosesTrend($analisis);
                    break;
                case 'summary':
                    // TODO: generateAISummary
                    break;
            }

            $analysisFinancialService->updateStatusJikaLengkap($analisis);
        });

        return back();
    }
}
