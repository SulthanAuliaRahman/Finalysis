<?php

namespace App\Services;

use App\Models\Analisis;
use App\Models\Dokumen;
use App\Models\Neraca;
use App\Models\LabaRugi;
use App\Models\Perusahaan;
use App\Models\ChartOfAccount;
use Illuminate\Validation\ValidationException;
use NeuronAI\Chat\Messages\UserMessage;

use App\Neuron\Agent\ProfitabilityAgent;
use App\Neuron\Agent\LiquidityAnalystAgent;
use App\Neuron\Agent\SolvencyAgent;
use App\Neuron\Agent\ActivityAgent;
use App\Neuron\Agent\CommonsizeAgent;
use App\Neuron\Agent\DupontAgent;
use App\Neuron\Agent\TrendAkunUtamaAgent;
use App\Neuron\Agent\TrendRasioAgent;
use App\Neuron\Agent\TrendDupontAgent;
use App\Neuron\Agent\TrendCommonsizeAgent;
use App\Neuron\Agent\SummaryAgent;

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
        $neracaSebelumnya = $this->cariNeracaSebelumnya($analisis);

        $this->calculateFinancialService->hitungSemuaRasio(
            $analisis,
            $neraca,
            $labaRugi,
            $neracaSebelumnya
        );
    }

    // =====================================================================
    // PENCARIAN PERIODE SEBELUMNYA
    // =====================================================================

    // PENTING: tabel `analisis` TIDAK punya perusahaan_id/periode_type/tahun/quarter
    // langsung — semua itu ada di tabel `dokumen` (analisis.dokumen_id -> dokumen).
    // Jadi pencarian periode sebelumnya dilakukan lewat Dokumen, bukan lewat Analisis.
    private function cariNeracaSebelumnya(Analisis $analisis): ?Neraca
    {
        $dokumen = $analisis->dokumen;

        if (!$dokumen) {
            return null;
        }

        $query = Dokumen::query()
            ->where('perusahaan_id', $dokumen->perusahaan_id)
            ->where('id', '!=', $dokumen->id)
            ->where('periode_type', $dokumen->periode_type);

        if ($dokumen->periode_type === 'quarterly') {
            $query->where(function ($q) use ($dokumen) {
                $q->where('tahun', '<', $dokumen->tahun)
                  ->orWhere(function ($q2) use ($dokumen) {
                      $q2->where('tahun', $dokumen->tahun)
                         ->where('quarter', '<', $dokumen->quarter);
                  });
            })->orderByDesc('tahun')->orderByDesc('quarter');
        } elseif ($dokumen->periode_type === 'monthly') {
            $query->where(function ($q) use ($dokumen) {
                $q->where('tahun', '<', $dokumen->tahun)
                  ->orWhere(function ($q2) use ($dokumen) {
                      $q2->where('tahun', $dokumen->tahun)
                         ->where('bulan', '<', $dokumen->bulan);
                  });
            })->orderByDesc('tahun')->orderByDesc('bulan');
        } else {
            // annual
            $query->where('tahun', '<', $dokumen->tahun)->orderByDesc('tahun');
        }

        $dokumenSebelumnya = $query->first();

        return $dokumenSebelumnya?->neraca;
    }

    // =====================================================================
    // HELPER — KONTEN PROMPT
    // =====================================================================

    // Blok info perusahaan: Nama, Sektor, Deskripsi. Dipakai di SEMUA prompt
    // (per-section maupun trend) supaya AI selalu punya konteks perusahaan.
    private function blokInfoPerusahaan(Perusahaan $perusahaan): string
    {
        $blok  = "=== INFORMASI PERUSAHAAN ===\n";
        $blok .= "Nama Perusahaan: {$perusahaan->nama}\n";
        $blok .= "Sektor: {$perusahaan->sektor}\n";
        $blok .= "Deskripsi: {$perusahaan->deskripsi}\n";

        return $blok;
    }

    // Blok laporan keuangan MENTAH (seluruh field Neraca + LabaRugi, bukan cuma
    // yang relevan ke section tertentu) — supaya AI punya angka asal di balik
    // rasio yang dihitung, mengurangi risiko narasi yang "lepas konteks".
    private function blokLaporanKeuanganMentah(?Neraca $neraca, ?LabaRugi $labaRugi): string
    {
        $blok  = "=== LAPORAN KEUANGAN (DATA MENTAH) ===\n";
        $blok .= "--- Neraca / Laporan Posisi Keuangan ---\n";
        $blok .= "Total Kas & Setara Kas: " . number_format((float) ($neraca->total_kas_setara_kas ?? 0), 0, ',', '.') . "\n";
        $blok .= "Total Aset Lancar: " . number_format((float) ($neraca->total_asset_lancar ?? 0), 0, ',', '.') . "\n";
        $blok .= "Total Aset Tetap: " . number_format((float) ($neraca->total_asset_tetap ?? 0), 0, ',', '.') . "\n";
        $blok .= "Total Aset: " . number_format((float) ($neraca->total_asset ?? 0), 0, ',', '.') . "\n";
        $blok .= "Total Liabilitas Jangka Pendek: " . number_format((float) ($neraca->total_liabilities_pendek ?? 0), 0, ',', '.') . "\n";
        $blok .= "Total Liabilitas Jangka Panjang: " . number_format((float) ($neraca->total_liabilities_panjang ?? 0), 0, ',', '.') . "\n";
        $blok .= "Total Liabilitas: " . number_format((float) ($neraca->total_liabilities ?? 0), 0, ',', '.') . "\n";
        $blok .= "Total Ekuitas: " . number_format((float) ($neraca->total_equitas ?? 0), 0, ',', '.') . "\n";
        $blok .= "--- Laporan Laba Rugi ---\n";
        $blok .= "Total Pendapatan: " . number_format((float) ($labaRugi->total_pendapatan ?? 0), 0, ',', '.') . "\n";
        $blok .= "Total Beban: " . number_format((float) ($labaRugi->total_beban ?? 0), 0, ',', '.') . "\n";
        $blok .= "Total Biaya Pajak: " . number_format((float) ($labaRugi->total_biaya_pajak ?? 0), 0, ',', '.') . "\n";
        $blok .= "Laba Bersih Sebelum Pajak: " . number_format((float) ($labaRugi->laba_bersih_sebelum_pajak ?? 0), 0, ',', '.') . "\n";
        $blok .= "Laba Bersih Sesudah Pajak: " . number_format((float) ($labaRugi->laba_bersih_sesudah_pajak ?? 0), 0, ',', '.') . "\n";

        return $blok;
    }

    // Gabungan info perusahaan + laporan keuangan mentah. Dipakai di awal
    // SEMUA prompt per-section (likuiditas, profitabilitas, solvabilitas,
    // aktivitas, dupont, commonsize).

    // Rincian akun INDIVIDUAL (bukan total per kelompok) untuk sub_kelompok_akun
    // tertentu — dipakai di section yang butuh AI menyebut akun spesifik sebagai
    // bukti pendukung narasi, bukan cuma angka agregat dari tabel neraca/laba_rugi.
    private function blokDetailAkun(Dokumen $dokumen, array $subKelompokAkun, string $judul): string
    {
        $akun = ChartOfAccount::query()
            ->where('dokumen_id', $dokumen->id)
            ->whereIn('sub_kelompok_akun', $subKelompokAkun)
            ->orderByDesc('nilai_akun')
            ->get();

        if ($akun->isEmpty()) {
            return '';
        }

        $blok = "=== {$judul} ===\n";
        foreach ($akun as $a) {
            $blok .= "- {$a->nama_akun}: " . number_format((float) $a->nilai_akun, 0, ',', '.') . "\n";
        }

        return $blok;
    }

    private function konteksDasarPrompt(Dokumen $dokumen): string
    {
        return $this->blokInfoPerusahaan($dokumen->perusahaan)
            . $this->blokLaporanKeuanganMentah($dokumen->neraca, $dokumen->labaRugi);
    }

    // Label periode untuk satu Dokumen (dipakai di prosesSummaryAnalisis, karena
    // Analisis tidak punya kolom periode sendiri — datanya ada di Dokumen).
    private function labelPeriodeDokumen(Dokumen $dokumen): string
    {
        if ($dokumen->periode_type === 'annual') {
            return "Tahunan {$dokumen->tahun}";
        }
        if ($dokumen->periode_type === 'quarterly') {
            return "Q{$dokumen->quarter} {$dokumen->tahun}";
        }
        return "Bulan {$dokumen->bulan} {$dokumen->tahun}";
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

    // GANTI method prosesLikuiditas() yang lama dengan versi ini.
// Perubahan: tambah $aktivitas dan blok data WCT sebagai konteks pendukung,
// supaya klaim "idle assets" di LiquidityAnalystAgent bisa dikonfirmasi
// silang dengan angka WCT yang sebenarnya (bukan asumsi tanpa data).

    public function prosesLikuiditas(Analisis $analisis, ?string $userPrompt = null): void
    {
        $data = $analisis->likuiditas;
        $dokumen = $analisis->dokumen;
        $aktivitas = $analisis->aktivitas;

        $Prompt  = $this->konteksDasarPrompt($dokumen);
        $Prompt .= "=== DATA RASIO LIKUIDITAS ===\n";
        $Prompt .= "Current Ratio (CR): " . $data->current_ratio . "x\n";
        $Prompt .= "Cash Ratio (CSR): " . $data->cash_ratio . "x\n";

        if ($aktivitas) {
            $Prompt .= "=== DATA RASIO AKTIVITAS (konteks pendukung, bukan topik utama) ===\n";
            $Prompt .= "Working Capital Turnover (WCT): " . $aktivitas->working_capital_turnover . "x\n";
        }

        $Prompt .= "\nBerikan narasi analisis likuiditas berdasarkan data di atas. ";
        $Prompt .= "Gunakan data WCT HANYA sebagai konfirmasi silang jika Current Ratio relatif tinggi ";
        $Prompt .= "(CR tinggi + WCT rendah = indikasi modal kerja menganggur/idle assets; CR tinggi + WCT wajar = modal kerja tetap produktif). ";
        $Prompt .= "Jangan menjadikan aktivitas sebagai topik utama narasi.\n";

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
        $dokumen = $analisis->dokumen;
        $aktivitas = $analisis->aktivitas;
        $solvabilitas = $analisis->solvabilitas;

        $Prompt  = $this->konteksDasarPrompt($dokumen);
        $Prompt .= "=== DATA RASIO PROFITABILITAS ===\n";
        $Prompt .= "Net Profit Margin (NPM): " . $data->net_profit_margin . "%\n";
        $Prompt .= "Return on Assets (ROA): " . $data->ROA . "%\n";
        $Prompt .= "Return on Equity (ROE): " . $data->ROE . "%\n";

        if ($aktivitas) {
            $Prompt .= "=== DATA RASIO AKTIVITAS (konteks pendukung, bukan topik utama) ===\n";
            $Prompt .= "Total Asset Turnover (TATO): " . $aktivitas->total_asset_turnover . "x\n";
        }

        if ($solvabilitas) {
            $Prompt .= "=== DATA RASIO SOLVABILITAS (konteks pendukung, bukan topik utama) ===\n";
            $Prompt .= "Financial Leverage: " . $solvabilitas->leverage_multiplier . "x\n";
        }

        $Prompt .= "\nBerikan narasi analisis profitabilitas berdasarkan data di atas. ";
        $Prompt .= "Gunakan data TATO dan Financial Leverage HANYA sebagai konfirmasi silang saat menjelaskan ROA/ROE ";
        $Prompt .= "(mis. ROE jauh lebih tinggi dari ROA mengindikasikan peran leverage; ROA rendah bisa ditelusuri dari TATO rendah atau NPM yang tipis). ";
        $Prompt .= "Jangan menjadikan aktivitas/solvabilitas sebagai topik utama narasi.\n";

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
        $dokumen = $analisis->dokumen;

        $Prompt  = $this->konteksDasarPrompt($dokumen);
        $Prompt .= "=== DATA RASIO SOLVABILITAS ===\n";
        $Prompt .= "Debt to Equity Ratio (DER): " . $data->debt_to_equity . "x\n";
        $Prompt .= "Debt to Asset Ratio (DAR): " . $data->debt_to_asset . "x\n";
        $Prompt .= "Financial Leverage: " . $data->leverage_multiplier . "x\n";

        $Prompt .= $this->blokDetailAkun($dokumen, ['liabilitas_jangka_pendek'], 'RINCIAN AKUN LIABILITAS JANGKA PENDEK');
        $Prompt .= $this->blokDetailAkun($dokumen, ['liabilitas_jangka_panjang'], 'RINCIAN AKUN LIABILITAS JANGKA PANJANG');

        $Prompt .= "\nBerikan narasi analisis solvabilitas berdasarkan data di atas. ";
        $Prompt .= "Manfaatkan rincian akun individual di atas (jika tersedia) untuk menyebut akun spesifik mana yang paling memengaruhi DAR/DER, bukan cuma menyebut total agregatnya.\n";

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
        $dokumen = $analisis->dokumen;
        $likuiditas = $analisis->likuiditas;

        $Prompt  = $this->konteksDasarPrompt($dokumen);
        $Prompt .= "=== DATA RASIO AKTIVITAS ===\n";
        $Prompt .= "Total Asset Turnover (TATO): " . $data->total_asset_turnover . "x\n";
        $Prompt .= "Working Capital Turnover (WCT): " . $data->working_capital_turnover . "x\n";
        $Prompt .= "Fixed Asset Turnover (FAT): " . $data->fixed_asset_turnover . "x\n";

        $Prompt .= $this->blokDetailAkun($dokumen, ['aset_tetap'], 'RINCIAN AKUN ASET TETAP (relevan untuk FAT)');
        $Prompt .= $this->blokDetailAkun($dokumen, ['kas_setara_kas', 'aset_lancar_selain_kas'], 'RINCIAN AKUN ASET LANCAR (relevan untuk WCT)');
        $Prompt .= $this->blokDetailAkun($dokumen, ['liabilitas_jangka_pendek'], 'RINCIAN AKUN LIABILITAS JANGKA PENDEK (relevan untuk WCT)');

        if ($likuiditas) {
            $Prompt .= "=== DATA RASIO LIKUIDITAS (konteks pendukung, bukan topik utama) ===\n";
            $Prompt .= "Current Ratio (CR): " . $likuiditas->current_ratio . "x\n";
            $Prompt .= "Cash Ratio (CSR): " . $likuiditas->cash_ratio . "x\n";
        }

        $Prompt .= "\nBerikan narasi analisis aktivitas operasional berdasarkan data di atas. ";
        $Prompt .= "Manfaatkan rincian akun individual di atas (jika tersedia) untuk menyebut akun spesifik mana yang paling memengaruhi FAT atau WCT, bukan cuma menyebut total agregatnya. ";
        $Prompt .= "Gunakan data likuiditas HANYA sebagai konfirmasi silang jika ada pola yang relevan ";
        $Prompt .= "(contoh: WCT rendah namun Current Ratio tinggi bisa mengindikasikan modal kerja menumpuk idle, bukan sekadar kondisi likuid). ";
        $Prompt .= "Jangan menjadikan likuiditas sebagai topik utama narasi.\n";

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
        // analisis_dupont cuma menyimpan roe_dupont. NPM, TATO, dan Leverage
        // yang jadi komponen pembentuknya diambil dari tabel masing-masing.
        $data = $analisis->dupont;
        $dokumen = $analisis->dokumen;
        $npm      = $analisis->profitabilitas?->net_profit_margin;
        $tato     = $analisis->aktivitas?->total_asset_turnover;
        $leverage = $analisis->solvabilitas?->leverage_multiplier;
        $der      = $analisis->solvabilitas?->debt_to_equity;
        $dar      = $analisis->solvabilitas?->debt_to_asset;

        $Prompt  = $this->konteksDasarPrompt($dokumen);
        $Prompt .= "=== DATA ANALISIS DUPONT ===\n";
        $Prompt .= "Net Profit Margin (NPM): " . $npm . "%\n";
        $Prompt .= "Total Asset Turnover (TATO): " . $tato . " kali\n";
        $Prompt .= "Leverage Multiplier (Total Aset / Ekuitas): " . $leverage . " kali\n";
        $Prompt .= "Hasil ROE = NPM x TATO x Leverage: " . $data->roe_dupont . "%\n";

        if ($der !== null || $dar !== null) {
            $Prompt .= "=== DATA RASIO SOLVABILITAS (konteks pendukung) ===\n";
            $Prompt .= "Debt to Equity Ratio (DER): " . $der . "x\n";
            $Prompt .= "Debt to Asset Ratio (DAR): " . $dar . "x\n";
        }

        $Prompt .= "\nBerikan narasi analisis DuPont berdasarkan data di atas.\n";

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
        $dokumen = $analisis->dokumen;
        $likuiditas = $analisis->likuiditas;
        $solvabilitas = $analisis->solvabilitas;

        $Prompt  = $this->konteksDasarPrompt($dokumen);
        $Prompt .= "=== DATA COMMON-SIZE (PERSENTASE) ===\n";
        $Prompt .= "--- Common-Size Laporan Laba Rugi (basis Pendapatan = 100%) ---\n";
        $Prompt .= "Pendapatan: " . $data->pendapatan_persen . "%\n";
        $Prompt .= "Beban (termasuk beban pajak): " . $data->beban_persen . "%\n";
        $Prompt .= "Laba Bersih: " . $data->laba_bersih_persen . "%\n";
        $Prompt .= "--- Common-Size Laporan Posisi Keuangan (basis Total Aset = 100%) ---\n";
        $Prompt .= "Aset Lancar: " . $data->aset_lancar_persen . "%\n";
        $Prompt .= "Aset Tetap: " . $data->aset_tetap_persen . "%\n";
        $Prompt .= "Liabilitas Jangka Pendek: " . $data->liabilitas_pendek_persen . "%\n";
        $Prompt .= "Liabilitas Jangka Panjang: " . $data->liabilitas_panjang_persen . "%\n";
        $Prompt .= "Ekuitas: " . $data->ekuitas_persen . "%\n";

        if ($likuiditas) {
            $Prompt .= "=== DATA RASIO LIKUIDITAS (konteks pendukung) ===\n";
            $Prompt .= "Current Ratio (CR): " . $likuiditas->current_ratio . "x\n";
        }

        if ($solvabilitas) {
            $Prompt .= "=== DATA RASIO SOLVABILITAS (konteks pendukung) ===\n";
            $Prompt .= "Debt to Asset Ratio (DAR): " . $solvabilitas->debt_to_asset . "x\n";
        }

        $Prompt .= "\nBerikan narasi analisis common-size berdasarkan data di atas.\n";

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
    // NARASI AI PER SECTION (TREND, 4 KATEGORI TERPISAH)
    // Data diambil live dari Analisis::getXxxTrend(), bukan dari snapshot.
    // Trend Arus Kas SUDAH DIHAPUS (tidak dipakai lagi).
    // =====================================================================

    public function prosesTrendAkunUtama(Analisis $analisis, ?string $userPrompt = null): void
    {
        $trendData = $analisis->getAkunUtamaTrend();
        $periodeData = $trendData['periode_data'];

        $Prompt  = $this->blokInfoPerusahaan($analisis->dokumen->perusahaan);
        $Prompt .= "=== TREN AKUN UTAMA ===\n";
        $Prompt .= "Berikan narasi analisis tren akun utama (Pendapatan, Laba Bersih, Total Aset, Kas Setara Kas, Total Ekuitas) lintas periode berikut: \n";
        $Prompt .= "STATUS DATA: " . count($periodeData) . " periode tersedia dalam scope";
        $Prompt .= $trendData['has_gap']
            ? ", namun ada periode dengan data tidak lengkap — fokuskan narasi hanya pada periode yang datanya tersedia.\n"
            : ", seluruh data lengkap.\n";

        foreach ($periodeData as $titik) {
            $label = $this->labelPeriodeArray($titik['analisis']);
            $Prompt .= "--- {$label} ---\n";
            $Prompt .= "Pendapatan: " . number_format($titik['total_pendapatan'] ?? 0, 0, ',', '.') . " (Δ " . ($titik['growth_total_pendapatan'] !== null ? round($titik['growth_total_pendapatan'], 2) . '%' : '-') . ")\n";
            $Prompt .= "Laba Bersih: " . number_format($titik['laba_bersih_sesudah_pajak'] ?? 0, 0, ',', '.') . " (Δ " . ($titik['growth_laba_bersih_sesudah_pajak'] !== null ? round($titik['growth_laba_bersih_sesudah_pajak'], 2) . '%' : '-') . ")\n";
            $Prompt .= "Total Aset: " . number_format($titik['total_asset'] ?? 0, 0, ',', '.') . " (Δ " . ($titik['growth_total_asset'] !== null ? round($titik['growth_total_asset'], 2) . '%' : '-') . ")\n";
            $Prompt .= "Kas Setara Kas: " . number_format($titik['total_kas_setara_kas'] ?? 0, 0, ',', '.') . " (Δ " . ($titik['growth_total_kas_setara_kas'] !== null ? round($titik['growth_total_kas_setara_kas'], 2) . '%' : '-') . ")\n";
            $Prompt .= "Total Ekuitas: " . number_format($titik['total_equitas'] ?? 0, 0, ',', '.') . " (Δ " . ($titik['growth_total_equitas'] !== null ? round($titik['growth_total_equitas'], 2) . '%' : '-') . ")\n";
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

        $Prompt  = $this->blokInfoPerusahaan($analisis->dokumen->perusahaan);
        $Prompt .= "=== TREN RASIO KEUANGAN ===\n";
        $Prompt .= "Berikan narasi analisis tren rasio keuangan (likuiditas, profitabilitas, solvabilitas, aktivitas) lintas periode berikut: \n";
        $Prompt .= "STATUS DATA: " . count($periodeData) . " periode tersedia dalam scope";
        $Prompt .= $trendData['has_gap']
            ? ", namun ada periode dengan data tidak lengkap — fokuskan narasi hanya pada periode yang datanya tersedia.\n"
            : ", seluruh data lengkap.\n";

        foreach ($periodeData as $titik) {
            $a = $titik['analisis'];
            $label = $this->labelPeriodeArray($a);
            $Prompt .= "--- {$label} ---\n";
            $Prompt .= "CR: " . ($a['likuiditas']['current_ratio'] ?? '-') . "%, CSR: " . ($a['likuiditas']['cash_ratio'] ?? '-') . "%\n";
            $Prompt .= "NPM: " . ($a['profitabilitas']['net_profit_margin'] ?? '-') . "%, ROA: " . ($a['profitabilitas']['ROA'] ?? '-') . "%, ROE: " . ($a['profitabilitas']['ROE'] ?? '-') . "%\n";
            $Prompt .= "DER: " . ($a['solvabilitas']['debt_to_equity'] ?? '-') . "%, DAR: " . ($a['solvabilitas']['debt_to_asset'] ?? '-') . "%, Financial Leverage: " . ($a['solvabilitas']['leverage_multiplier'] ?? '-') . "x\n";
            $Prompt .= "TATO: " . ($a['aktivitas']['total_asset_turnover'] ?? '-') . "x, WCT: " . ($a['aktivitas']['working_capital_turnover'] ?? '-') . "x, FAT: " . ($a['aktivitas']['fixed_asset_turnover'] ?? '-') . "x\n";
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

        $Prompt  = $this->blokInfoPerusahaan($analisis->dokumen->perusahaan);
        $Prompt .= "=== TREN DUPONT ===\n";
        $Prompt .= "Berikan narasi analisis tren DuPont (NPM, TATO, Leverage Multiplier, ROE) lintas periode berikut: \n";
        $Prompt .= "STATUS DATA: " . count($periodeData) . " periode tersedia dalam scope";
        $Prompt .= $trendData['has_gap']
            ? ", namun ada periode dengan data tidak lengkap — fokuskan narasi hanya pada periode yang datanya tersedia.\n"
            : ", seluruh data lengkap.\n";

        foreach ($periodeData as $titik) {
            $a = $titik['analisis'];
            $label = $this->labelPeriodeArray($a);
            $Prompt .= "--- {$label} ---\n";
            $Prompt .= "NPM: " . ($a['profitabilitas']['net_profit_margin'] ?? '-') . "%, TATO: " . ($a['aktivitas']['total_asset_turnover'] ?? '-') . "x, Leverage: " . ($a['solvabilitas']['leverage_multiplier'] ?? '-') . "x, ROE Dupont: " . ($a['dupont']['roe_dupont'] ?? '-') . "%\n";
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

        $Prompt  = $this->blokInfoPerusahaan($analisis->dokumen->perusahaan);
        $Prompt .= "=== TREN COMMON-SIZE ===\n";
        $Prompt .= "Berikan narasi analisis tren common-size (proporsi vertikal Laba Rugi & Neraca) lintas periode berikut: \n";
        $Prompt .= "STATUS DATA: " . count($periodeData) . " periode tersedia dalam scope";
        $Prompt .= $trendData['has_gap']
            ? ", namun ada periode dengan data tidak lengkap — fokuskan narasi hanya pada periode yang datanya tersedia.\n"
            : ", seluruh data lengkap.\n";

        foreach ($periodeData as $titik) {
            $a = $titik['analisis'];
            $label = $this->labelPeriodeArray($a);
            $c = $a['commonsize'];
            $Prompt .= "--- {$label} ---\n";
            $Prompt .= "Beban: " . ($c['beban_persen'] ?? '-') . "%, Laba Bersih: " . ($c['laba_bersih_persen'] ?? '-') . "%\n";
            $Prompt .= "Aset Lancar: " . ($c['aset_lancar_persen'] ?? '-') . "%, Aset Tetap: " . ($c['aset_tetap_persen'] ?? '-') . "%, Liabilitas Jk. Pendek: " . ($c['liabilitas_pendek_persen'] ?? '-') . "%, Liabilitas Jk. Panjang: " . ($c['liabilitas_panjang_persen'] ?? '-') . "%, Ekuitas: " . ($c['ekuitas_persen'] ?? '-') . "%\n";
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

    public function prosesSummaryAnalisis(Analisis $analisis, ?string $userPrompt = null): void
    {
        // Analisis tidak punya relasi perusahaan()/kolom periode langsung — lewat dokumen().
        $dokumen = $analisis->dokumen;
        $perusahaan = $dokumen->perusahaan;
        $labelPeriode = $this->labelPeriodeDokumen($dokumen);

        $Prompt = "Susun Executive Summary berdasarkan seluruh hasil analisis keuangan yang tersedia.\n";

        $Prompt .= "=== INFORMASI PERUSAHAAN ===\n";
        $Prompt .= "Perusahaan : {$perusahaan->nama}\n";
        $Prompt .= "Sektor : {$perusahaan->sektor}\n";
        $Prompt .= "Periode Analisis : {$labelPeriode}\n";

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
        }

        if ($analisis->ringkasan_laporan) {
            $Prompt .= "=== EXECUTIVE SUMMARY SEBELUMNYA ===\n";
            $Prompt .= $analisis->ringkasan_laporan . "\n";
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
            'ringkasan_laporan' => $narasi,
        ]);
    }
}