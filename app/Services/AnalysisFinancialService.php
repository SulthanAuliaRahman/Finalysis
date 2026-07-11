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
use App\Neuron\RAG\TrendAkunUtamaAgent;
use App\Neuron\RAG\TrendRasioAgent;
use App\Neuron\RAG\TrendDupontAgent;
use App\Neuron\RAG\TrendCommonsizeAgent;
use App\Neuron\RAG\TrendArusKasAgent;
use App\Neuron\RAG\SummaryAgent;

use App\Services\CalculateFinancialService;

class AnalysisFinancialService
{
    protected CalculateFinancialService $calculateFinancialService;

    public function __construct(CalculateFinancialService $calculateFinancialService)
    {
        $this->calculateFinancialService = $calculateFinancialService;
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

    // =====================================================================
    // HELPER
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

    // Label periode dari array (dipakai untuk data trend yang datang sebagai array, bukan model Analisis).
    private function labelPeriodeArray(array $a): string
    {
        if ($a['periode_type'] === 'annual') {
            return "Tahunan {$a['tahun']}";
        }
        if ($a['periode_type'] === 'quarterly') {
            return "Q{$a['quarter']} {$a['tahun']}";
        }
        return "Bulan {$a['bulan']} {$a['tahun']}";
    }

    // Sisipkan narasi hasil generate sebelumnya (kalau ada) + instruksi eksplisit
    // ke AI soal apa yang harus dilakukan dengan narasi lama itu.
    private function tambahkanKonteksNarasiSebelumnya(string &$prompt, ?string $narasiSebelumnya): void
    {
        if ($narasiSebelumnya) {
            $prompt .= "\nCatatan: berikut narasi hasil generate sebelumnya untuk periode ini:\n" . $narasiSebelumnya . "\n";
            $prompt .= "Jika ada 'Instruksi Tambahan dari Pengguna' di bawah, revisi narasi di atas sesuai instruksi tersebut. Jika tidak ada instruksi tambahan, buat narasi baru yang independen (boleh berbeda gaya/susunan kalimat dari sebelumnya), bukan mengulang persis.\n";
        }
    }

    // =====================================================================
    // NARASI AI PER SECTION (1 PERIODE)
    // =====================================================================

    public function prosesLikuiditas(Analisis $analisis, ?string $userPrompt = null): void
    {
        $data = $analisis->likuiditas;
        $perusahaan = $analisis->perusahaan;

        $Prompt  = "Informasi Perusahaan\n";
        $Prompt .= "Nama Perusahaan: {$perusahaan->nama}\n";
        $Prompt .= "Sektor: {$perusahaan->sektor}\n";
        $Prompt .= "Deskripsi: {$perusahaan->deskripsi}\n";

        $Prompt .= "Berikan narasi analisis likuiditas berdasarkan data berikut: \n";
        $Prompt .= "Current Ratio (CR): " . $data->current_ratio . "x\n";
        $Prompt .= "Quick Ratio (QR): " . $data->quick_ratio . "x\n";
        $Prompt .= "Cash Ratio (CSR): " . $data->cash_ratio . "x\n";

        $this->tambahkanKonteksNarasiSebelumnya($Prompt, $data->narasi_likuiditas_AI);

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

        $this->tambahkanKonteksNarasiSebelumnya($Prompt, $data->narasi_profitabilitas_AI);

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
        $Prompt .= "Debt to Equity Ratio (DER): " . $data->debt_to_equity . "x\n";
        $Prompt .= "Debt to Asset Ratio (DAR): " . $data->debt_to_asset . "x\n";

        $this->tambahkanKonteksNarasiSebelumnya($Prompt, $data->narasi_solvabilitas_AI);

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

        $this->tambahkanKonteksNarasiSebelumnya($Prompt, $data->narasi_aktivitas_AI);

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

        $this->tambahkanKonteksNarasiSebelumnya($Prompt, $data->narasi_dupont_AI);

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

        $this->tambahkanKonteksNarasiSebelumnya($Prompt, $data->narasi_commonsize_AI);

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = CommonsizeAgent::make()->chat(new UserMessage($Prompt));
        $narasi = $response->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.';
        $narasi = TextCleanerService::bersihkanMarkdown($narasi);

        $data->update(['narasi_commonsize_AI' => $narasi]);
    }

    // =====================================================================
    // NARASI AI PER SECTION (TREND, 5 KATEGORI TERPISAH)
    // Data diambil live dari Analisis::getXxxTrend(), bukan dari snapshot.
    // =====================================================================

    public function prosesTrendAkunUtama(Analisis $analisis, ?string $userPrompt = null): void
    {
        $trendData = $analisis->getAkunUtamaTrend();
        $periodeData = $trendData['periode_data'];

        $Prompt = "Berikan narasi analisis tren akun utama (Pendapatan, Laba Kotor, Laba Bersih, Total Aset, Kas Setara Kas, Total Ekuitas, Net Cash Flow) lintas periode berikut: \n";
        $Prompt .= "STATUS DATA: " . count($periodeData) . " periode tersedia dalam scope";
        $Prompt .= $trendData['has_gap']
            ? ", namun ada periode dengan data tidak lengkap — fokuskan narasi hanya pada periode yang datanya tersedia.\n"
            : ", seluruh data lengkap.\n";

        foreach ($periodeData as $titik) {
            $label = $this->labelPeriodeArray($titik['analisis']);
            $Prompt .= "--- {$label} ---\n";
            $Prompt .= "Pendapatan: " . number_format($titik['pendapatan'] ?? 0, 0, ',', '.') . " (Δ " . ($titik['growth_pendapatan'] !== null ? round($titik['growth_pendapatan'], 2) . '%' : '-') . ")\n";
            $Prompt .= "Laba Kotor: " . number_format($titik['laba_kotor'] ?? 0, 0, ',', '.') . " (Δ " . ($titik['growth_laba_kotor'] !== null ? round($titik['growth_laba_kotor'], 2) . '%' : '-') . ")\n";
            $Prompt .= "Laba Bersih: " . number_format($titik['laba_bersih'] ?? 0, 0, ',', '.') . " (Δ " . ($titik['growth_laba_bersih'] !== null ? round($titik['growth_laba_bersih'], 2) . '%' : '-') . ")\n";
            $Prompt .= "Total Aset: " . number_format($titik['total_assets'] ?? 0, 0, ',', '.') . " (Δ " . ($titik['growth_total_assets'] !== null ? round($titik['growth_total_assets'], 2) . '%' : '-') . ")\n";
            $Prompt .= "Kas Setara Kas: " . number_format($titik['kas_setara_kas'] ?? 0, 0, ',', '.') . " (Δ " . ($titik['growth_kas_setara_kas'] !== null ? round($titik['growth_kas_setara_kas'], 2) . '%' : '-') . ")\n";
            $Prompt .= "Total Ekuitas: " . number_format($titik['total_equity'] ?? 0, 0, ',', '.') . " (Δ " . ($titik['growth_total_equity'] !== null ? round($titik['growth_total_equity'], 2) . '%' : '-') . ")\n";
            $Prompt .= "Net Cash Flow: " . number_format($titik['net_cash_flow'] ?? 0, 0, ',', '.') . " (Δ " . ($titik['growth_net_cash_flow'] !== null ? round($titik['growth_net_cash_flow'], 2) . '%' : '-') . ")\n";
        }

        $this->tambahkanKonteksNarasiSebelumnya($Prompt, $trendData['narasi_trend_akun_utama_AI']);

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = TrendAkunUtamaAgent::make()->chat(new UserMessage($Prompt));
        $narasi = $response->getMessage()->getContent() ?? 'Tidak ada insight.';
        $narasi = TextCleanerService::bersihkanMarkdown($narasi);

        $analisis->trend()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            ['narasi_trend_akun_utama_AI' => $narasi]
        );
    }

