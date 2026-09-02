<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use App\Models\Dokumen;
use App\Services\DokumenService;
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
}
