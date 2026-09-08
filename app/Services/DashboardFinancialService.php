<?php

namespace App\Services;

use App\Models\Dokumen;
use App\Models\Perusahaan;
use Illuminate\Support\Collection;

class DashboardFinancialService
{
    public function build(?Perusahaan $perusahaan, ?string $selectedDocumentId = null): array {

        if (! $perusahaan?->exists) {
            return $this->empty();
        }

        $documents = $perusahaan->dokumen()
            ->whereHas('neraca')
            ->whereHas('labaRugi')
            ->whereHas('analisis')
            ->with([
                'analisis:id,dokumen_id,ringkasan_laporan,status_generate',
            ])
            ->orderByDesc('tahun')
            ->orderByDesc('quarter')
            ->orderByDesc('bulan')
            ->get();


        if ($documents->isEmpty()) {
            return $this->empty($perusahaan);
        }

        $selected = $documents->firstWhere('id', $selectedDocumentId)
            ?? $documents->first();

        $akunTrend = $selected->getAkunUtamaTrend();
        $rasioTrend = $selected->getRasioTrend();


        $akunTrendData = collect(
            $akunTrend['periode_data'] ?? []
        );

        $rasioTrendData = collect(
            $rasioTrend['periode_data'] ?? []
        );



        $currentAccounts = $akunTrendData->last();
        $previousAccounts = $akunTrendData->count() > 1? $akunTrendData->get($akunTrendData->count() - 2): null;
        $currentRatios = $this->findRatioData($rasioTrendData, $currentAccounts);
        $previousRatios = $this->findRatioData($rasioTrendData, $previousAccounts);

        return [
            'company' => $this->company($perusahaan),
            'selectedPeriod' => $this->selectedPeriod($selected),
            'periodOptions' => $this->periodOptions($documents),
            'financialOverview' => $this->financialOverview($currentAccounts,$previousAccounts),
            'ratios' => $this->ratios($currentRatios, $previousRatios),
            'trendSeries' => $this->trendSeries(
                $akunTrendData,
                $rasioTrendData
            ),
        ];
    }



    private function company(Perusahaan $perusahaan): array
    {
        return [
            'id' => $perusahaan->id,
            'name' => $perusahaan->nama,
        ];
    }




    private function selectedPeriod(Dokumen $document): array
    {
        return [
            'documentId' => $document->id,
            'label' => $document->periode,
            'type' => $document->periode_type,
            'reportStatus' => $document->status,
            'analysisId' => $document->analisis?->id,
            'analysisStatus' => $document->analisis?->status_generate,
            'reportSummary' => $document->analisis?->ringkasan_laporan,
        ];
    }


    private function periodOptions(Collection $documents): array
    {
        return $documents
            ->map(function (Dokumen $document) {
                return [
                    'documentId' => $document->id,
                    'label' => $document->periode,
                    'type' => $document->periode_type,
                ];
            })
            ->values()
            ->all();
    }



