<?php

namespace App\Services;

use App\Models\Analisis;
use App\Models\Neraca;
use App\Models\LabaRugi;
use App\Models\Dokumen;              
use App\Models\AnalisisTrendPeriode;
use Illuminate\Validation\ValidationException;
use NeuronAI\Chat\Messages\UserMessage;

use App\Neuron\RAG\ProfitabilityAgent;
use App\Neuron\RAG\LiquidityAgent;
use App\Neuron\RAG\SolvencyAgent;
use App\Neuron\RAG\ActivityAgent;

use App\Neuron\RAG\CommonsizeAgent;
use App\Neuron\RAG\DupontAgent;
use App\Neuron\RAG\TrendAgent;
use App\Models\AnalisisCommonsize;
use App\Models\AnalisisDupont;
use App\Models\AnalisisTrend;

class AnalysisFinancialService
{
    protected FinancialService $financialService;

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

        if (in_array($section, ['profitabilitas', 'aktivitas', 'commonsize', 'dupont', 'trend']) && (!$neraca || !$labaRugi)) {
            throw ValidationException::withMessages([
                'regenerasi' => "Data Neraca dan Laba Rugi harus lengkap untuk menghitung rasio $section."
            ]);
        }
    }

    public function prosesLikuiditas(Analisis $analisis, Neraca $neraca): void
    {
       

        $cr = $this->financialService->currentRatio((float) $neraca->current_assets, (float) $neraca->current_liabilities);
        $qr = $this->financialService->quickRatio((float) $neraca->current_assets, (float) $neraca->persediaan, (float) $neraca->current_liabilities);
        $csr = $this->financialService->cashRatio((float) $neraca->kas_setara_kas, (float) $neraca->current_liabilities);


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

    public function prosesDupont(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        $npm      = $this->financialService->netProfitMargin((float) $labaRugi->laba_bersih, (float) $labaRugi->pendapatan);
        $tato     = $this->financialService->totalAssetTurnover((float) $labaRugi->pendapatan, (float) $neraca->total_assets);
        $leverage = $this->financialService->financialLeverage((float) $neraca->total_assets, (float) $neraca->total_equity);
        $roe      = $npm * $tato * $leverage;

        $Prompt = "Berikan narasi analisis DuPont berdasarkan data berikut: \n";
        $Prompt .= "Net Profit Margin (NPM): " . round($npm * 100, 2) . "%\n";
        $Prompt .= "Total Asset Turnover (TATO): " . round($tato, 2) . " kali\n";
        $Prompt .= "Leverage Multiplier (Total Aset / Ekuitas): " . round($leverage, 2) . " kali\n";
        $Prompt .= "Hasil ROE = NPM x TATO x Leverage: " . round($roe * 100, 2) . "%\n";

        $response = DupontAgent::make()->chat(new UserMessage($Prompt));

        $analisis->dupont()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'net_profit_margin'    => round($npm * 100, 2),
                'total_asset_turnover' => round($tato, 2),
                'leverage_multiplier'  => round($leverage, 2),
                'roe'                  => round($roe * 100, 2),
                'narasi_dupont_AI' => $response->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.',
            ]
        );
    }

    private function resolveDataKeuangan(Analisis $analisis): array
    {
        $dokumen = Dokumen::where('perusahaan_id', $analisis->perusahaan_id)
            ->where('periode_type', $analisis->periode_type)
            ->where('tahun', $analisis->tahun)
            ->where('quarter', $analisis->quarter)
            ->where('bulan', $analisis->bulan)
            ->first();

        if (!$dokumen) {
            return [null, null, null];
        }

        return [$dokumen->neraca, $dokumen->labaRugi, $dokumen->arusKas];
    }

    private function labelPeriode(Analisis $analisis): string
    {
        if ($analisis->periode_type === 'annual') {
            return "Tahunan {$analisis->tahun}";
        }
        if ($analisis->periode_type === 'quarterly') {
            return "Q{$analisis->quarter} {$analisis->tahun}";
        }
        return "Bulan {$analisis->bulan} {$analisis->tahun}";
    }

    public function prosesTrend(Analisis $analisis): void
    {
        // 1. Tentukan scope periode sejenis (SAMA seperti sebelumnya, tidak berubah)
        $query = Analisis::where('perusahaan_id', $analisis->perusahaan_id)
            ->where('periode_type', $analisis->periode_type);

        if ($analisis->periode_type === 'quarterly') {
            $query->where('tahun', $analisis->tahun)
                ->where('quarter', '<=', $analisis->quarter);
        } elseif ($analisis->periode_type === 'annual') {
            $query->where('tahun', '<=', $analisis->tahun);
        } else {
            $query->where('tahun', $analisis->tahun)
                ->where('bulan', '<=', $analisis->bulan);
        }

        $scopeAnalisis = $query->orderBy('tahun')->orderBy('quarter')->orderBy('bulan')
            ->with(['likuiditas', 'profitabilitas', 'solvabilitas', 'aktivitas', 'dupont', 'commonsize'])
            ->get();

        // 2. Resolve data keuangan + kumpulkan titik data akun utama (SAMA seperti sebelumnya)
        $titikData = [];
        foreach ($scopeAnalisis as $itemAnalisis) {
            [$neracaItem, $labaRugiItem, $arusKasItem] = $this->resolveDataKeuangan($itemAnalisis);

            if (!$neracaItem || !$labaRugiItem) {
                continue;
            }

            $netCashFlow = $arusKasItem
                ? ((float) $arusKasItem->kas_masuk - (float) $arusKasItem->kas_keluar)
                : null;

            $titikData[] = [
                'analisis_id'    => $itemAnalisis->id,
                'periode_label'  => $this->labelPeriode($itemAnalisis),
                'pendapatan'     => (float) $labaRugiItem->pendapatan,
                'laba_kotor'     => (float) $labaRugiItem->laba_kotor,
                'laba_bersih'    => (float) $labaRugiItem->laba_bersih,
                'total_assets'   => (float) $neracaItem->total_assets,
                'kas_setara_kas' => (float) $neracaItem->kas_setara_kas,
                'total_equity'   => (float) $neracaItem->total_equity,
                'net_cash_flow'  => $netCashFlow,
            ];
        }

        $isDataIlustratif = count($titikData) < 2;

        // 3a. Prompt Akun Utama (SAMA seperti sebelumnya)
        $promptAkun = "Berikan narasi analisis tren (horizontal) berdasarkan data akun utama berikut: \n";
        if ($isDataIlustratif) {
            $promptAkun .= "STATUS DATA: TIDAK LENGKAP, hanya " . count($titikData) . " periode tersedia. "
                . "Ikuti instruksi 'Catatan Data Ilustratif' — buat data pembanding ilustratif yang konsisten secara matematis, beri label jelas.\n";
        } else {
            $promptAkun .= "STATUS DATA: RIIL, " . count($titikData) . " periode tersedia — jangan pakai label ilustratif.\n";
        }
        foreach ($titikData as $titik) {
            $promptAkun .= "--- {$titik['periode_label']} ---\n";
            $promptAkun .= "Pendapatan: " . number_format($titik['pendapatan'], 0, ',', '.') . "\n";
            $promptAkun .= "Laba Bersih: " . number_format($titik['laba_bersih'], 0, ',', '.') . "\n";
            $promptAkun .= "Total Aset: " . number_format($titik['total_assets'], 0, ',', '.') . "\n";
        }
        $narasiAkun = TrendAgent::make()->chat(new UserMessage($promptAkun))
            ->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.';

        // 3b. Prompt Tren Rasio (BARU) — ambil dari relasi likuiditas/profitabilitas/solvabilitas/aktivitas tiap periode
        $baris = [];
        foreach ($scopeAnalisis as $itemAnalisis) {
            if (!$itemAnalisis->likuiditas && !$itemAnalisis->profitabilitas
                && !$itemAnalisis->solvabilitas && !$itemAnalisis->aktivitas) {
                continue; // periode ini belum punya rasio sama sekali, skip
            }
            $label = $this->labelPeriode($itemAnalisis);
            $l = $itemAnalisis->likuiditas;
            $p = $itemAnalisis->profitabilitas;
            $s = $itemAnalisis->solvabilitas;
            $a = $itemAnalisis->aktivitas;

            $baris[] = "--- {$label} ---\n"
                . "CR: " . ($l->current_ratio ?? '-') . "%, QR: " . ($l->quick_ratio ?? '-') . "%, CSR: " . ($l->cash_ratio ?? '-') . "%\n"
                . "NPM: " . ($p->net_profit_margin ?? '-') . "%, ROA: " . ($p->ROA ?? '-') . "%, ROE: " . ($p->ROE ?? '-') . "%\n"
                . "DER: " . ($s->debt_to_equity ?? '-') . "%, DAR: " . ($s->debt_to_asset ?? '-') . "%\n"
                . "TATO: " . ($a->total_asset_turnover ?? '-') . "x\n";
        }

        if (count($baris) < 2) {
            $narasiRasio = 'Belum cukup periode dengan rasio lengkap untuk menyusun tren rasio yang bermakna.';
        } else {
            $promptRasio = "Berikan narasi analisis tren rasio keuangan (likuiditas, profitabilitas, solvabilitas, aktivitas) lintas periode berikut: \n"
                . implode("\n", $baris);
            $narasiRasio = TrendAgent::make()->chat(new UserMessage($promptRasio))
                ->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.';
        }

        // 3c. Prompt Dupont (BARU) — gaya sama seperti narasi di AnalisisDupontCard, tapi lintas periode
        $barisDupont = [];
        foreach ($scopeAnalisis as $itemAnalisis) {
            if (!$itemAnalisis->dupont) continue;
            $d = $itemAnalisis->dupont;
            $barisDupont[] = "--- {$this->labelPeriode($itemAnalisis)} ---\n"
                . "NPM: {$d->net_profit_margin}%, TATO: {$d->total_asset_turnover}x, "
                . "Leverage: {$d->leverage_multiplier}x, ROE: {$d->roe}%\n";
        }

        if (empty($barisDupont)) {
            $narasiDupont = 'Belum ada data DuPont yang tersedia untuk periode-periode dalam scope ini.';
        } else {
            $promptDupont = "Berikan narasi analisis DuPont berdasarkan data per periode berikut: \n" . implode("\n", $barisDupont);
            $narasiDupont = DupontAgent::make()->chat(new UserMessage($promptDupont))
                ->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.';
        }

        // 3d. Prompt Common-size (BARU) — gaya sama seperti narasi di AnalisisCommonsizeCard, tapi lintas periode
        $barisCommonsize = [];
        foreach ($scopeAnalisis as $itemAnalisis) {
            if (!$itemAnalisis->commonsize) continue;
            $c = $itemAnalisis->commonsize;
            $barisCommonsize[] = "--- {$this->labelPeriode($itemAnalisis)} ---\n"
                . "HPP: {$c->hpp_persen}%, Laba Kotor: {$c->laba_kotor_persen}%, "
                . "Beban Lain & Pajak: {$c->beban_lain_pajak_persen}%, Laba Bersih: {$c->laba_bersih_persen}%\n"
                . "Aset Lancar: {$c->aset_lancar_persen}%, Aset Tetap: {$c->aset_tetap_persen}%, "
                . "Liabilitas Lancar: {$c->liabilitas_lancar_persen}%, Liabilitas Jk. Panjang: {$c->liabilitas_panjang_persen}%, "
                . "Ekuitas: {$c->ekuitas_persen}%\n";
        }

        if (empty($barisCommonsize)) {
            $narasiCommonsize = 'Belum ada data Common-size yang tersedia untuk periode-periode dalam scope ini.';
        } else {
            $promptCommonsize = "Berikan narasi analisis common-size berdasarkan data per periode berikut: \n" . implode("\n", $barisCommonsize);
            $narasiCommonsize = CommonsizeAgent::make()->chat(new UserMessage($promptCommonsize))
                ->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.';
        }

        // 4. Simpan induk analisis_trend — 4 narasi sekaligus
        $trend = $analisis->trend()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'is_data_ilustratif'    => $isDataIlustratif,
                'narasi_trend_AI'       => $narasiAkun,
                'narasi_rasio_AI'       => $narasiRasio,
                'narasi_dupont_AI'      => $narasiDupont,
                'narasi_commonsize_AI'  => $narasiCommonsize,
            ]
        );

        // 5. Ganti seluruh titik data lama (SAMA seperti sebelumnya, tidak berubah)
        $trend->periodeData()->delete();

        $sebelumnya = null;
        foreach (array_values($titikData) as $urutan => $titik) {
            $growth = function (string $field) use ($titik, $sebelumnya) {
                if (!$sebelumnya || $titik[$field] === null || $sebelumnya[$field] === null || (float) $sebelumnya[$field] <= 0) {
                    return null;
                }
                return round((($titik[$field] - $sebelumnya[$field]) / $sebelumnya[$field]) * 100, 6);
            };

            AnalisisTrendPeriode::create([
                'analisis_trend_id'     => $trend->id,
                'analisis_id'           => $titik['analisis_id'],
                'urutan'                => $urutan + 1,
                'pendapatan'            => $titik['pendapatan'],
                'laba_kotor'            => $titik['laba_kotor'],
                'laba_bersih'           => $titik['laba_bersih'],
                'total_assets'          => $titik['total_assets'],
                'kas_setara_kas'        => $titik['kas_setara_kas'],
                'total_equity'          => $titik['total_equity'],
                'net_cash_flow'         => $titik['net_cash_flow'],
                'growth_pendapatan'     => $growth('pendapatan'),
                'growth_laba_kotor'     => $growth('laba_kotor'),
                'growth_laba_bersih'    => $growth('laba_bersih'),
                'growth_total_assets'   => $growth('total_assets'),
                'growth_kas_setara_kas' => $growth('kas_setara_kas'),
                'growth_total_equity'   => $growth('total_equity'),
                'growth_net_cash_flow'  => $growth('net_cash_flow'),
            ]);

            $sebelumnya = $titik;
        }
    }


    public function prosesCommonsize(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        $pendapatan = (float) $labaRugi->pendapatan;
        $labaKotor  = (float) $labaRugi->laba_kotor;
        $labaBersih = (float) $labaRugi->laba_bersih;

        $hpp             = $pendapatan - $labaKotor;
        $bebanLainPajak  = $labaKotor - $labaBersih; // gabungan OpEx + Bunga + Pajak, tidak bisa dipecah dari data yang ada

        $hppPersen            = $this->financialService->commonSizePercentage($hpp, $pendapatan);
        $labaKotorPersen      = $this->financialService->commonSizePercentage($labaKotor, $pendapatan);
        $bebanLainPajakPersen = $this->financialService->commonSizePercentage($bebanLainPajak, $pendapatan);
        $labaBersihPersen     = $this->financialService->commonSizePercentage($labaBersih, $pendapatan);

        $asetTetap         = (float) $neraca->total_assets - (float) $neraca->current_assets;
        $liabilitasPanjang = (float) $neraca->total_liabilities - (float) $neraca->current_liabilities;

        $asetLancarPersen        = $this->financialService->commonSizePercentage((float) $neraca->current_assets, (float) $neraca->total_assets);
        $asetTetapPersen         = $this->financialService->commonSizePercentage($asetTetap, (float) $neraca->total_assets);
        $liabilitasLancarPersen  = $this->financialService->commonSizePercentage((float) $neraca->current_liabilities, (float) $neraca->total_assets);
        $liabilitasPanjangPersen = $this->financialService->commonSizePercentage($liabilitasPanjang, (float) $neraca->total_assets);
        $ekuitasPersen           = $this->financialService->commonSizePercentage((float) $neraca->total_equity, (float) $neraca->total_assets);

        $Prompt = "Berikan narasi analisis common-size berdasarkan data berikut: \n";
        $Prompt .= "--- Common-Size Income Statement (basis Pendapatan = 100%) ---\n";
        $Prompt .= "Pendapatan Usaha: 100%\n";
        $Prompt .= "HPP: " . round($hppPersen, 2) . "%\n";
        $Prompt .= "Laba Kotor: " . round($labaKotorPersen, 2) . "%\n";
        $Prompt .= "Beban Lain-lain & Pajak (gabungan OpEx+Bunga+Pajak): " . round($bebanLainPajakPersen, 2) . "%\n";
        $Prompt .= "Laba Bersih: " . round($labaBersihPersen, 2) . "%\n";
        $Prompt .= "PENTING: sumber data hanya mencatat Pendapatan, Laba Kotor, dan Laba Bersih. OpEx, EBIT, dan Beban Bunga TIDAK tercatat terpisah, sehingga digabung jadi satu pos 'Beban Lain-lain & Pajak'. JANGAN memecah/mengarang angka OpEx, EBIT, atau Bunga secara individual — bahas pos gabungan ini apa adanya.\n";
        $Prompt .= "--- Common-Size Balance Sheet (basis Total Aset = 100%) ---\n";
        $Prompt .= "Aset Lancar: " . round($asetLancarPersen, 2) . "%\n";
        $Prompt .= "Aset Tetap: " . round($asetTetapPersen, 2) . "%\n";
        $Prompt .= "Liabilitas Lancar: " . round($liabilitasLancarPersen, 2) . "%\n";
        $Prompt .= "Liabilitas Jangka Panjang: " . round($liabilitasPanjangPersen, 2) . "%\n";
        $Prompt .= "Ekuitas: " . round($ekuitasPersen, 2) . "%\n";

        $response = CommonsizeAgent::make()->chat(new UserMessage($Prompt));

        $analisis->commonsize()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'hpp_persen'               => round($hppPersen, 2),
                'laba_kotor_persen'        => round($labaKotorPersen, 2),
                'beban_lain_pajak_persen'  => round($bebanLainPajakPersen, 2),
                'laba_bersih_persen'       => round($labaBersihPersen, 2),
                'aset_lancar_persen'        => round($asetLancarPersen, 2),
                'aset_tetap_persen'         => round($asetTetapPersen, 2),
                'liabilitas_lancar_persen'  => round($liabilitasLancarPersen, 2),
                'liabilitas_panjang_persen' => round($liabilitasPanjangPersen, 2),
                'ekuitas_persen'            => round($ekuitasPersen, 2),
                'narasi_commonsize_AI' => $response->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.',
            ]
        );
    }

    public function updateStatusJikaLengkap(Analisis $analisis): void
    {
        $lengkap = $analisis->likuiditas()->exists()
                && $analisis->profitabilitas()->exists()
                && $analisis->solvabilitas()->exists()
                && $analisis->aktivitas()->exists()
                && $analisis->commonsize()->exists()
                && $analisis->dupont()->exists()
                && $analisis->trend()->exists();

        if ($lengkap && $analisis->status === 'belum dianalisis') {
            $analisis->update(['status' => 'sudah dianalisis']);
        }
    }
}
