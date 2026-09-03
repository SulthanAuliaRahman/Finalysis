<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use App\Models\Dokumen;
use App\Models\Analisis;
use App\Services\DokumenService;
use App\Services\CalculateFinancialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Exception;

class DokumenController extends Controller
{
    public function __construct(protected DokumenService $dokumenService) {}

    public function index(Perusahaan $perusahaan)
    {
        return Inertia::render('Perusahaan/Dokumen/Index', [
            'perusahaan'  => $perusahaan,
            'dokumenList' => $perusahaan->dokumen()->latest()->get(),
        ]);
    }

    public function create(Perusahaan $perusahaan)
    {
        return Inertia::render('Perusahaan/Dokumen/Create', [
            'perusahaan' => $perusahaan,
        ]);
    }

    public function importExcel(Request $request, Perusahaan $perusahaan)
    {
        $validated = $request->validate([
            'file'         => ['required', 'file', 'mimes:xlsx,xls'],
            'periode_type' => ['required', 'in:annual,quarterly,monthly'],
            'tahun'        => ['required', 'integer', 'min:1900', 'max:2100'],
            'quarter'      => ['required_if:periode_type,quarterly', 'nullable', 'integer', 'between:1,4'],
            'bulan'        => ['required_if:periode_type,monthly', 'nullable', 'integer', 'between:1,12'],
        ]);

        try {
            $dokumen = $this->dokumenService->importExcel($perusahaan, $validated);

            $analisis = Analisis::create(['dokumen_id' => $dokumen->id]);
            $this->hitungDataAnalissisLaporan($analisis, new CalculateFinancialService());

        } catch (Exception $e) {
            // Ditangkap onError di Create.jsx via errs.file
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        return redirect()
            ->route('perusahaan.dokumen.detail', [$perusahaan->id, $dokumen->id])
            ->with('success', 'Berkas berhasil diunggah dan diekstrak.');
    }

    public function detail(Perusahaan $perusahaan, Dokumen $dokumen)
    {
        $dokumen->load(['neraca', 'labaRugi', 'chartOfAccounts']);

        return Inertia::render('Perusahaan/Dokumen/DetailDokumen', [
            'perusahaan' => $perusahaan,
            'dokumen'    => [
                'id'                => $dokumen->id,
                'nama_file'         => $dokumen->nama_file,
                'storage_path'      => $dokumen->storage_path,
                'periode_type'      => $dokumen->periode_type,
                'tahun'             => $dokumen->tahun,
                'quarter'           => $dokumen->quarter,
                'bulan'             => $dokumen->bulan,
                'neraca'            => $dokumen->neraca,
                'laba_rugi'         => $dokumen->labaRugi,
                'chart_of_accounts' => $dokumen->chartOfAccounts,
            ],
        ]);
    }

    public function destroy(Perusahaan $perusahaan, Dokumen $dokumen)
    {
        Storage::disk('public')->delete($dokumen->storage_path);
        $dokumen->delete();

        return redirect()->route('perusahaan.dokumen.index', $perusahaan->id)
            ->with('success', 'Dokumen berhasil dihapus.');
    }

    private function hitungDataAnalissisLaporan(Analisis $analisis,CalculateFinancialService $calculateFinancialService) {
        $analisis->load([
            'dokumen.neraca',
            'dokumen.labaRugi',
        ]);

        $dokumen = $analisis->dokumen;
        $neraca = $dokumen->neraca;
        $labaRugi = $dokumen->labaRugi;

        if (!$neraca || !$labaRugi) {
            throw new \RuntimeException(
                'Data neraca atau laba rugi belum tersedia.'
            );
        }

        // Cari dokumen periode sebelumnya
        $query = Dokumen::where('perusahaan_id', $dokumen->perusahaan_id)
            ->where('periode_type', $dokumen->periode_type)
            ->where(function ($query) use ($dokumen) {

                if ($dokumen->periode_type === 'annual') {

                    $query->where('tahun', '<', $dokumen->tahun);

                } elseif ($dokumen->periode_type === 'quarterly') {

                    $query->where(function ($q) use ($dokumen) {
                        $q->where('tahun', '<', $dokumen->tahun)
                            ->orWhere(function ($q2) use ($dokumen) {
                                $q2->where('tahun', $dokumen->tahun)
                                    ->where('quarter', '<', $dokumen->quarter);
                            });
                    });

                }
                // elseif ($dokumen->periode_type === 'monthly') {

                //     $query->where(function ($q) use ($dokumen) {
                //         $q->where('tahun', '<', $dokumen->tahun)
                //             ->orWhere(function ($q2) use ($dokumen) {
                //                 $q2->where('tahun', $dokumen->tahun)
                //                     ->where('bulan', '<', $dokumen->bulan);
                //             });
                //     });
                // }
            })
            ->with('neraca');

        // Ambil periode yang paling dekat dengan periode sekarang
        if ($dokumen->periode_type === 'annual') {

            $dokumenSebelumnya = $query
                ->orderByDesc('tahun')
                ->first();

        } elseif ($dokumen->periode_type === 'quarterly') {

            $dokumenSebelumnya = $query
                ->orderByDesc('tahun')
                ->orderByDesc('quarter')
                ->first();

        }
        // else
        // {
        // //monthly

        //     $dokumenSebelumnya = $query
        //         ->orderByDesc('tahun')
        //         ->orderByDesc('bulan')
        //         ->first();
        // }

        $neracaSebelumnya = $dokumenSebelumnya?->neraca;

        $calculateFinancialService->hitungSemuaRasio(
            $analisis,
            $neraca,
            $labaRugi,
            $neracaSebelumnya
        );

        return back();
    }
}