    private function financialOverview(
        ?array $current,
        ?array $previous
    ): array {
        if (! $current) {
            return [];
        }

        $metrics = [
            [
                'key' => 'revenue',
                'label' => 'Pendapatan',
                'field' => 'total_pendapatan',
            ],
            // [
            //     'key' => 'netIncome',
            //     'label' => 'Laba Bersih',
            //     'field' => 'laba_bersih_sesudah_pajak',
            // ],
            [
                'key' => 'totalAssets',
                'label' => 'Total Aset',
                'field' => 'total_asset',
            ],
            [
                'key' => 'totalExpenses',
                'label' => 'Total Beban',
                'field' => 'total_beban',
            ],
            [
                'key' => 'totalLiabilities',
                'label' => 'Total Liabilitas',
                'field' => 'total_liabilities',
            ],
            [
                'key' => 'equity',
                'label' => 'Ekuitas',
                'field' => 'total_equitas',
            ],
        ];

        return collect($metrics)
            ->mapWithKeys(function (array $metric) use (
                $current,
                $previous
            ) {
                $currentValue = $this->number(
                    $current[$metric['field']] ?? null
                );

                $previousValue = $this->number(
                    $previous[$metric['field']] ?? null
                );

                $change = $this->percentageChange(
                    $currentValue,
                    $previousValue
                );

                return [
                    $metric['key'] => [
                        'label' => $metric['label'],
                        'value' => $currentValue,
                        'comparison' => $change,
                        'trend' => $this->trend($change),
                    ],
                ];
            })
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Ratios
    |--------------------------------------------------------------------------
    */

    private function ratios(array $current, array $previous): array {
        $definitions = [
            [
                'key' => 'currentRatio',
                'label' => 'Current Ratio',
                'category' => 'Likuiditas',
                'field' => 'likuiditas.current_ratio',
            ],
            [
                'key' => 'cashRatio',
                'label' => 'Cash Ratio',
                'category' => 'Likuiditas',
                'field' => 'likuiditas.cash_ratio',
            ],
            [
                'key' => 'netProfitMargin',
                'label' => 'Net Profit Margin',
                'category' => 'Profitabilitas',
                'field' => 'profitabilitas.net_profit_margin',
            ],
            [
                'key' => 'roa',
                'label' => 'ROA',
                'category' => 'Profitabilitas',
                'field' => 'profitabilitas.ROA',
            ],
            [
                'key' => 'roe',
                'label' => 'ROE',
                'category' => 'Profitabilitas',
                'field' => 'profitabilitas.ROE',
            ],
            [
                'key' => 'debtToAsset',
                'label' => 'Debt to Asset',
                'category' => 'Solvabilitas',
                'field' => 'solvabilitas.debt_to_asset',
            ],
            [
                'key' => 'debtToEquity',
                'label' => 'Debt to Equity',
                'category' => 'Solvabilitas',
                'field' => 'solvabilitas.debt_to_equity',
            ],
        ];

        return collect($definitions)
            ->map(function (array $definition) use (
                $current,
                $previous
            ) {
                $value = $this->number(
                    data_get($current, $definition['field'])
                );

                $before = $this->number(
                    data_get($previous, $definition['field'])
                );

                $delta = $value === null || $before === null
                    ? null
                    : $value - $before;

                return [
                    'key' => $definition['key'],
                    'label' => $definition['label'],
                    'category' => $definition['category'],
                    'value' => $value,
                    'delta' => $delta,
                    'trend' => $this->trend($delta),
                ];
            })
            ->values()
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Trend Series
    |--------------------------------------------------------------------------
    */

    private function trendSeries(Collection $akunTrendData, Collection $rasioTrendData): array {
        return $akunTrendData
            ->map(function (array $account) use ($rasioTrendData) {

                $ratio = $this->findRatioData(
                    $rasioTrendData,
                    $account
                );

                return [
                    'label' => $this->periodLabelFromData(
                        $account['analisis'] ?? []
                    ),

                    'revenue' => $this->number(
                        $account['total_pendapatan'] ?? null
                    ),

                    'totalExpenses' => $this->number(
                        $account['total_beban'] ?? null
                    ),

                    'totalAssets' => $this->number(
                        $account['total_asset'] ?? null
                    ),

                    'totalLiabilities' => $this->number(
                        $account['total_liabilities'] ?? null
                    ),

                    'equity' => $this->number(
                        $account['total_equitas'] ?? null
                    ),

                    'currentRatio' => $this->number(
                        data_get(
                            $ratio,
                            'likuiditas.current_ratio'
                        )
                    ),

                    'cashRatio' => $this->number(
                        data_get(
                            $ratio,
                            'likuiditas.cash_ratio'
                        )
                    ),

                    'netProfitMargin' => $this->number(
                        data_get(
                            $ratio,
                            'profitabilitas.net_profit_margin'
                        )
                    ),

                    'roa' => $this->number(
                        data_get(
                            $ratio,
                            'profitabilitas.ROA'
                        )
                    ),

                    'roe' => $this->number(
                        data_get(
                            $ratio,
                            'profitabilitas.ROE'
                        )
                    ),

                    'debtToAsset' => $this->number(
                        data_get(
                            $ratio,
                            'solvabilitas.debt_to_asset'
                        )
                    ),

                    'debtToEquity' => $this->number(
                        data_get(
                            $ratio,
                            'solvabilitas.debt_to_equity'
                        )
                    ),
                ];
            })
            ->values()
            ->all();
    }

    private function findRatioData(Collection $ratioTrendData, ?array $accountData): array {
        if (! $accountData) {
            return [];
        }

        $period = $accountData['analisis'] ?? [];

        $item = $ratioTrendData->first(
            function (array $data) use ($period) {

                $analysis = $data['analisis'] ?? [];

                return
                    ($analysis['periode_type'] ?? null)
                        === ($period['periode_type'] ?? null)

                    && (int) ($analysis['tahun'] ?? 0)
                        === (int) ($period['tahun'] ?? 0)

                    && (int) ($analysis['quarter'] ?? 0)
                        === (int) ($period['quarter'] ?? 0)

                    && (int) ($analysis['bulan'] ?? 0)
                        === (int) ($period['bulan'] ?? 0);
            }
        );

        return $item['analisis'] ?? [];
    }



    private function empty(?Perusahaan $perusahaan = null): array {
        return [
            'company' => $perusahaan? $this->company($perusahaan): null,
            'selectedPeriod' => null,
            'periodOptions' => [],
            'financialOverview' => [],
            'ratios' => [],
            'trendSeries' => [],
        ];
    }

    private function number(?int $value): ?float
    {
        return $value === null
            ? null
            : (float) $value;
    }



    private function percentageChange(?float $current,?float $previous): ?float {
        if (
            $current === null ||
            $previous === null ||
            $previous == 0.0
        ) {
            return null;
        }

        return (
            ($current - $previous)
            / abs($previous)
        ) * 100;
    }


    private function trend(?float $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return match (true) {
            $value > 0 => 'up',
            $value < 0 => 'down',
            default => 'stable',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Period Label
    |--------------------------------------------------------------------------
    */

    private function periodLabelFromData(array $period): string
    {
        return match ($period['periode_type'] ?? null) {

            'annual' =>
                (string) ($period['tahun'] ?? ''),

            'quarterly' =>
                "Q{$period['quarter']} {$period['tahun']}",

            'monthly' =>
                $this->monthName(
                    (int) ($period['bulan'] ?? 0)
                ) . " {$period['tahun']}",

            default => '-',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Month Name
    |--------------------------------------------------------------------------
    */

    private function monthName(int $month): string
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ][$month] ?? '-';
    }
}