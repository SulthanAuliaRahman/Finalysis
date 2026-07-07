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
use App\Neuron\RAG\CommonsizeAgent;
use App\Neuron\RAG\DupontAgent;

use App\Services\CalculateFinancialService;
use App\Services\TrendAnalysisService;

class AnalysisFinancialService
{
    protected CalculateFinancialService $calculateFinancialService;
    protected TrendAnalysisService $trendAnalysisService;

    public function __construct(
        CalculateFinancialService $calculateFinancialService,
        TrendAnalysisService $trendAnalysisService
    ) {
        $this->calculateFinancialService = $calculateFinancialService;
        $this->trendAnalysisService = $trendAnalysisService;
    }

    public function validasiKelengkapanData(?Neraca $neraca, ?LabaRugi $labaRugi): void
    {
        if (!$neraca || !$labaRugi) {
            throw ValidationException::withMessages([
                'hitung_rasio' => "Data Neraca dan Laba Rugi harus lengkap untuk menghitung seluruh rasio."
            ]);
        }
    }

    // Delegasi ke CalculateFinancialService — controller tetap panggil method ini.
    public function hitungSemuaRasio(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        $this->calculateFinancialService->hitungSemuaRasio($analisis, $neraca, $labaRugi);
    }

    // Delegasi ke TrendAnalysisService — controller tetap panggil method ini.
    public function prosesTrend(Analisis $analisis): void
    {
        $this->trendAnalysisService->prosesTrend($analisis);
    }

    // =====================================================================
    // NARASI AI PER SECTION (baca dari data yang sudah dihitung, tidak
    // menghitung ulang apapun — perhitungan dilakukan di CalculateFinancialService).
    // Setiap hasil AI dibersihkan dari markdown sebelum disimpan.
    // =====================================================================


    private function npmBenchmarkUntukSektor(?string $sektor): string
    {
        $sektor = strtolower(trim((string) $sektor));

        return match(true) {
            str_contains($sektor, 'jasa') => 'Jasa (umumnya > 10%)',
            str_contains($sektor, 'dagang') || str_contains($sektor, 'ritel') || str_contains($sektor, 'retail')
                => 'Dagang/Ritel (umumnya 2-5%)',
            str_contains($sektor, 'manufaktur') || str_contains($sektor, 'industri')
                => 'Manufaktur (umumnya 5-10%)',
            default => 'Umum/tidak teridentifikasi (bandingkan terutama antar periode perusahaan ini sendiri sebagai acuan utama, bukan angka mutlak)',
        };
    }

    public function prosesLikuiditas(Analisis $analisis, ?string $userPrompt = null): void
    {
        $data = $analisis->likuiditas;

        $Prompt  = "Berikan narasi analisis likuiditas berdasarkan data berikut: \n";
        $Prompt .= "Current Ratio (CR): " . $data->current_ratio . "%\n";
        $Prompt .= "Quick Ratio (QR): " . $data->quick_ratio . "%\n";
        $Prompt .= "Cash Ratio (CSR): " . $data->cash_ratio . "%\n";

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = LiquidityAnalystAgent::make()->chat(new UserMessage($Prompt));

        $narasi = $response->getMessage()->getContent() ?? 'Tidak ada insight.';
        $narasi = TextCleanerService::bersihkanMarkdown($narasi);

        $data->update(['narasi_likuiditas_AI' => $narasi]);
    }

    public function prosesProfitabilitas(Analisis $analisis, ?string $userPrompt = null): void
    {
        $data = $analisis->profitabilitas;
        $sektor = $analisis->perusahaan->sektor;
        $benchmarkNpm = $this->npmBenchmarkUntukSektor($sektor);

        $Prompt  = "Berikan narasi analisis profitabilitas berdasarkan data berikut: \n";
        $Prompt .= "Sektor Perusahaan: " . ($sektor ?: 'Tidak diketahui') . "\n";
        $Prompt .= "Benchmark NPM untuk sektor ini: {$benchmarkNpm}\n";
        $Prompt .= "Net Profit Margin (NPM): " . $data->net_profit_margin . "%\n";
        $Prompt .= "Return on Assets (ROA): " . $data->ROA . "%\n";
        $Prompt .= "Return on Equity (ROE): " . $data->ROE . "%\n";

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = ProfitabilityAgent::make()->chat(new UserMessage($Prompt));

        $narasi = $response->getMessage()->getContent() ?? 'Tidak ada insight.';
        $narasi = TextCleanerService::bersihkanMarkdown($narasi);

        $data->update(['narasi_profitabilitas_AI' => $narasi]);
    }

