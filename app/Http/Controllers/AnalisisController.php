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
                    'periode_type' => $referensi->periode_type,
                    'tahun' => $referensi->tahun,
                    'quarter' => $referensi->quarter,
                    'bulan' => $referensi->bulan,
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
                'id' => $analisis->id,
                'periode_label' => $analisis->periode,
                'periode_type' => $analisis->periode_type,
                'tahun' => $analisis->tahun,
                'jumlah_dokumen' => $group->count(),
                'status' => $analisis->status,
            ];
        })
        ->sortBy([
            ['tahun', 'desc'],
            ['id', 'desc'],
        ])
        ->values();

        return Inertia::render('Perusahaan/Analisis/Index', [
            'perusahaan' => $perusahaan,
            'analisisList' => $analisisList,
        ]);
    }

    public function analisis(Perusahaan $perusahaan, Analisis $analisis)
    {
        abort_if($analisis->perusahaan_id !== $perusahaan->id, 404);

        $analisis->load(['likuiditas', 'profitabilitas', 'solvabilitas', 'aktivitas']);

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

        return Inertia::render('Perusahaan/Analisis/Detail', [
            'perusahaan' => $perusahaan,
            'analisis' => [
                'id' => $analisis->id,
                'periode_label' => $analisis->periode,
                'status' => $analisis->status,
                'ai_summary_insight' => $analisis->AI_summary_insight,
            ],
            'dokumenPeriode' => $dokumenPeriode,
            'likuiditas' => $analisis->likuiditas,
            'profitabilitas' => $analisis->profitabilitas,
            'solvabilitas' => $analisis->solvabilitas,
            'aktivitas' => $analisis->aktivitas,
            'neraca' => $neraca,
            'labaRugi' => $labaRugi,
        ]);
    }

    public function regenerasi(Request $request, Perusahaan $perusahaan, Analisis $analisis, AnalysisFinancialService $analysisFinancialService)
    {
        $request->validate([
            'section' => 'required|string|in:likuiditas,profitabilitas,solvabilitas,aktivitas,summary'
        ]);

        $section = $request->input('section');

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

        $analysisFinancialService->validasiKelengkapanData($section, $neraca, $labaRugi);


        DB::transaction(function () use ($section, $analisis, $neraca, $labaRugi, $analysisFinancialService) {

            switch ($section) {
                case 'likuiditas':
                    $analysisFinancialService->prosesLikuiditas($analisis, $neraca);
                    break;

                case 'profitabilitas':
                    $analysisFinancialService->prosesProfitabilitas($analisis, $neraca, $labaRugi);
                    break;

                case 'solvabilitas':
                    $analysisFinancialService->prosesSolvabilitas($analisis, $neraca);
                    break;

                case 'aktivitas':
                    $analysisFinancialService->prosesAktivitas($analisis, $neraca, $labaRugi);
                    break;

                case 'summary':
                    // TODO: Implementasi trigger prompt AI Agent (RAG) di sini Untuk Summary
                    // $analysisFinancialService->generateAISummary($analisis);
                    break;
            }

            // 3. Update Status
            $analysisFinancialService->updateStatusJikaLengkap($analisis);
        });

        return back();
    }
}
