<?php

namespace App\Services;

use App\Models\Analisis;
use App\Models\Dokumen;
use App\Models\AnalisisTrendPeriode;
use NeuronAI\Chat\Messages\UserMessage;

use App\Neuron\RAG\TrendAgent;
use App\Neuron\RAG\DupontAgent;
use App\Neuron\RAG\CommonsizeAgent;

class TrendAnalysisService
{
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
        // 1. Tentukan scope periode sejenis
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

        // 2. Resolve data keuangan + kumpulkan titik data akun utama
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

        // 3a. Narasi Akun Utama
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
        $narasiAkun = TextCleanerService::bersihkanMarkdown($narasiAkun);

        // 3b. Narasi Tren Rasio
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
                . "CR: " . ($l ? $l->current_ratio : '-') . "%, "
                . "QR: " . ($l ? $l->quick_ratio : '-') . "%, "
                . "CSR: " . ($l ? $l->cash_ratio : '-') . "%\n"
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
            $narasiRasio = TextCleanerService::bersihkanMarkdown($narasiRasio);
        }

        // 3c. Narasi Tren DuPont
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
            $narasiDupont = TextCleanerService::bersihkanMarkdown($narasiDupont);
        }

        // 3d. Narasi Tren Common-size
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
            $narasiCommonsize = TextCleanerService::bersihkanMarkdown($narasiCommonsize);
        }

        // 4. Simpan induk analisis_trend
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

        // 5. Ganti seluruh titik data lama, insert ulang
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
}