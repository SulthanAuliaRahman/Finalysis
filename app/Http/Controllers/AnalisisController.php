<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use App\Models\Analisis;
use App\Models\Neraca;
use App\Models\LabaRugi;
use App\Models\Dokumen;
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
                    'status' => 'belum dihitung',
                ]
            );

            $dokumenTerbaru = $group->max('updated_at');

            if ($analisis->status === 'sudah dihitung' && $dokumenTerbaru->gt($analisis->updated_at)) {
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
            'perusahaan'   => $perusahaan,
            'analisisList' => $analisisList,
        ]);
    }

    // ke page analisis detail
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

        $referensiDokumen = $perusahaan->dokumen()
            ->where('status', 'selesai')
            ->withCount('chunks')
            ->latest()
            ->get()
            ->map(fn ($dokumen) => [
                'id'            => $dokumen->id,
                'nama_file'     => $dokumen->nama_file,
                'periode_label' => $dokumen->periode,
                'chunks_count'  => $dokumen->chunks_count,
                'pdf_url'       => route('perusahaan.dokumen.view-pdf', [$perusahaan, $dokumen]),
                'chunks_url'    => route('perusahaan.analisis.referensi-chunks', [$perusahaan, $analisis, $dokumen]),
            ]);

        return Inertia::render('Perusahaan/Analisis/Detail', [
            'perusahaan'      => $perusahaan,
            'analisis'        => [
                'id'                 => $analisis->id,
                'periode_label'      => $analisis->periode,
                'status'             => $analisis->status,
                'ai_summary_insight' => $analisis->AI_summary_insight,
            ],
            'dokumenPeriode'  => $dokumenPeriode,
            'referensiDokumen' => $referensiDokumen,
            'likuiditas'      => $analisis->likuiditas,
            'profitabilitas'  => $analisis->profitabilitas,
            'solvabilitas'    => $analisis->solvabilitas,
            'aktivitas'       => $analisis->aktivitas,
            'dupont'          => $analisis->dupont,
            'commonsize'      => $analisis->commonsize,
            'trendAkunUtama'  => $analisis->getAkunUtamaTrend(),
            'trendRasio'      => $analisis->getRasioTrend(),
            'trendDupont'     => $analisis->getDupontTrend(),
            'trendCommonsize' => $analisis->getCommonsizeTrend(),
            'trendArusKas'    => $analisis->getArusKasTrend(),
            'neraca'          => $neraca,
            'labaRugi'        => $labaRugi,
        ]);
    }

    public function referensiChunks(Request $request, Perusahaan $perusahaan, Analisis $analisis, Dokumen $dokumen)
    {
        abort_if($analisis->perusahaan_id !== $perusahaan->id || $dokumen->perusahaan_id !== $perusahaan->id, 404);

        $section = $request->validate([
            'section' => 'required|string|in:likuiditas,profitabilitas,solvabilitas,aktivitas,dupont,commonsize,trend_akun_utama,trend_rasio,trend_dupont,trend_commonsize,trend_arus_kas,summary',
        ])['section'];

        return response()->json([
            'chunks' => DB::table('analisis_referensi')
                ->leftJoin('dokumen', 'dokumen.id', '=', 'analisis_referensi.dokumen_id')
                ->where('analisis_id', $analisis->id)
                ->where('section', $section)
                ->where('dokumen_id', $dokumen->id)
                ->orderBy('urutan')
                ->get([
                    'analisis_referensi.id',
                    'analisis_referensi.dokumen_id',
                    'analisis_referensi.chunk_index',
                    'analisis_referensi.text',
                    'analisis_referensi.score',
                    'dokumen.nama_file as source_file',
                    'dokumen.periode_type as source_periode_type',
                    'dokumen.tahun as source_tahun',
                    'dokumen.quarter as source_quarter',
                    'dokumen.bulan as source_bulan',
                ]),
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
            'section'     => 'required|string|in:likuiditas,profitabilitas,solvabilitas,aktivitas,dupont,commonsize,trend_akun_utama,trend_rasio,trend_dupont,trend_commonsize,trend_arus_kas,summary',
            'user_prompt' => 'nullable|string|max:1000',
        ]);

        $section    = $request->input('section');
        $userPrompt = $request->input('user_prompt');

        if (!in_array($analisis->status, ['sudah dihitung'])) {
            return back()->withErrors(['message' => 'Silahkan Hitung Data Finansial terlebih dahulu.']);
        }

        DB::transaction(function () use ($section, $analisis, $analysisFinancialService, $userPrompt) {
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
                case 'trend_akun_utama':
                    $analysisFinancialService->prosesTrendAkunUtama($analisis, $userPrompt);
                    break;
                case 'trend_rasio':
                    $analysisFinancialService->prosesTrendRasio($analisis, $userPrompt);
                    break;
                case 'trend_dupont':
                    $analysisFinancialService->prosesTrendDupont($analisis, $userPrompt);
                    break;
                case 'trend_commonsize':
                    $analysisFinancialService->prosesTrendCommonsize($analisis, $userPrompt);
                    break;
                case 'trend_arus_kas':
                    $analysisFinancialService->prosesTrendArusKas($analisis, $userPrompt);
                    break;
                case 'summary':
                    // minimal sudah ada AI Narasi untuk 4 rasio utama
                    $analisis->load([
                        'likuiditas',
                        'profitabilitas',
                        'solvabilitas',
                        'aktivitas',
                    ]);

                    $hasNarasi =
                        filled($analisis->likuiditas?->narasi_likuiditas_AI) &&
                        filled($analisis->profitabilitas?->narasi_profitabilitas_AI) &&
                        filled($analisis->solvabilitas?->narasi_solvabilitas_AI) &&
                        filled($analisis->aktivitas?->narasi_aktivitas_AI);

                    if (!$hasNarasi) {
                        return back()->withErrors([
                            'message' => 'Minimal Komponen Rasio Di lakukan analisis AI Sebelum Mendapatkan summary !'
                        ]);
                    }

                    $analysisFinancialService->prosesSummaryAnalisis($analisis, $userPrompt);
                    break;
            }

        });

        return back();
    }
}
