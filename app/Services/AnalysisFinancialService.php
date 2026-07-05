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
use App\Neuron\RAG\LiquidityAnalystAgent;
use App\Neuron\RAG\SolvencyAgent;
use App\Neuron\RAG\ActivityAgent;

use App\Neuron\RAG\CommonsizeAgent;
use App\Neuron\RAG\DupontAgent;
use App\Neuron\RAG\TrendAgent;
use App\Models\AnalisisCommonsize;
use App\Models\AnalisisDupont;
use App\Models\AnalisisTrend;


// ini perlu di refactor
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

    public function hitungSemuaRasio(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        // Panggil semua fungsi perhitungan yang sudah di-refactor
        $this->hitungLikuiditas($analisis, $neraca);
        $this->hitungProfitabilitas($analisis, $neraca, $labaRugi);
        $this->hitungSolvabilitas($analisis, $neraca);
        $this->hitungAktivitas($analisis, $neraca, $labaRugi);
        $this->hitungDupont($analisis, $neraca, $labaRugi);
        $this->hitungCommonsize($analisis, $neraca, $labaRugi);

        // Update status jika rasio berhasil dihitung
        if (in_array($analisis->status, ['belum dianalisis', 'Terjadi Perubahan Data!'])) {
            $analisis->update(['status' => 'rasio tersedia']);
        }
    }

    public function prosesLikuiditas(Analisis $analisis, ?string $userPrompt = null): void
    {
        $data = $analisis->likuiditas;

        $Prompt  = "Berikan narasi analisis likuiditas berdasarkan data berikut: \n";
        $Prompt .= "Current Ratio (CR): " . $data->current_ratio . "x\n";
        $Prompt .= "Quick Ratio (QR): " . $data->quick_ratio . "x\n";
        $Prompt .= "Cash Ratio (CSR): " . $data->cash_ratio . "x\n";

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = LiquidityAnalystAgent::make()->chat(new UserMessage($Prompt));
        $data->update(['narasi_likuiditas_AI' => $response->getMessage()->getContent() ?? 'Tidak ada insight.']);
    }

    public function prosesProfitabilitas(Analisis $analisis, ?string $userPrompt = null): void
    {
        $data = $analisis->profitabilitas;
        $Prompt  = "Berikan narasi analisis profitabilitas berdasarkan data berikut: \n";
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
        $Prompt  = "Berikan narasi analisis solvabilitas berdasarkan data berikut: \n";
        $Prompt .= "Debt to Equity Ratio (DER): " . $data->debt_to_equity . "%\n";
        $Prompt .= "Debt to Asset Ratio (DAR): " . $data->debt_to_asset . "%\n";

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

        $data->update([
            'narasi_dupont_AI' => $response->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.'
        ]);
    }

    public function prosesCommonsize(Analisis $analisis, ?string $userPrompt = null): void
    {
        $data = $analisis->commonsize;

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
        $Prompt .= "Aset Lancar: " . $data->aset_lancar_persen. "%\n";
        $Prompt .= "Aset Tetap: " . $data->aset_tetap_persen . "%\n";
        $Prompt .= "Liabilitas Lancar: " . $data->liabilitas_lancar_persen . "%\n";
        $Prompt .= "Liabilitas Jangka Panjang: " . $data->liabilitas_panjang_persen . "%\n";
        $Prompt .= "Ekuitas: " . $data->ekuitas_persen . "%\n";

        if ($userPrompt) {
            $Prompt .= "\nInstruksi Tambahan dari Pengguna: " . $userPrompt . "\n";
        }

        $response = CommonsizeAgent::make()->chat(new UserMessage($Prompt));

        $data->update([
            'narasi_commonsize_AI' => $response->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.'
        ]);
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
                'kas_setara_kas' => (float) $neracaItem->cash_equivalent,
                'total_equity'   => (float) $neracaItem->total_equity,
                'net_cash_flow'  => $netCashFlow,
            ];
        }

        $isDataIlustratif = count($titikData) < 2;

        $promptAkun  = "Berikan narasi analisis tren (horizontal) berdasarkan data akun utama berikut: \n";
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

        $baris = [];
        foreach ($scopeAnalisis as $itemAnalisis) {
            if (!$itemAnalisis->likuiditas && !$itemAnalisis->profitabilitas
                && !$itemAnalisis->solvabilitas && !$itemAnalisis->aktivitas) {
                continue;
            }
            $label = $this->labelPeriode($itemAnalisis);
            $l = $itemAnalisis->likuiditas;
            $p = $itemAnalisis->profitabilitas;
            $s = $itemAnalisis->solvabilitas;
            $a = $itemAnalisis->aktivitas;

            $baris[] = "--- {$label} ---\n"
                . "CR: " . ($l ? $l->current_ratio : '-') . "x, "
                . "QR: " . ($l ? $l->quick_ratio : '-') . "x, "
                . "CSR: " . ($l ? $l->cash_ratio : '-') . "x\n"
                . "NPM: " . ($p ? $p->net_profit_margin : '-') . "%, "
                . "ROA: " . ($p ? $p->ROA : '-') . "%, "
                . "ROE: " . ($p ? $p->ROE : '-') . "%\n"
                . "DER: " . ($s ? $s->debt_to_equity : '-') . "%, "
                . "DAR: " . ($s ? $s->debt_to_asset : '-') . "%\n"
                . "TATO: " . ($a ? $a->total_asset_turnover : '-') . "x\n";
        }

        if (count($baris) < 2) {
            $narasiRasio = 'Belum cukup periode dengan rasio lengkap untuk menyusun tren rasio yang bermakna.';
        } else {
            $promptRasio = "Berikan narasi analisis tren rasio keuangan (likuiditas, profitabilitas, solvabilitas, aktivitas) lintas periode berikut: \n"
                . implode("\n", $baris);
            $narasiRasio = TrendAgent::make()->chat(new UserMessage($promptRasio))
                ->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.';
        }

        $barisDupont = [];
        foreach ($scopeAnalisis as $itemAnalisis) {
            if (!$itemAnalisis->dupont) continue;
            $dupont = $itemAnalisis->dupont;
            $barisDupont[] = "--- {$this->labelPeriode($itemAnalisis)} ---\n"
                . "NPM: " . $dupont->net_profit_margin . "%, "
                . "TATO: " . $dupont->total_asset_turnover . "x, "
                . "Leverage: " . $dupont->leverage_multiplier . "x, "
                . "ROE: " . $dupont->roe . "%\n";
        }

        if (empty($barisDupont)) {
            $narasiDupont = 'Belum ada data DuPont yang tersedia untuk periode-periode dalam scope ini.';
        } else {
            $promptDupont = "Berikan narasi analisis DuPont berdasarkan data per periode berikut: \n"
                . implode("\n", $barisDupont);
            $narasiDupont = DupontAgent::make()->chat(new UserMessage($promptDupont))
                ->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.';
        }

        $barisCommonsize = [];
        foreach ($scopeAnalisis as $itemAnalisis) {
            if (!$itemAnalisis->commonsize) continue;
            $commonsize = $itemAnalisis->commonsize;
            $barisCommonsize[] = "--- {$this->labelPeriode($itemAnalisis)} ---\n"
                . "HPP: " . $commonsize->hpp_persen . "%, "
                . "Laba Kotor: " . $commonsize->laba_kotor_persen . "%, "
                . "Beban Lain & Pajak: " . $commonsize->beban_lain_pajak_persen . "%, "
                . "Laba Bersih: " . $commonsize->laba_bersih_persen . "%\n"
                . "Aset Lancar: " . $commonsize->aset_lancar_persen . "%, "
                . "Aset Tetap: " . $commonsize->aset_tetap_persen . "%, "
                . "Liabilitas Lancar: " . $commonsize->liabilitas_lancar_persen . "%, "
                . "Liabilitas Jk. Panjang: " . $commonsize->liabilitas_panjang_persen . "%, "
                . "Ekuitas: " . $commonsize->ekuitas_persen . "%\n";
        }

        if (empty($barisCommonsize)) {
            $narasiCommonsize = 'Belum ada data Common-size yang tersedia untuk periode-periode dalam scope ini.';
        } else {
            $promptCommonsize = "Berikan narasi analisis common-size berdasarkan data per periode berikut: \n"
                . implode("\n", $barisCommonsize);
            $narasiCommonsize = CommonsizeAgent::make()->chat(new UserMessage($promptCommonsize))
                ->getMessage()->getContent() ?? 'Tidak ada insight yang dihasilkan oleh AI.';
        }

        $trend = $analisis->trend()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'is_data_ilustratif'   => $isDataIlustratif,
                'narasi_trend_AI'      => $narasiAkun,
                'narasi_rasio_AI'      => $narasiRasio,
                'narasi_dupont_AI'     => $narasiDupont,
                'narasi_commonsize_AI' => $narasiCommonsize,
            ]
        );

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

    public function hitungLikuiditas(Analisis $analisis, Neraca $neraca): void
    {
        $cr  = $this->financialService->currentRatio((float) $neraca->current_assets, (float) $neraca->current_liabilities);
        $qr  = $this->financialService->quickRatio((float) $neraca->current_assets, (float) $neraca->inventory, (float) $neraca->current_liabilities);
        $csr = $this->financialService->cashRatio((float) $neraca->cash_equivalent, (float) $neraca->current_liabilities);

        $analisis->likuiditas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'current_ratio' => round($cr, 2),
                'quick_ratio'   => round($qr, 2),
                'cash_ratio'    => round($csr, 2),
            ]
        );
    }

    public function hitungProfitabilitas(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        $npm = $this->financialService->netProfitMargin((float) $labaRugi->laba_bersih, (float) $labaRugi->pendapatan);
        $roa = $this->financialService->returnOnAssets((float) $labaRugi->laba_bersih, (float) $neraca->total_assets);
        $roe = $this->financialService->returnOnEquity((float) $labaRugi->laba_bersih, (float) $neraca->total_equity);

        $analisis->profitabilitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'net_profit_margin' => round($npm * 100, 2),
                'ROA'               => round($roa * 100, 2),
                'ROE'               => round($roe * 100, 2),
            ]
        );
    }

    public function hitungSolvabilitas(Analisis $analisis, Neraca $neraca): void
    {
        $dte = $this->financialService->debtToEquity((float) $neraca->total_liabilities, (float) $neraca->total_equity);
        $dta = $this->financialService->debtToAsset((float) $neraca->total_liabilities, (float) $neraca->total_assets);

        $analisis->solvabilitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'debt_to_equity' => round($dte * 100, 2),
                'debt_to_asset'  => round($dta * 100, 2),
            ]
        );
    }

    public function hitungAktivitas(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        $tato = $this->financialService->totalAssetTurnover((float) $labaRugi->pendapatan, (float) $neraca->total_assets);

        $analisis->aktivitas()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            ['total_asset_turnover' => round($tato, 2)]
        );
    }

    public function hitungDupont(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        $npm      = $this->financialService->netProfitMargin((float) $labaRugi->laba_bersih, (float) $labaRugi->pendapatan);
        $tato     = $this->financialService->totalAssetTurnover((float) $labaRugi->pendapatan, (float) $neraca->total_assets);
        $leverage = $this->financialService->financialLeverage((float) $neraca->total_assets, (float) $neraca->total_equity);
        $roe      = $npm * $tato * $leverage;

        $analisis->dupont()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'net_profit_margin'    => round($npm, 2),
                'total_asset_turnover' => round($tato, 2),
                'leverage_multiplier'  => round($leverage, 2),
                'roe'                  => round($roe, 2),
            ]
        );
    }

    public function hitungCommonsize(Analisis $analisis, Neraca $neraca, LabaRugi $labaRugi): void
    {
        $pendapatan = (float) $labaRugi->pendapatan;
        $labaKotor  = (float) $labaRugi->laba_kotor;
        $labaBersih = (float) $labaRugi->laba_bersih;

        $hpp            = $pendapatan - $labaKotor;
        $bebanLainPajak = $labaKotor - $labaBersih;

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

        $analisis->commonsize()->updateOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'hpp_persen'               => round($hppPersen, 2),
                'laba_kotor_persen'        => round($labaKotorPersen, 2),
                'beban_lain_pajak_persen'  => round($bebanLainPajakPersen, 2),
                'laba_bersih_persen'       => round($labaBersihPersen, 2),
                'aset_lancar_persen'       => round($asetLancarPersen, 2),
                'aset_tetap_persen'        => round($asetTetapPersen, 2),
                'liabilitas_lancar_persen' => round($liabilitasLancarPersen, 2),
                'liabilitas_panjang_persen'=> round($liabilitasPanjangPersen, 2),
                'ekuitas_persen'           => round($ekuitasPersen, 2),
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

        if ($lengkap) {
            $analisis->update(['status' => 'sudah dianalisis']);
        }
    }
}