    public function prosesSolvabilitas(Analisis $analisis, ?string $userPrompt = null): void
    {
        $data = $analisis->solvabilitas;

        $Prompt  = "Berikan narasi analisis solvabilitas berdasarkan data berikut: \n";
        $Prompt .= "Debt to Equity Ratio (DER): " . $data->debt_to_equity . "%\n";
        $Prompt .= "Debt to Asset Ratio (DAR): " . $data->debt_to_asset . "%\n";

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = SolvencyAgent::make()->chat(new UserMessage($Prompt));

        $narasi = $response->getMessage()->getContent() ?? 'Tidak ada insight.';
        $narasi = TextCleanerService::bersihkanMarkdown($narasi);

        $data->update(['narasi_solvabilitas_AI' => $narasi]);
    }

    public function prosesAktivitas(Analisis $analisis, ?string $userPrompt = null): void
    {
        $data = $analisis->aktivitas;

        $Prompt  = "Berikan narasi analisis aktivitas operasional berdasarkan data berikut: \n";
        $Prompt .= "Total Asset Turnover (TATO): " . $data->total_asset_turnover . "x\n";

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = ActivityAgent::make()->chat(new UserMessage($Prompt));

        $narasi = $response->getMessage()->getContent() ?? 'Tidak ada insight.';
        $narasi = TextCleanerService::bersihkanMarkdown($narasi);

        $data->update(['narasi_aktivitas_AI' => $narasi]);
    }

    public function prosesDupont(Analisis $analisis, ?string $userPrompt = null): void
    {
        $data = $analisis->dupont;

        $Prompt  = "Berikan narasi analisis DuPont berdasarkan data berikut: \n";
        $Prompt .= "Net Profit Margin (NPM): " . $data->net_profit_margin . "%\n";
        $Prompt .= "Total Asset Turnover (TATO): " . $data->total_asset_turnover . " kali\n";
        $Prompt .= "Leverage Multiplier (Total Aset / Ekuitas): " . $data->leverage_multiplier . " kali\n";
        $Prompt .= "Hasil ROE = NPM x TATO x Leverage: " . $data->roe . "%\n";

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = DupontAgent::make()->chat(new UserMessage($Prompt));

        $narasi = $response->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.';
        $narasi = TextCleanerService::bersihkanMarkdown($narasi);

        $data->update(['narasi_dupont_AI' => $narasi]);
    }

    public function prosesCommonsize(Analisis $analisis, ?string $userPrompt = null): void
    {
        $data = $analisis->commonsize;

        $Prompt  = "Berikan narasi analisis common-size berdasarkan data berikut: \n";
        $Prompt .= "--- Common-Size Income Statement (basis Pendapatan = 100%) ---\n";
        $Prompt .= "Pendapatan Usaha: 100%\n";
        $Prompt .= "HPP: " . $data->hpp_persen . "%\n";
        $Prompt .= "Laba Kotor: " . $data->laba_kotor_persen . "%\n";
        $Prompt .= "Beban Lain-lain & Pajak (gabungan OpEx+Bunga+Pajak): " . $data->beban_lain_pajak_persen . "%\n";
        $Prompt .= "Laba Bersih: " . $data->laba_bersih_persen . "%\n";
        $Prompt .= "PENTING: sumber data hanya mencatat Pendapatan, Laba Kotor, dan Laba Bersih. OpEx, EBIT, dan Beban Bunga TIDAK tercatat terpisah, sehingga digabung jadi satu pos 'Beban Lain-lain & Pajak'. JANGAN memecah/mengarang angka OpEx, EBIT, atau Bunga secara individual — bahas pos gabungan ini apa adanya.\n";
        $Prompt .= "--- Common-Size Balance Sheet (basis Total Aset = 100%) ---\n";
        $Prompt .= "Aset Lancar: " . $data->aset_lancar_persen . "%\n";
        $Prompt .= "Aset Tetap: " . $data->aset_tetap_persen . "%\n";
        $Prompt .= "Liabilitas Lancar: " . $data->liabilitas_lancar_persen . "%\n";
        $Prompt .= "Liabilitas Jangka Panjang: " . $data->liabilitas_panjang_persen . "%\n";
        $Prompt .= "Ekuitas: " . $data->ekuitas_persen . "%\n";

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = CommonsizeAgent::make()->chat(new UserMessage($Prompt));

        $narasi = $response->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.';
        $narasi = TextCleanerService::bersihkanMarkdown($narasi);

        $data->update(['narasi_commonsize_AI' => $narasi]);
    }

    // =====================================================================
    // STATUS
    // =====================================================================

    public function updateStatusJikaLengkap(Analisis $analisis): void
    {
        $lengkap = $analisis->likuiditas()->exists()
                && $analisis->profitabilitas()->exists()
                && $analisis->solvabilitas()->exists()
                && $analisis->aktivitas()->exists()
                && $analisis->commonsize()->exists()
                && $analisis->dupont()->exists()
                && $analisis->trend()->exists();

        if ($lengkap) {
            $analisis->update(['status' => 'sudah dianalisis']);
        }
    }
}
