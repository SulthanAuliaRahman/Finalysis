<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use App\Models\Analisis;
use Illuminate\Http\Request;
use App\Services\AnalysisFinancialService;
use App\Services\CalculateFinancialService;
use Inertia\Inertia;

class AnalisisController extends Controller
{
    public function index(Perusahaan $perusahaan)
    {
        $analisisList = Analisis::query()
            ->whereHas('dokumen', fn ($q) => $q->where('perusahaan_id', $perusahaan->id))
            ->with('dokumen')
            ->latest()
            ->get()
            ->map(fn (Analisis $analisis) => [
                'id'            => $analisis->id,
                'tahun'         => $analisis->dokumen->tahun,
                'periode_label' => $this->buildPeriodeLabel($analisis->dokumen),
                'nama_file'     => $analisis->dokumen->nama_file,
                'sudah_diringkas' => filled($analisis->ringkasan_laporan),
            ]);

        return Inertia::render('Perusahaan/Analisis/Index', [
            'perusahaan'   => $perusahaan,
            'analisisList' => $analisisList,
        ]);
    }

    private function buildPeriodeLabel($dokumen): string
    {
        return match ($dokumen->periode_type) {
            'annual'    => "Tahunan {$dokumen->tahun}",
            'quarterly' => "Q{$dokumen->quarter} {$dokumen->tahun}",
            'monthly'   => now()->setDate($dokumen->tahun, $dokumen->bulan, 1)->translatedFormat('F Y'),
            default     => (string) $dokumen->tahun,
        };
    }

    // ke page analisis detail
    public function detail(Perusahaan $perusahaan, Analisis $analisis)
    {
        $analisis->load([
            'dokumen.neraca',
            'dokumen.labaRugi',
            'likuiditas',
            'profitabilitas',
            'solvabilitas',
            'aktivitas',
            'dupont',
            'commonsize',
            'trend',
        ]);

        $dokumen = $analisis->dokumen;

        if ($dokumen->perusahaan_id !== $perusahaan->id) {
            abort(404);
        }

        // dd($analisis->getDupontTrend());

        return Inertia::render('Perusahaan/Analisis/Detail', [
            'perusahaan'      => $perusahaan,
            'analisis'        => [
                'id'                => $analisis->id,
                'periode_label'     => $this->buildPeriodeLabel($dokumen),
                'ai_summary_insight' => $analisis->ringkasan_laporan,
            ],
            'dokumenPeriode'  => [
                'nama_file'    => $dokumen->nama_file,
                'periode_type' => $dokumen->periode_type,
                'tahun'        => $dokumen->tahun,
                'quarter'      => $dokumen->quarter,
                'bulan'        => $dokumen->bulan,
            ],
            'likuiditas'      => $analisis->likuiditas,
            'profitabilitas'  => $analisis->profitabilitas,
            'solvabilitas'    => $analisis->solvabilitas,
            'aktivitas'       => $analisis->aktivitas,
            'dupont'          => $analisis->dupont,
            'commonsize'      => $analisis->commonsize,
            'trendRasio'      => $analisis->getRasioTrend(),
            'trendDupont'     => $analisis->getDupontTrend(),
            'trendCommonsize' => $analisis->getCommonsizeTrend(),
            'trendAkunUtama'  => $analisis->getAkunUtamaTrend(),
            'narasi_trend'     =>$analisis->trend,
            'neraca'          => $dokumen->neraca,
            'labaRugi'        => $dokumen->labaRugi,
        ]);
    }

    public function generateSeluruhAnalisis(Perusahaan $perusahaan,Analisis $analisis) {
        // Cek apakah analisis sudah pernah di-generate
        if ($analisis->ringkasan_laporan !== null) {
            return back()->withErrors([
                'message' => 'Analisis sudah di-generate.'
            ]);
        }

        $sections = [
            'likuiditas','profitabilitas',
            'solvabilitas','aktivitas',
            'dupont','commonsize',
            'trend_akun_utama','trend_rasio',
            'trend_dupont','trend_commonsize',
            'summary',
        ];

        foreach ($sections as $section) {
            $sectionRequest = new Request([
                'section' => $section,
                'user_prompt' => null, // untuk awal gak butuh
            ]);

            $this->generateAnalisis(
                $sectionRequest,
                $perusahaan,
                $analisis,
                new AnalysisFinancialService(new CalculateFinancialService())
            );
        }

        return back()->with([
            'success' => 'Seluruh analisis berhasil di-generate.'
        ]);
    }

    // untuk generate analisis per section (di pakai untuk regenearasi juga)
    public function generateAnalisis(Request $request, Perusahaan $perusahaan, Analisis $analisis, AnalysisFinancialService $analysisFinancialService)
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
                // case 'trend_arus_kas':
                //     $analysisFinancialService->prosesTrendArusKas($analisis, $userPrompt);
                //     break;
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

        return back();

    }
}
