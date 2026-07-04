<?php

namespace App\Services;

use App\Models\Analisis;
use App\Models\Neraca;
use App\Models\LabaRugi;
use Illuminate\Validation\ValidationException;
use NeuronAI\Chat\Messages\UserMessage;

use App\Neuron\RAG\ProfitabilityAgent;
use App\Neuron\RAG\LiquidityAnalystAgent;
use App\Neuron\RAG\SolvencyAgent;
use App\Neuron\RAG\ActivityAgent;

class AnalysisFinancialService
{
    protected FinancialService $financialService;

    public function __construct(FinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function validasiKelengkapanData(?Neraca $neraca, ?LabaRugi $labaRugi): void
    {
        if (!$neraca || !$labaRugi) {
            throw ValidationException::withMessages([
                'hitung_rasio' => "Data Neraca dan Laba Rugi harus lengkap untuk menghitung seluruh rasio."
            ]);
        }
    }

    //tanpa AI Generate
    public function hitungSemuaRasio(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        // Likuiditas
        $cr = $this->financialService->currentRatio((float) $neraca->current_assets, (float) $neraca->current_liabilities);
        $qr = $this->financialService->quickRatio((float) $neraca->current_assets, (float) $neraca->inventory, (float) $neraca->current_liabilities);
        $csr = $this->financialService->cashRatio((float) $neraca->cash_equivalent, (float) $neraca->current_liabilities);

        $analisis->likuiditas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            ['current_ratio' => round($cr * 100, 2), 'quick_ratio' => round($qr * 100, 2), 'cash_ratio' => round($csr * 100, 2)]
        );

        // Profitabilitas
        $npm = $this->financialService->netProfitMargin((float) $labaRugi->laba_bersih, (float) $labaRugi->pendapatan);
        $roa = $this->financialService->returnOnAssets((float) $labaRugi->laba_bersih, (float) $neraca->total_assets);
        $roe = $this->financialService->returnOnEquity((float) $labaRugi->laba_bersih, (float) $neraca->total_equity);

        $analisis->profitabilitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            ['net_profit_margin' => round($npm * 100, 2), 'ROA' => round($roa * 100, 2), 'ROE' => round($roe * 100, 2)]
        );

        // Solvabilitas
        $dte = $this->financialService->debtToEquity((float) $neraca->total_liabilities, (float) $neraca->total_equity);
        $dta = $this->financialService->debtToAsset((float) $neraca->total_liabilities, (float) $neraca->total_assets);

        $analisis->solvabilitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            ['debt_to_equity' => round($dte * 100, 2), 'debt_to_asset' => round($dta * 100, 2)]
        );

        // Aktivitas
        $tato = $this->financialService->totalAssetTurnover((float) $labaRugi->pendapatan, (float) $neraca->total_assets);

        $analisis->aktivitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            ['total_asset_turnover' => round($tato * 100, 2)]
        );

        // Update Status
        if ($analisis->status === 'belum dianalisis' || $analisis->status === 'Terjadi Perubahan Data!') {
            $analisis->update(['status' => 'rasio tersedia']);
        }
    }

    // AI Generate
    public function prosesLikuiditas(Analisis $analisis, ?string $userPrompt = null): void
    {
        $data = $analisis->likuiditas;

        $Prompt = "Berikan narasi analisis likuiditas berdasarkan data berikut: \n";
        $Prompt .= "Current Ratio (CR): " . $data->current_ratio . "%\n";
        $Prompt .= "Quick Ratio (QR): " . $data->quick_ratio . "%\n";
        $Prompt .= "Cash Ratio (CSR): " . $data->cash_ratio . "%\n";

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = LiquidityAnalystAgent::make()->chat(new UserMessage($Prompt));
        $data->update(['narasi_likuiditas_AI' => $response->getMessage()->getContent() ?? 'Tidak ada insight.']);
    }

    public function prosesProfitabilitas(Analisis $analisis, ?string $userPrompt = null): void
    {
        $data = $analisis->profitabilitas;

        $Prompt = "Berikan narasi analisis profitabilitas berdasarkan data berikut: \n";
        $Prompt .= "Net Profit Margin (NPM): " . $data->net_profit_margin . "%\n";
        $Prompt .= "Return on Assets (ROA): " . $data->ROA . "%\n";
        $Prompt .= "Return on Equity (ROE): " . $data->ROE . "%\n";

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = ProfitabilityAgent::make()->chat(new UserMessage($Prompt));
        $data->update(['narasi_profitabilitas_AI' => $response->getMessage()->getContent() ?? 'Tidak ada insight.']);
    }

    public function prosesSolvabilitas(Analisis $analisis, ?string $userPrompt = null): void
    {
        $data = $analisis->solvabilitas;

        $Prompt = "Berikan narasi analisis solvabilitas berdasarkan data berikut: \n";
        $Prompt .= "Debt to Equity Ratio (DTE): " . $data->debt_to_equity . "%\n";
        $Prompt .= "Debt to Asset Ratio (DTA): " . $data->debt_to_asset . "%\n";

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = SolvencyAgent::make()->chat(new UserMessage($Prompt));
        $data->update(['narasi_solvabilitas_AI' => $response->getMessage()->getContent() ?? 'Tidak ada insight.']);
    }

    public function prosesAktivitas(Analisis $analisis, ?string $userPrompt = null): void
    {
        $data = $analisis->aktivitas;

        $Prompt = "Berikan narasi analisis aktivitas operasional berdasarkan data berikut: \n";
        $Prompt .= "Total Asset Turnover (TATO): " . $data->total_asset_turnover . "%\n";

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = ActivityAgent::make()->chat(new UserMessage($Prompt));
        $data->update(['narasi_aktivitas_AI' => $response->getMessage()->getContent() ?? 'Tidak ada insight.']);
    }

    public function updateStatusJikaLengkap(Analisis $analisis): void
    {
        // Cek apakah semua narasi sudah terisi
        $lengkap = !empty($analisis->likuiditas->narasi_likuiditas_AI)
                && !empty($analisis->profitabilitas->narasi_profitabilitas_AI)
                && !empty($analisis->solvabilitas->narasi_solvabilitas_AI)
                && !empty($analisis->aktivitas->narasi_aktivitas_AI);

        if ($lengkap) {
            $analisis->update(['status' => 'sudah dianalisis']);
        }
    }
}
