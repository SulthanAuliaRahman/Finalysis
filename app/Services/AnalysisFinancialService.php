<?php

namespace App\Services;

use App\Models\Analisis;
use App\Models\Neraca;
use App\Models\LabaRugi;
use Illuminate\Validation\ValidationException;
use NeuronAI\Chat\Messages\UserMessage;

use App\Neuron\RAG\ProfitabilityAgent;
use App\Neuron\RAG\LiquidityAgent;
use App\Neuron\RAG\SolvencyAgent;
use App\Neuron\RAG\ActivityAgent;

class AnalysisFinancialService
{
    protected FinancialService $financialService;

    // Inject FinancialService ke dalam pipeline
    public function __construct(FinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function validasiKelengkapanData(string $section, ?Neraca $neraca, ?LabaRugi $labaRugi): void
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

    public function prosesLikuiditas(Analisis $analisis, Neraca $neraca): void
    {
        $inventarisDefault = 0;
        $kasDefault = 0;

        $cr = $this->financialService->currentRatio((float) $neraca->current_assets, (float) $neraca->current_liabilities);
        $qr = $this->financialService->quickRatio((float) $neraca->current_assets, $inventarisDefault, (float) $neraca->current_liabilities);
        $csr = $this->financialService->cashRatio($kasDefault, (float) $neraca->current_liabilities);

        // Build Prompt untuk LiquidityAgent
        $Prompt = "Berikan narasi analisis likuiditas berdasarkan data berikut: \n";
        $Prompt .= "Current Ratio (CR): " . round($cr * 100, 2) . "%\n";
        $Prompt .= "Quick Ratio (QR): " . round($qr * 100, 2) . "%\n";
        $Prompt .= "Cash Ratio (CSR): " . round($csr * 100, 2) . "%\n";

        $response = LiquidityAgent::make()->chat(new UserMessage($Prompt));

        $analisis->likuiditas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'current_ratio' => round($cr * 100, 2),
                'quick_ratio'   => round($qr * 100, 2),
                'cash_ratio'    => round($csr * 100, 2),
                'narasi_likuiditas_AI' => $response->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.',
            ]
        );
    }

    public function prosesProfitabilitas(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        $npm = $this->financialService->netProfitMargin((float) $labaRugi->laba_bersih, (float) $labaRugi->pendapatan);
        $roa = $this->financialService->returnOnAssets((float) $labaRugi->laba_bersih, (float) $neraca->total_assets);
        $roe = $this->financialService->returnOnEquity((float) $labaRugi->laba_bersih, (float) $neraca->total_equity);

        $Prompt = "Berikan narasi analisis profitabilitas berdasarkan data berikut: \n";
        $Prompt .= "Net Profit Margin (NPM): " . round($npm * 100, 2) . "%\n";
        $Prompt .= "Return on Assets (ROA): " . round($roa * 100, 2) . "%\n";
        $Prompt .= "Return on Equity (ROE): " . round($roe * 100, 2) . "%\n";

        $response = ProfitabilityAgent::make()->chat(new UserMessage($Prompt));

        $analisis->profitabilitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'net_profit_margin' => round($npm * 100, 2),
                'ROA'               => round($roa * 100, 2),
                'ROE'               => round($roe * 100, 2),
                'narasi_profitabilitas_AI' => $response->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.',
            ]
        );
    }

    public function prosesSolvabilitas(Analisis $analisis, Neraca $neraca): void
    {
        $dte = $this->financialService->debtToEquity((float) $neraca->total_liabilities, (float) $neraca->total_equity);
        $dta = $this->financialService->debtToAsset((float) $neraca->total_liabilities, (float) $neraca->total_assets);

        // Build Prompt untuk SolvencyAgent
        $Prompt = "Berikan narasi analisis solvabilitas berdasarkan data berikut: \n";
        $Prompt .= "Debt to Equity Ratio (DTE): " . round($dte * 100, 2) . "%\n";
        $Prompt .= "Debt to Asset Ratio (DTA): " . round($dta * 100, 2) . "%\n";

        $response = SolvencyAgent::make()->chat(new UserMessage($Prompt));

        $analisis->solvabilitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'debt_to_equity' => round($dte * 100, 2),
                'debt_to_asset'  => round($dta * 100, 2),
                'narasi_solvabilitas_AI' => $response->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.',
            ]
        );
    }

    public function prosesAktivitas(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        $tato = $this->financialService->totalAssetTurnover((float) $labaRugi->pendapatan, (float) $neraca->total_assets);

        // Build Prompt untuk ActivityAgent
        // Catatan: TATO biasanya diukur dalam satuan "kali" perputaran, bukan persentase
        $Prompt = "Berikan narasi analisis aktivitas operasional berdasarkan data berikut: \n";
        $Prompt .= "Total Asset Turnover (TATO): " . round($tato * 100, 2) . " kali\n";

        $response = ActivityAgent::make()->chat(new UserMessage($Prompt));

        $analisis->aktivitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'total_asset_turnover' => round($tato * 100, 2),
                'narasi_aktivitas_AI' => $response->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.',
            ]
        );
    }

    public function updateStatusJikaLengkap(Analisis $analisis): void
    {
        $lengkap = $analisis->likuiditas()->exists()
                && $analisis->profitabilitas()->exists()
                && $analisis->solvabilitas()->exists()
                && $analisis->aktivitas()->exists();

        if ($lengkap && $analisis->status === 'belum dianalisis') {
            $analisis->update(['status' => 'sudah dianalisis']);
        }
    }
}
