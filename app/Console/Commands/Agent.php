<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use NeuronAI\Chat\Messages\UserMessage;

// Mengimpor semua Agen Terfokus dari sub-folder RAG sesuai definisi file Anda
use App\Neuron\RAG\LiquidityAgent;
use App\Neuron\RAG\ProfitabilityAgent; 
use App\Neuron\RAG\SolvencyAgent;      
use App\Neuron\RAG\ActivityAgent;
use App\Neuron\RAG\CommonsizeAgent;    
use App\Neuron\RAG\DupontAgent;        
use App\Neuron\RAG\TrendAgent;         
use App\Neuron\RAG\ConclusionAgent;
use App\Services\FinancialService;

class Agent extends Command
{
    protected $signature = 'app:agent';
    protected $description = 'Menjalankan Analisis Keuangan Berbasis Multi-Agent Terfokus';

    public function handle()
    {
        $this->warn("Menjalankan mode simulasi: Membaca hardcoded data jika database kosong...");

        
        // BLOK 1 — PROFIL PERUSAHAAN
        
        $company = (object)[
            'id' => 1,
            'name' => 'PT Pilar Wahana Artha',
            'type' => 'Manufaktur', 
            'scale' => 'Menengah / UMKM', 
            'description' => 'Perusahaan yang bergerak di bidang penyusunan komponen manufaktur untuk keperluan Tugas Akhir.'
        ];
        $period = '2023';

        $companyProfile = <<<PROFILE
            ### PROFIL PERUSAHAAN
            Nama Perusahaan : {$company->name}
            Jenis Usaha     : {$company->type}
            Skala Usaha     : {$company->scale}
            Deskripsi Bisnis: {$company->description}
            Periode Analisis: {$period}
            PROFILE;

        // BLOK 2 — DATA FINANSIAL & PENGHITUNGAN METRIK
        
        $fin = (object)[
            'total_assets' => 1200000000, 'total_liabilities' => 400000000, 'total_equity' => 800000000,
            'current_assets' => 500000000, 'current_liabilities' => 250000000, 'non_current_assets' => 700000000,
            'non_current_liabilities' => 150000000, 'revenue' => 1000000000, 'net_profit' => 120000000,
            'gross_profit' => 400000000, 'operating_expenses' => 250000000, 'ebit' => 150000000,
            'interest_expense' => 20000000, 'inventory' => 100000000, 'cash' => 150000000
        ];

        $financialService = new FinancialService();

        
        $currentRatio = $financialService->currentRatio($fin->current_assets, $fin->current_liabilities);
        $quickRatio   = $financialService->quickRatio($fin->current_assets, $fin->inventory, $fin->current_liabilities);
        $cashRatio    = $financialService->cashRatio($fin->cash, $fin->current_liabilities);
        
        $npm          = $financialService->netProfitMargin($fin->net_profit, $fin->revenue);
        $roa          = $financialService->returnOnAssets($fin->net_profit, $fin->total_assets);
        $roe          = $financialService->returnOnEquity($fin->net_profit, $fin->total_equity);
        
        $der          = $financialService->debtToEquity($fin->total_liabilities, $fin->total_equity);
        $dar          = $financialService->debtToAsset($fin->total_liabilities, $fin->total_assets);
        $tato         = $financialService->totalAssetTurnover($fin->revenue, $fin->total_assets);
        $leverage     = $financialService->financialLeverage($fin->total_assets, $fin->total_equity);

        // Perhitungan Persentase Common-Size via Service
        $cs_grossProfit  = $financialService->commonSizePercentage($fin->gross_profit, $fin->revenue);
        $cs_operatingExp = $financialService->commonSizePercentage($fin->operating_expenses, $fin->revenue);
        $cs_netProfit    = $financialService->commonSizePercentage($fin->net_profit, $fin->revenue);
        
        $cs_currentAssets = $financialService->commonSizePercentage($fin->current_assets, $fin->total_assets);
        $cs_currentLiab   = $financialService->commonSizePercentage($fin->current_liabilities, $fin->total_assets);
        $cs_equity        = $financialService->commonSizePercentage($fin->total_equity, $fin->total_assets);

  
        // BLOK 3 — KONTEKS DOKUMEN LAPORAN KEUANGAN (RAG Retrieval)
    
        $chunkContext = "\n### KONTEKS DOKUMEN LAPORAN KEUANGAN (RAG)\n";
        try {
            $query = "insight kondisi keuangan perusahaan {$company->name} periode {$period}";
            $topChunks = \App\Services\RetrievalService::retrieve($query, topK: 3);
            foreach ($topChunks as $index => $chunk) {
                $no = $index + 1;
                $chunkContext .= "[Chunk {$no} — Hal. {$chunk->page_number}] {$chunk->chunk_text}\n";
            }
        } catch (\Exception $e) {
            $chunkContext .= "[Simulasi RAG]: Pendapatan usaha tumbuh positif sebesar 15%. Namun, manajemen sengaja menahan porsi kas operasional yang lebih besar untuk mengamankan likuiditas jangka pendek.\n";
        }

  
        // BLOK 4 — DISTRIBUSI PROMPT KE AGENT SPESIFIK
     
        $this->comment("\n=== MEMULAI EKSEKUSI MULTI-AGENT TERFOKUS ===");

        // 1. Eksekusi Agen Likuiditas [cite: 26]
        $this->info("1/8 Menjalankan Agen Likuiditas...");
        $liqPrompt = $companyProfile . "\nMETRIK LIKUIDITAS: Current Ratio: {$currentRatio}, Quick Ratio: {$quickRatio}, Cash Ratio: {$cashRatio}\n[BENCHMARK: Current Ratio >= 1.5 sehat, Quick Ratio >= 1.0 sehat, Cash Ratio >= 0.2]\n" . $chunkContext;
        $liqResult = LiquidityAgent::make()->chat(new UserMessage($liqPrompt))->getMessage()->getContent();

        // 2. Eksekusi Agen Profitabilitas [cite: 35]
        $this->info("2/8 Menjalankan Agen Profitabilitas...");
        $profPrompt = $companyProfile . "\nMETRIK PROFITABILITAS: Net Profit Margin (NPM): {$npm}, ROA: {$roa}, ROE: {$roe}\n[BENCHMARK: NPM Manufaktur Menengah 5-10%, ROA >= 5%, ROE 10-15%]\n" . $chunkContext;
        $profResult = ProfitabilityAgent::make()->chat(new UserMessage($profPrompt))->getMessage()->getContent();

        // 3. Eksekusi Agen Solvabilitas [cite: 43]
        $this->info("3/8 Menjalankan Agen Solvabilitas...");
        $solPrompt = $companyProfile . "\nMETRIK SOLVABILITAS: Debt to Equity Ratio (DER): {$der}, Debt to Assets Ratio (DAR): {$dar}\n[BENCHMARK KONSERVATIF: DER <= 1.00, DAR <= 0.50]\n" . $chunkContext;
        $solResult = SolvencyAgent::make()->chat(new UserMessage($solPrompt))->getMessage()->getContent();

        // 4. Eksekusi Agen Aktivitas [cite: 2]
        $this->info("4/8 Menjalankan Agen Aktivitas...");
        $actPrompt = $companyProfile . "\nMETRIK AKTIVITAS: Total Asset Turnover (TATO): {$tato}\n[BENCHMARK: TATO >= 1.0 dianggap produktif]\n" . $chunkContext;
        $actResult = ActivityAgent::make()->chat(new UserMessage($actPrompt))->getMessage()->getContent();

        // 5. Eksekusi Agen Common-Size [cite: 10]
        $this->info("5/8 Menjalankan Agen Common-Size...");
        $csPrompt = $companyProfile . "\nCOMMON-SIZE INCOME STATEMENT: Gross Profit %: {$cs_grossProfit}%, Operating Expenses %: {$cs_operatingExp}%, Net Profit %: {$cs_netProfit}%\nCOMMON-SIZE BALANCE SHEET: Current Assets %: {$cs_currentAssets}%, Current Liabilities %: {$cs_currentLiab}%, Total Equity %: {$cs_equity}%\n" . $chunkContext;
        $csResult = CommonsizeAgent::make()->chat(new UserMessage($csPrompt))->getMessage()->getContent();

        // 6. Eksekusi Agen DuPont [cite: 18]
        $this->info("6/8 Menjalankan Agen DuPont...");
        $dupontPrompt = $companyProfile . "\nDUPONT PARAMETERS: Net Profit Margin (NPM): {$npm}, Asset Turnover (TATO): {$tato}, Leverage Multiplier: {$leverage}\n" . $chunkContext;
        $dupontResult = DupontAgent::make()->chat(new UserMessage($dupontPrompt))->getMessage()->getContent();

        // 7. Eksekusi Agen Tren 
        $this->info("7/8 Menjalankan Agen Tren...");
        // Catatan: Karena simulasi saat ini menggunakan data satu periode (2023), prompt ini mengirimkan gambaran dasar untuk perbandingan horizontal.
        $trendPrompt = $companyProfile . "\nDATA PERIODE BERJALAN: Revenue: {$fin->revenue}, Net Profit: {$fin->net_profit}, Total Assets: {$fin->total_assets}\n" . $chunkContext;
        $trendResult = TrendAgent::make()->chat(new UserMessage($trendPrompt))->getMessage()->getContent();

        // 8. Eksekusi Agen Kesimpulan Eksekutif (Yanto-Konklusi) [cite: 60, 63]
        $this->info("8/8 Menjalankan Agen Kesimpulan Eksekutif...");
        
        // Menyatukan seluruh output tekstual dari agen 1 sampai 7 sebagai bahan review Yanto-Konklusi 
        $allAnalysesText = "### KUMPULAN LAPORAN TIM SPESIALIS\n\n" .
            $liqResult . "\n\n" .
            $profResult . "\n\n" .
            $solResult . "\n\n" .
            $actResult . "\n\n" .
            $csResult . "\n\n" .
            $dupontResult . "\n\n" .
            $trendResult;

        $conclusionPrompt = $companyProfile . "\n" . $allAnalysesText;
        $conclusionResult = ConclusionAgent::make()->chat(new UserMessage($conclusionPrompt))->getMessage()->getContent();

        
        // BLOK 5 — KONSOLIDASI OUTPUT LAPORAN AKHIR
        
        $this->comment("\n=================== HASIL KONSOLIDASI MULTI-AGENT ===================");
        echo "# LAPORAN ANALISIS FINANSIAL EKSEKUTIF (RAG-AUGMENTED MULTI-AGENT)\n\n";
        echo $liqResult . "\n\n";
        echo $profResult . "\n\n";
        echo $solResult . "\n\n";
        echo $actResult . "\n\n";
        echo $csResult . "\n\n";
        echo $dupontResult . "\n\n";
        echo $trendResult . "\n\n";
        
        // Mencetak hasil Kesimpulan Eksekutif di bagian akhir laporan
        echo $conclusionResult . "\n\n";
    }
}