    public function prosesTrendRasio(Analisis $analisis, ?string $userPrompt = null): void
    {
        $trendData = $analisis->getRasioTrend();
        $periodeData = $trendData['periode_data'];

        $Prompt = "Berikan narasi analisis tren rasio keuangan (likuiditas, profitabilitas, solvabilitas, aktivitas) lintas periode berikut: \n";
        $Prompt .= "STATUS DATA: " . count($periodeData) . " periode tersedia dalam scope";
        $Prompt .= $trendData['has_gap']
            ? ", namun ada periode dengan data tidak lengkap — fokuskan narasi hanya pada periode yang datanya tersedia.\n"
            : ", seluruh data lengkap.\n";

        foreach ($periodeData as $titik) {
            $a = $titik['analisis'];
            $label = $this->labelPeriodeArray($a);
            $Prompt .= "--- {$label} ---\n";
            $Prompt .= "CR: " . ($a['likuiditas']['current_ratio'] ?? '-') . "%, QR: " . ($a['likuiditas']['quick_ratio'] ?? '-') . "%, CSR: " . ($a['likuiditas']['cash_ratio'] ?? '-') . "%\n";
            $Prompt .= "NPM: " . ($a['profitabilitas']['net_profit_margin'] ?? '-') . "%, ROA: " . ($a['profitabilitas']['ROA'] ?? '-') . "%, ROE: " . ($a['profitabilitas']['ROE'] ?? '-') . "%\n";
            $Prompt .= "DER: " . ($a['solvabilitas']['debt_to_equity'] ?? '-') . "%, DAR: " . ($a['solvabilitas']['debt_to_asset'] ?? '-') . "%\n";
            $Prompt .= "TATO: " . ($a['aktivitas']['total_asset_turnover'] ?? '-') . "x\n";
        }

        $this->tambahkanKonteksNarasiSebelumnya($Prompt, $trendData['narasi_trend_rasio_AI']);

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = TrendRasioAgent::make()->chat(new UserMessage($Prompt));
        $narasi = $response->getMessage()->getContent() ?? 'Tidak ada insight.';
        $narasi = TextCleanerService::bersihkanMarkdown($narasi);

        $analisis->trend()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            ['narasi_trend_rasio_AI' => $narasi]
        );
    }

    public function prosesTrendDupont(Analisis $analisis, ?string $userPrompt = null): void
    {
        $trendData = $analisis->getDupontTrend();
        $periodeData = $trendData['periode_data'];

        $Prompt = "Berikan narasi analisis tren DuPont (NPM, TATO, Leverage Multiplier, ROE) lintas periode berikut: \n";
        $Prompt .= "STATUS DATA: " . count($periodeData) . " periode tersedia dalam scope";
        $Prompt .= $trendData['has_gap']
            ? ", namun ada periode dengan data tidak lengkap — fokuskan narasi hanya pada periode yang datanya tersedia.\n"
            : ", seluruh data lengkap.\n";

        foreach ($periodeData as $titik) {
            $a = $titik['analisis'];
            $label = $this->labelPeriodeArray($a);
            $d = $a['dupont'];
            $Prompt .= "--- {$label} ---\n";
            $Prompt .= "NPM: " . ($d['net_profit_margin'] ?? '-') . "%, TATO: " . ($d['total_asset_turnover'] ?? '-') . "x, Leverage: " . ($d['leverage_multiplier'] ?? '-') . "x, ROE: " . ($d['roe'] ?? '-') . "%\n";
        }

        $this->tambahkanKonteksNarasiSebelumnya($Prompt, $trendData['narasi_trend_dupont_AI']);

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = TrendDupontAgent::make()->chat(new UserMessage($Prompt));
        $narasi = $response->getMessage()->getContent() ?? 'Tidak ada insight.';
        $narasi = TextCleanerService::bersihkanMarkdown($narasi);

        $analisis->trend()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            ['narasi_trend_dupont_AI' => $narasi]
        );
    }

    public function prosesTrendCommonsize(Analisis $analisis, ?string $userPrompt = null): void
    {
        $trendData = $analisis->getCommonsizeTrend();
        $periodeData = $trendData['periode_data'];

        $Prompt = "Berikan narasi analisis tren common-size (proporsi vertikal Laba Rugi & Neraca) lintas periode berikut: \n";
        $Prompt .= "STATUS DATA: " . count($periodeData) . " periode tersedia dalam scope";
        $Prompt .= $trendData['has_gap']
            ? ", namun ada periode dengan data tidak lengkap — fokuskan narasi hanya pada periode yang datanya tersedia.\n"
            : ", seluruh data lengkap.\n";

        foreach ($periodeData as $titik) {
            $a = $titik['analisis'];
            $label = $this->labelPeriodeArray($a);
            $c = $a['commonsize'];
            $Prompt .= "--- {$label} ---\n";
            $Prompt .= "HPP: " . ($c['hpp_persen'] ?? '-') . "%, Laba Kotor: " . ($c['laba_kotor_persen'] ?? '-') . "%, Beban Lain & Pajak: " . ($c['beban_lain_pajak_persen'] ?? '-') . "%, Laba Bersih: " . ($c['laba_bersih_persen'] ?? '-') . "%\n";
            $Prompt .= "Aset Lancar: " . ($c['aset_lancar_persen'] ?? '-') . "%, Aset Tetap: " . ($c['aset_tetap_persen'] ?? '-') . "%, Liabilitas Lancar: " . ($c['liabilitas_lancar_persen'] ?? '-') . "%, Liabilitas Jk. Panjang: " . ($c['liabilitas_panjang_persen'] ?? '-') . "%, Ekuitas: " . ($c['ekuitas_persen'] ?? '-') . "%\n";
        }

        $this->tambahkanKonteksNarasiSebelumnya($Prompt, $trendData['narasi_trend_commonsize_AI']);

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = TrendCommonsizeAgent::make()->chat(new UserMessage($Prompt));
        $narasi = $response->getMessage()->getContent() ?? 'Tidak ada insight.';
        $narasi = TextCleanerService::bersihkanMarkdown($narasi);

        $analisis->trend()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            ['narasi_trend_commonsize_AI' => $narasi]
        );
    }

    public function prosesTrendArusKas(Analisis $analisis, ?string $userPrompt = null): void
    {
        $trendData = $analisis->getArusKasTrend();
        $periodeData = $trendData['periode_data'];

        $Prompt = "Berikan narasi analisis tren arus kas (Kas Masuk, Kas Keluar, Net Cash Flow) lintas periode berikut: \n";
        $Prompt .= "STATUS DATA: " . count($periodeData) . " periode tersedia dalam scope";
        $Prompt .= $trendData['has_gap']
            ? ", namun ada periode dengan data tidak lengkap — fokuskan narasi hanya pada periode yang datanya tersedia.\n"
            : ", seluruh data lengkap.\n";

        foreach ($periodeData as $titik) {
            $label = $this->labelPeriodeArray($titik['analisis']);
            $kasMasuk = $titik['kas_masuk'];
            $kasKeluar = $titik['kas_keluar'];
            $net = ($kasMasuk !== null && $kasKeluar !== null) ? $kasMasuk - $kasKeluar : null;

            $Prompt .= "--- {$label} ---\n";
            $Prompt .= "Kas Masuk: " . ($kasMasuk !== null ? number_format($kasMasuk, 0, ',', '.') : '-') . "\n";
            $Prompt .= "Kas Keluar: " . ($kasKeluar !== null ? number_format($kasKeluar, 0, ',', '.') : '-') . "\n";
            $Prompt .= "Net Cash Flow: " . ($net !== null ? number_format($net, 0, ',', '.') : '-') . "\n";
        }

        $this->tambahkanKonteksNarasiSebelumnya($Prompt, $trendData['narasi_arus_kas_AI']);

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = TrendArusKasAgent::make()->chat(new UserMessage($Prompt));
        $narasi = $response->getMessage()->getContent() ?? 'Tidak ada insight.';
        $narasi = TextCleanerService::bersihkanMarkdown($narasi);

        $analisis->trend()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            ['narasi_trend_arus_kas_AI' => $narasi]
        );
    }

    public function prosesSummaryAnalisis(Analisis $analisis, ?string $userPrompt = null): void
    {
        $Prompt = "Susun Executive Summary berdasarkan seluruh hasil analisis keuangan yang tersedia.\n";

        $Prompt .= "=== INFORMASI PERUSAHAAN ===\n";
        $Prompt .= "Perusahaan : {$analisis->perusahaan->nama}\n";
        $Prompt .= "Sektor : {$analisis->perusahaan->sektor}\n";
        $Prompt .= "Periode Analisis : {$analisis->periode}\n";

        $Prompt .= "Gunakan seluruh hasil analisis berikut sebagai dasar penyusunan Executive Summary.\n";
        $Prompt .= "Apabila suatu analisis tidak tersedia maka abaikan, jangan membuat asumsi.\n";


         if ($analisis->likuiditas?->narasi_likuiditas_AI) {
            $Prompt .= "=== ANALISIS LIKUIDITAS ===\n";
            $Prompt .= $analisis->likuiditas->narasi_likuiditas_AI . "\n";
        }

        if ($analisis->profitabilitas?->narasi_profitabilitas_AI) {
            $Prompt .= "=== ANALISIS PROFITABILITAS ===\n";
            $Prompt .= $analisis->profitabilitas->narasi_profitabilitas_AI . "\n";
        }

        if ($analisis->solvabilitas?->narasi_solvabilitas_AI) {
            $Prompt .= "=== ANALISIS SOLVABILITAS ===\n";
            $Prompt .= $analisis->solvabilitas->narasi_solvabilitas_AI . "\n";
        }

        if ($analisis->aktivitas?->narasi_aktivitas_AI) {
            $Prompt .= "=== ANALISIS AKTIVITAS ===\n";
            $Prompt .= $analisis->aktivitas->narasi_aktivitas_AI . "\n";
        }

        if ($analisis->dupont?->narasi_dupont_AI) {
            $Prompt .= "=== ANALISIS DUPONT ===\n";
            $Prompt .= $analisis->dupont->narasi_dupont_AI . "\n";
        }

        if ($analisis->commonsize?->narasi_commonsize_AI) {
            $Prompt .= "=== ANALISIS COMMON SIZE ===\n";
            $Prompt .= $analisis->commonsize->narasi_commonsize_AI . "\n";
        }

        if ($analisis->trend) {

            if ($analisis->trend->narasi_trend_akun_utama_AI) {
                $Prompt .= "=== TREND AKUN UTAMA ===\n";
                $Prompt .= $analisis->trend->narasi_trend_akun_utama_AI . "\n";
            }

            if ($analisis->trend->narasi_trend_rasio_AI) {
                $Prompt .= "=== TREND RASIO ===\n";
                $Prompt .= $analisis->trend->narasi_trend_rasio_AI . "\n";
            }

            if ($analisis->trend->narasi_trend_dupont_AI) {
                $Prompt .= "=== TREND DUPONT ===\n";
                $Prompt .= $analisis->trend->narasi_trend_dupont_AI . "\n";
            }

            if ($analisis->trend->narasi_trend_commonsize_AI) {
                $Prompt .= "=== TREND COMMON SIZE ===\n";
                $Prompt .= $analisis->trend->narasi_trend_commonsize_AI . "\n";
            }

            if ($analisis->trend->narasi_trend_arus_kas_AI) {
                $Prompt .= "=== TREND ARUS KAS ===\n";
                $Prompt .= $analisis->trend->narasi_trend_arus_kas_AI . "\n";
            }
        }

        if ($analisis->AI_summary_insight) {
            $Prompt .= "=== EXECUTIVE SUMMARY SEBELUMNYA ===\n";
            $Prompt .= $analisis->AI_summary_insight . "\n";
        }

        if ($userPrompt) {
            $Prompt .= "=== PERMINTAAN PENGGUNA ===\n";
            $Prompt .= $userPrompt . "\n";
        }

        $Prompt .= "Susun kembali Executive Summary berdasarkan seluruh informasi di atas.";

        $response = SummaryAgent::make()->chat(new UserMessage($Prompt));
        $narasi = $response->getMessage()->getContent() ?? 'Tidak ada insight.';
        $narasi = TextCleanerService::bersihkanMarkdown($narasi);

        $analisis->update([
            'AI_summary_insight' => $narasi,
        ]);
    }
}
