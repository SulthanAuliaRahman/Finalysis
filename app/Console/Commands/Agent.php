<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use NeuronAI\Chat\Messages\UserMessage;
// Mengimpor keempat Agen Terfokus dari sub-folder Agents
use App\Neuron\Agents\LiquidityAgent;
use App\Neuron\Agents\ProfitabilityDuPontAgent;
use App\Neuron\Agents\SolvencyStructureAgent;
use App\Neuron\Agents\ActivityAgent;

class Agent extends Command
{
    protected $signature = 'app:agent';
    protected $description = 'Menjalankan Analisis Keuangan Berbasis Multi-Agent Terfokus';

    public function handle()
    {
        $this->warn("Menjalankan mode simulasi: Membaca hardcoded data jika database kosong...");

        
        // BLOK 1 — PROFIL PERUSAHAAN (Murni Hardcoded untuk Sementara)
        
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

        // BLOK 2 — DATA FINANSIAL & PENGHITUNGAN METRIK (Murni Hardcoded)
        
        $fin = (object)[
            'total_assets' => 1200000000, 'total_liabilities' => 400000000, 'total_equity' => 800000000,
            'current_assets' => 500000000, 'current_liabilities' => 250000000, 'non_current_assets' => 700000000,
            'non_current_liabilities' => 150000000, 'revenue' => 1000000000, 'net_profit' => 120000000,
            'gross_profit' => 400000000, 'operating_expenses' => 250000000, 'ebit' => 150000000,
            'interest_expense' => 20000000, 'inventory' => 100000000, 'cash' => 150000000
        ];

        // Penghitungan Rasio Keuangan
        $currentRatio = $fin->current_assets / $fin->current_liabilities;
        $quickRatio   = ($fin->current_assets - $fin->inventory) / $fin->current_liabilities;
        $cashRatio    = $fin->cash / $fin->current_liabilities;
        $npm          = $fin->net_profit / $fin->revenue;
        $roa          = $fin->net_profit / $fin->total_assets;
        $roe          = $fin->net_profit / $fin->total_equity;
        $der          = $fin->total_liabilities / $fin->total_equity;
        $dar          = $fin->total_liabilities / $fin->total_assets;
        $tato         = $fin->revenue / $fin->total_assets;
        $leverage     = $fin->total_assets / $fin->total_equity;

        // Perhitungan Persentase Common-Size
        $cs_grossProfit     = ($fin->gross_profit / $fin->revenue) * 100;
        $cs_operatingExp    = ($fin->operating_expenses / $fin->revenue) * 100;
        $cs_netProfit       = ($fin->net_profit / $fin->revenue) * 100;
        $cs_currentAssets   = ($fin->current_assets / $fin->total_assets) * 100;
        $cs_currentLiab     = ($fin->current_liabilities / $fin->total_assets) * 100;
        $cs_equity          = ($fin->total_equity / $fin->total_assets) * 100;

  
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

        // 1. Eksekusi Agen Likuiditas
        $this->info("1/4 Menjalankan Agen Likuiditas...");
        $liqPrompt = $companyProfile . "\nMETRIK LIKUIDITAS: Current Ratio: {$currentRatio}, Quick Ratio: {$quickRatio}, Cash Ratio: {$cashRatio}\n[BENCHMARK: Current Ratio >= 1.5 sehat, Quick Ratio >= 1.0 sehat, Cash Ratio >= 0.2]\n" . $chunkContext;
        $liqResult = LiquidityAgent::make()->chat(new UserMessage($liqPrompt))->getMessage()->getContent();

        // 2. Eksekusi Agen Profitabilitas & DuPont
        $this->info("2/4 Menjalankan Agen Profitabilitas & DuPont...");
        $profPrompt = $companyProfile . "\nMETRIK PROFITABILITAS: Net Profit Margin (NPM): {$npm}, ROA: {$roa}, ROE: {$roe}\nDUPONT DECOMPOSITION: Asset Turnover: {$tato}, Financial Leverage Factor: {$leverage}\nCOMMON-SIZE INCOME STATEMENT: Gross Profit %: {$cs_grossProfit}%, Operating Expenses %: {$cs_operatingExp}%, Net Profit %: {$cs_netProfit}%\n" . $chunkContext;
        $profResult = ProfitabilityDuPontAgent::make()->chat(new UserMessage($profPrompt))->getMessage()->getContent();

        // 3. Eksekusi Agen Solvabilitas & Struktur Modal
        $this->info("3/4 Menjalankan Agen Solvabilitas & Struktur Modal...");
        $solPrompt = $companyProfile . "\nMETRIK SOLVABILITAS: Debt to Equity Ratio (DER): {$der}, Debt to Assets Ratio (DAR): {$dar}\nCOMMON-SIZE BALANCE SHEET: Current Assets %: {$cs_currentAssets}%, Current Liabilities %: {$cs_currentLiab}%, Total Equity %: {$cs_equity}%\n" . $chunkContext;
        $solResult = SolvencyStructureAgent::make()->chat(new UserMessage($solPrompt))->getMessage()->getContent();

        // 4. Eksekusi Agen Aktivitas
        $this->info("4/4 Menjalankan Agen Aktivitas...");
        $actPrompt = $companyProfile . "\nMETRIK AKTIVITAS: Total Asset Turnover (TATO): {$tato}\n[BENCHMARK: TATO >= 1.0 dianggap produktif]\n" . $chunkContext;
        $actResult = ActivityAgent::make()->chat(new UserMessage($actPrompt))->getMessage()->getContent();

        
        // BLOK 5 — KONSOLIDASI OUTPUT LAPORAN AKHIR
    
        $this->comment("\n=================== HASIL KONSOLIDASI MULTI-AGENT ===================");
        echo "# LAPORAN ANALISIS FINANSIAL EKSEKUTIF (RAG-AUGMENTED MULTI-AGENT)\n\n";
        echo $liqResult . "\n\n";
        echo $profResult . "\n\n";
        echo $solResult . "\n\n";
        echo $actResult . "\n\n";
    }
}