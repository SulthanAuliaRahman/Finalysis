<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use App\Models\Analisis;
use Illuminate\Http\Request;
use App\Jobs\GenerateAnalisisJob;
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
            'analisis'   => [
                'id'                 => $analisis->id,
                'periode_label'      => $this->buildPeriodeLabel($dokumen),
                'ai_summary_insight' => $analisis->ringkasan_laporan,
                'status_generate'    => $analisis->status_generate,
                'progress_current'   => $analisis->progress_current,
                'progress_total'     => $analisis->progress_total,
                'error_message'      => $analisis->error_message,
                'section_status'     => $analisis->section_status,
                'semua_selesai'      => $analisis->semuaSelesai(),
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

    // dipanggil tombol "Generate analisis" di Detail.jsx -> dispatch job, TIDAK menunggu AI
    public function generateSeluruhAnalisis(Perusahaan $perusahaan, Analisis $analisis)
    {
        if ($analisis->status_generate === 'processing') {
            return back()->withErrors(['message' => 'Analisis sedang diproses, mohon tunggu.']);
        }

        if ($analisis->semuaSelesai()) {
            return back()->withErrors(['message' => 'Analisis sudah lengkap di-generate.']);
        }

        GenerateAnalisisJob::dispatch($analisis);

        return back()->with(['success' => 'Proses generate analisis dimulai di background.']);
    }

    // dipanggil React tiap 2 detik selama status_generate == processing
    public function statusGenerate(Perusahaan $perusahaan, Analisis $analisis)
    {
        return response()->json([
            'status_generate'    => $analisis->status_generate,
            'progress_current'   => $analisis->progress_current,
            'progress_total'     => $analisis->progress_total,
            'error_message'      => $analisis->error_message,
            'ai_summary_insight' => $analisis->ringkasan_laporan,
            'section_status'     => $analisis->section_status,
            'semua_selesai'      => $analisis->semuaSelesai(),
        ]);
    }

    // regenerasi 1 section, HANYA boleh kalau proses generate awal sudah tuntas
    public function generateAnalisis(Request $request, Perusahaan $perusahaan, Analisis $analisis)
    {
        $request->validate([
            'section'     => 'required|string|in:'.implode(',', Analisis::SECTIONS),
            'user_prompt' => 'nullable|string|max:1000',
        ]);


        if (!$analisis->semuaSelesai()) {
            return back()->withErrors(['message' => 'Selesaikan generate seluruh analisis terlebih dahulu sebelum regenerasi per section.']);
        }

        GenerateAnalisisJob::dispatch($analisis, $request->input('section'), $request->input('user_prompt'));

        return back()->with(['success' => 'Regenerasi section dimulai.']);
    }
}
