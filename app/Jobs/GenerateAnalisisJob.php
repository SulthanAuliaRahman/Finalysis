<?php

namespace App\Jobs;

use App\Models\Analisis;
use App\Services\AnalysisFinancialService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAnalisisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 900;
    public $tries = 1;

    public function __construct(
        public Analisis $analisis,
        public ?string $onlySection = null,
        public ?string $userPrompt = null,
    ) {
    }

    public function handle(AnalysisFinancialService $service): void
    {
        $sections  = $this->onlySection ? [$this->onlySection] : $this->analisis->sectionsBelumSelesai();
        $isFullRun = $this->onlySection === null;

        if ($isFullRun) {
            $this->analisis->update([
                'status_generate'  => 'processing',
                'progress_current' => 0,
                'progress_total'   => count($sections), // total dari yang BELUM, bukan 11 selalu
                'error_message'    => null,
            ]);
        }

        foreach ($sections as $i => $section) {
            if (in_array($section, Analisis::TREND_SECTIONS) && $this->analisis->apakahPeriodePertama()) {
                $this->analisis->updateSectionStatus($section, 'tidak_berlaku');
                if ($isFullRun) $this->analisis->update(['progress_current' => $i + 1]);
                continue;
            }

            $this->analisis->updateSectionStatus($section, 'processing');

            try {
                $this->prosesSection($section, $service);
                $this->analisis->updateSectionStatus($section, 'selesai');
            } catch (\Throwable $e) {
                report($e);
                $this->analisis->updateSectionStatus($section, 'gagal', $e->getMessage());
            }

            if ($isFullRun) $this->analisis->update(['progress_current' => $i + 1]);
        }

        if ($isFullRun) {
            $this->analisis->update([
                'status_generate' => $this->analisis->semuaSelesai() ? 'selesai' : 'gagal',
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        if ($this->onlySection) {
            $this->analisis->updateSectionStatus($this->onlySection, 'gagal', $exception->getMessage());
            return;
        }

        $this->analisis->update([
            'status_generate' => 'gagal',
            'error_message'   => $exception->getMessage(),
        ]);
    }

    private function prosesSection(string $section, AnalysisFinancialService $service): void
    {
        match ($section) {
            'likuiditas'        => $service->prosesLikuiditas($this->analisis, $this->userPrompt),
            'profitabilitas'    => $service->prosesProfitabilitas($this->analisis, $this->userPrompt),
            'solvabilitas'      => $service->prosesSolvabilitas($this->analisis, $this->userPrompt),
            'aktivitas'         => $service->prosesAktivitas($this->analisis, $this->userPrompt),
            'dupont'            => $service->prosesDupont($this->analisis, $this->userPrompt),
            'commonsize'        => $service->prosesCommonsize($this->analisis, $this->userPrompt),
            'trend_akun_utama'  => $service->prosesTrendAkunUtama($this->analisis, $this->userPrompt),
            'trend_rasio'       => $service->prosesTrendRasio($this->analisis, $this->userPrompt),
            'trend_dupont'      => $service->prosesTrendDupont($this->analisis, $this->userPrompt),
            'trend_commonsize'  => $service->prosesTrendCommonsize($this->analisis, $this->userPrompt),
            'summary'           => $this->prosesSummary($service),
            default             => null,
        };
    }

    private function prosesSummary(AnalysisFinancialService $service): void
    {
        if (!$this->analisis->siapUntukSummary()) {
            throw new \RuntimeException('Section wajib (selain trend) belum lengkap, summary ditunda.');
        }

        $service->prosesSummaryAnalisis($this->analisis, $this->userPrompt);
    }
}
