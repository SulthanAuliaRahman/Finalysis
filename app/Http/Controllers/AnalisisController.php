<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use App\Models\Analisis;
use App\Models\Neraca;
use App\Models\LabaRugi;
use App\Services\FinancialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

use App\Neuron\RAG\RagAgent;

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

        // TAMBAHAN: Ambil Neraca & Laba Rugi untuk breakdown UI
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

    public function regenerasi(Request $request, Perusahaan $perusahaan, Analisis $analisis, FinancialService $financialService)
    {
        $request->validate([
            'section' => 'required|string|in:likuiditas,profitabilitas,solvabilitas,aktivitas,summary'
        ]);

        $section = $request->input('section');

        // 1. Ambil data Fundamental Terbaru sesuai periode Analisis
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

        // 2. Validasi Ketersediaan Data Berdasarkan Section yang Dipilih
        $this->validasiKelengkapanData($section, $neraca, $labaRugi);

        // 3. Jalankan Proses Spesifik secara Modular dalam Transaction
        DB::transaction(function () use ($section, $analisis, $neraca, $labaRugi, $financialService) {
        match ($section) {
                'likuiditas'     => $this->kalkulasiLikuiditas($analisis, $neraca, $financialService),
                'profitabilitas' => $this->kalkulasiProfitabilitas($analisis, $neraca, $labaRugi, $financialService),
                'solvabilitas'   => $this->kalkulasiSolvabilitas($analisis, $neraca, $financialService),
                'aktivitas'      => $this->kalkulasiAktivitas($analisis, $neraca, $labaRugi, $financialService),
                'summary'        => null, // TODO: Implementasi trigger prompt AI di sini pada fase selanjutnya
            };

            // 4. Update Status Analisis Utama jika seluruh 4 card rasio sudah terisi
            $this->updateStatusJikaLengkap($analisis);
        });

        return back();
    }

    // =========================================================================
    // PRIVATE HELPERS : MODULAR CALCULATION LOGIC

    private function validasiKelengkapanData(string $section, ?Neraca $neraca, ?LabaRugi $labaRugi): void
    {
        if (in_array($section, ['likuiditas', 'solvabilitas']) && !$neraca) {
            throw ValidationException::withMessages([
                'regenerasi' => "Data Neraca belum tersedia untuk menghitung rasio $section."
            ]);
        }

        if (in_array($section, ['profitabilitas', 'aktivitas']) && (!$neraca || !$labaRugi)) {
            throw ValidationException::withMessages([
                'regenerasi' => "Data Neraca dan Laba Rugi harus lengkap untuk menghitung rasio $section."
            ]);
        }
    }

    private function kalkulasiLikuiditas(Analisis $analisis, Neraca $neraca, FinancialService $fs): void
    {
        $inventarisDefault = 0;
        $kasDefault = 0;

        $cr = $fs->currentRatio((float) $neraca->current_assets, (float) $neraca->current_liabilities);
        $qr = $fs->quickRatio((float) $neraca->current_assets, $inventarisDefault, (float) $neraca->current_liabilities);
        $csr = $fs->cashRatio($kasDefault, (float) $neraca->current_liabilities);

        $analisis->likuiditas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'current_ratio' => round($cr * 100, 2),
                'quick_ratio'   => round($qr * 100, 2),
                'cash_ratio'    => round($csr * 100, 2),
            ]
        );
    }

    private function kalkulasiProfitabilitas(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi, FinancialService $fs): void
    {
        $npm = $fs->netProfitMargin((float) $labaRugi->laba_bersih, (float) $labaRugi->pendapatan);
        $roa = $fs->returnOnAssets((float) $labaRugi->laba_bersih, (float) $neraca->total_assets);
        $roe = $fs->returnOnEquity((float) $labaRugi->laba_bersih, (float) $neraca->total_equity);

        $analisis->profitabilitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'net_profit_margin' => round($npm * 100, 2),
                'ROA'               => round($roa * 100, 2),
                'ROE'               => round($roe * 100, 2),
            ]
        );
    }

    private function kalkulasiSolvabilitas(Analisis $analisis, Neraca $neraca, FinancialService $fs): void
    {
        $dte = $fs->debtToEquity((float) $neraca->total_liabilities, (float) $neraca->total_equity);
        $dta = $fs->debtToAsset((float) $neraca->total_liabilities, (float) $neraca->total_assets);

        $analisis->solvabilitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'debt_to_equity' => round($dte * 100, 2),
                'debt_to_asset'  => round($dta * 100, 2),
            ]
        );
    }

    private function kalkulasiAktivitas(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi, FinancialService $fs): void
    {
        $tato = $fs->totalAssetTurnover((float) $labaRugi->pendapatan, (float) $neraca->total_assets);

        $analisis->aktivitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'total_asset_turnover' => round($tato * 100, 2),
            ]
        );
    }

    private function updateStatusJikaLengkap(Analisis $analisis): void
    {
        // Cek apakah data relasi keempat metrik sudah ter-create di database
        $lengkap = $analisis->likuiditas()->exists()
                && $analisis->profitabilitas()->exists()
                && $analisis->solvabilitas()->exists()
                && $analisis->aktivitas()->exists();

        if ($lengkap && $analisis->status === 'belum dianalisis') {
            $analisis->update(['status' => 'sudah dianalisis']);
        }
    }
}
