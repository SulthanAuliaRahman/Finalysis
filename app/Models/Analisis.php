<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Dokumen;

class Analisis extends Model
{
    use HasFactory;

    protected $table = 'analisis';

    protected $fillable = [
        'perusahaan_id',
        'periode_type',
        'tahun',
        'quarter',
        'bulan',
        'status',
        'AI_summary_insight',
    ];

    protected $appends = ['periode'];


    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class);
    }

    public function likuiditas()
    {
        return $this->hasOne(AnalisisLikuiditas::class, 'analisis_id');
    }

    public function profitabilitas()
    {
        return $this->hasOne(AnalisisProfitabilitas::class, 'analisis_id');
    }

    public function solvabilitas()
    {
        return $this->hasOne(AnalisisSolvabilitas::class, 'analisis_id');
    }

    public function aktivitas()
    {
        return $this->hasOne(AnalisisAktivitas::class, 'analisis_id');
    }

    public function dupont()
    {
        return $this->hasOne(AnalisisDupont::class, 'analisis_id');
    }

    public function commonsize()
    {
        return $this->hasOne(AnalisisCommonsize::class, 'analisis_id');
    }

    public function trend()
    {
        return $this->hasOne(AnalisisTrend::class, 'analisis_id');
    }

    // Accessor periode yang konsisten dengan model Dokumen
    public function getPeriodeAttribute()
    {
        return match ($this->periode_type) {
            'annual'    => (string) $this->tahun,
            'quarterly' => "Q{$this->quarter} {$this->tahun}",
            'monthly'   => [
                1  => "Januari",
                2  => "Februari",
                3  => "Maret",
                4  => "April",
                5  => "Mei",
                6  => "Juni",
                7  => "Juli",
                8  => "Agustus",
                9  => "September",
                10 => "Oktober",
                11 => "November",
                12 => "Desember",
            ][$this->bulan] . " " . $this->tahun,
            default => '-',
        };
    }

    private function buildPeriodeQuery()
    {
        $query = Analisis::where('perusahaan_id', $this->perusahaan_id)
            ->where('periode_type', $this->periode_type);

        match ($this->periode_type) {
            'annual' => $query->where('tahun', '<=', $this->tahun),

            'quarterly' => $query->where(function ($q) {
                $q->where('tahun', '<', $this->tahun)
                  ->orWhere(function ($q2) {
                      $q2->where('tahun', $this->tahun)
                         ->where('quarter', '<=', $this->quarter);
                  });
            }),

            'monthly' => $query->where(function ($q) {
                $q->where('tahun', '<', $this->tahun)
                  ->orWhere(function ($q2) {
                      $q2->where('tahun', $this->tahun)
                         ->where('bulan', '<=', $this->bulan);
                  });
            }),
        };

        return $query
            ->orderByDesc('tahun')
            ->orderByDesc('quarter')
            ->orderByDesc('bulan')
            ->limit(5);
    }

    public function getRasioTrend(): array
    {
        $this->loadMissing('trend');

        $periodeList = $this->buildPeriodeQuery()
            ->with([
                'likuiditas:analisis_id,current_ratio,quick_ratio,cash_ratio',
                'profitabilitas:analisis_id,ROE,ROA,net_profit_margin',
                'solvabilitas:analisis_id,debt_to_equity,debt_to_asset',
                'aktivitas:analisis_id,total_asset_turnover',
            ])
            ->get()
            ->reverse()
            ->values();

        $hasGap = $periodeList->contains(function ($analisisPeriode) {
            return $analisisPeriode->likuiditas === null
                && $analisisPeriode->profitabilitas === null
                && $analisisPeriode->solvabilitas === null
                && $analisisPeriode->aktivitas === null;
        });

        $periodeData = $periodeList->map(function ($analisisPeriode, $index) {
            return [
                'urutan'   => $index + 1,
                'analisis' => [
                    'id'             => $analisisPeriode->id,
                    'periode_type'   => $analisisPeriode->periode_type,
                    'tahun'          => $analisisPeriode->tahun,
                    'quarter'        => $analisisPeriode->quarter,
                    'bulan'          => $analisisPeriode->bulan,
                    'likuiditas'     => $analisisPeriode->likuiditas ? [
                        'current_ratio' => $analisisPeriode->likuiditas->current_ratio,
                        'quick_ratio'   => $analisisPeriode->likuiditas->quick_ratio,
                        'cash_ratio'    => $analisisPeriode->likuiditas->cash_ratio,
                    ] : null,
                    'profitabilitas' => $analisisPeriode->profitabilitas ? [
                        'net_profit_margin' => $analisisPeriode->profitabilitas->net_profit_margin,
                        'ROA'               => $analisisPeriode->profitabilitas->ROA,
                        'ROE'               => $analisisPeriode->profitabilitas->ROE,
                    ] : null,
                    'solvabilitas'   => $analisisPeriode->solvabilitas ? [
                        'debt_to_equity' => $analisisPeriode->solvabilitas->debt_to_equity,
                        'debt_to_asset'  => $analisisPeriode->solvabilitas->debt_to_asset,
                    ] : null,
                    'aktivitas'      => $analisisPeriode->aktivitas ? [
                        'total_asset_turnover' => $analisisPeriode->aktivitas->total_asset_turnover,
                    ] : null,
                ],
            ];
        })->all();

        return [
            'narasi_trend_rasio_AI' => $this->trend?->narasi_trend_rasio_AI,
            'has_gap'               => $hasGap,
            'periode_data'          => $periodeData,
        ];
    }

    public function getDupontTrend(): array
    {
        $this->loadMissing('trend');

        $periodeList = $this->buildPeriodeQuery()
            ->with([
                'dupont:analisis_id,net_profit_margin,total_asset_turnover,leverage_multiplier,roe',
            ])
            ->get()
            ->reverse()
            ->values();

        $hasGap = $periodeList->contains(function ($analisisPeriode) {
            return $analisisPeriode->dupont === null;
        });

        $periodeData = $periodeList->map(function ($analisisPeriode, $index) {
            return [
                'urutan'   => $index + 1,
                'analisis' => [
                    'id'           => $analisisPeriode->id,
                    'periode_type' => $analisisPeriode->periode_type,
                    'tahun'        => $analisisPeriode->tahun,
                    'quarter'      => $analisisPeriode->quarter,
                    'bulan'        => $analisisPeriode->bulan,
                    'dupont'       => $analisisPeriode->dupont ? [
                        'net_profit_margin'    => $analisisPeriode->dupont->net_profit_margin,
                        'total_asset_turnover' => $analisisPeriode->dupont->total_asset_turnover,
                        'leverage_multiplier'  => $analisisPeriode->dupont->leverage_multiplier,
                        'roe'                  => $analisisPeriode->dupont->roe,
                    ] : null,
                ],
            ];
        })->all();

        return [
            'narasi_trend_dupont_AI' => $this->trend?->narasi_trend_dupont_AI,
            'has_gap'                => $hasGap,
            'periode_data'           => $periodeData,
        ];
    }

    public function getCommonsizeTrend(): array
    {
        $this->loadMissing('trend');

        $periodeList = $this->buildPeriodeQuery()
            ->with([
                'commonsize:analisis_id,hpp_persen,laba_kotor_persen,beban_lain_pajak_persen,laba_bersih_persen,aset_lancar_persen,aset_tetap_persen,liabilitas_lancar_persen,liabilitas_panjang_persen,ekuitas_persen',
            ])
            ->get()
            ->reverse()
            ->values();

        $hasGap = $periodeList->contains(function ($analisisPeriode) {
            return $analisisPeriode->commonsize === null;
        });

        $periodeData = $periodeList->map(function ($analisisPeriode, $index) {
            return [
                'urutan'   => $index + 1,
                'analisis' => [
                    'id'           => $analisisPeriode->id,
                    'periode_type' => $analisisPeriode->periode_type,
                    'tahun'        => $analisisPeriode->tahun,
                    'quarter'      => $analisisPeriode->quarter,
                    'bulan'        => $analisisPeriode->bulan,
                    'commonsize'   => $analisisPeriode->commonsize ? [
                        'hpp_persen'                => $analisisPeriode->commonsize->hpp_persen,
                        'laba_kotor_persen'         => $analisisPeriode->commonsize->laba_kotor_persen,
                        'beban_lain_pajak_persen'   => $analisisPeriode->commonsize->beban_lain_pajak_persen,
                        'laba_bersih_persen'        => $analisisPeriode->commonsize->laba_bersih_persen,
                        'aset_lancar_persen'        => $analisisPeriode->commonsize->aset_lancar_persen,
                        'aset_tetap_persen'         => $analisisPeriode->commonsize->aset_tetap_persen,
                        'liabilitas_lancar_persen'  => $analisisPeriode->commonsize->liabilitas_lancar_persen,
                        'liabilitas_panjang_persen' => $analisisPeriode->commonsize->liabilitas_panjang_persen,
                        'ekuitas_persen'            => $analisisPeriode->commonsize->ekuitas_persen,
                    ] : null,
                ],
            ];
        })->all();

        return [
            'narasi_trend_commonsize_AI' => $this->trend?->narasi_trend_commonsize_AI,
            'has_gap'                    => $hasGap,
            'periode_data'               => $periodeData,
        ];
    }
    
    public function getAkunUtamaTrend(): array
    {
        $this->loadMissing('trend');

        $periodeList = $this->buildPeriodeQuery()->get()->reverse()->values();
        $hasGap = false;

        $periodeData = $periodeList->map(function ($analisisPeriode, $index) use (&$hasGap) {
            $dokumen = Dokumen::with(['neraca', 'labaRugi', 'arusKas'])
                ->where('perusahaan_id', $analisisPeriode->perusahaan_id)
                ->where('periode_type', $analisisPeriode->periode_type)
                ->where('tahun', $analisisPeriode->tahun)
                ->where('quarter', $analisisPeriode->quarter)
                ->where('bulan', $analisisPeriode->bulan)
                ->latest()
                ->first();

            if (!$dokumen || (!$dokumen->neraca && !$dokumen->labaRugi && !$dokumen->arusKas)) {
                $hasGap = true;
            }

            $neraca = $dokumen?->neraca;
            $labaRugi = $dokumen?->labaRugi;
            $arusKas = $dokumen?->arusKas;

            return [
                'urutan'         => $index + 1,
                'analisis'       => [
                    'id'           => $analisisPeriode->id,
                    'periode_type' => $analisisPeriode->periode_type,
                    'tahun'        => $analisisPeriode->tahun,
                    'quarter'      => $analisisPeriode->quarter,
                    'bulan'        => $analisisPeriode->bulan,
                ],
                'pendapatan'     => $labaRugi?->pendapatan,
                'laba_kotor'     => $labaRugi?->laba_kotor,
                'laba_bersih'    => $labaRugi?->laba_bersih,
                'total_assets'   => $neraca?->total_assets,
                'kas_setara_kas' => $neraca?->cash_equivalent,
                'total_equity'   => $neraca?->total_equity,
                'net_cash_flow'  => $arusKas ? ($arusKas->kas_masuk - $arusKas->kas_keluar) : null,
            ];
        })->all();

        foreach ($periodeData as $i => &$data) {
            $prev = $i > 0 ? $periodeData[$i - 1] : null;
            $keys = ['pendapatan', 'laba_kotor', 'laba_bersih', 'total_assets', 'kas_setara_kas', 'total_equity', 'net_cash_flow'];

            foreach ($keys as $key) {
                $growthKey = 'growth_' . $key;
                if ($prev && isset($prev[$key]) && $prev[$key] != 0 && isset($data[$key])) {
                    $data[$growthKey] = (($data[$key] - $prev[$key]) / abs($prev[$key])) * 100;
                } else {
                    $data[$growthKey] = null;
                }
            }
        }

        return [
            'narasi_trend_AI' => $this->trend?->narasi_trend_akun_utama_AI,
            'has_gap'         => $hasGap,
            'periode_data'    => $periodeData,
        ];
    }

    public function getArusKasTrend(): array
    {
        $this->loadMissing('trend');

        $periodeList = $this->buildPeriodeQuery()->get()->reverse()->values();
        $hasGap = false;

        $periodeData = $periodeList->map(function ($analisisPeriode, $index) use (&$hasGap) {
            $dokumen = Dokumen::with(['arusKas'])
                ->where('perusahaan_id', $analisisPeriode->perusahaan_id)
                ->where('periode_type', $analisisPeriode->periode_type)
                ->where('tahun', $analisisPeriode->tahun)
                ->where('quarter', $analisisPeriode->quarter)
                ->where('bulan', $analisisPeriode->bulan)
                ->latest()
                ->first();

            if (!$dokumen || !$dokumen->arusKas) {
                $hasGap = true;
            }

            $arusKas = $dokumen?->arusKas;

            return [
                'urutan'         => $index + 1,
                'analisis'       => [
                    'id'           => $analisisPeriode->id,
                    'periode_type' => $analisisPeriode->periode_type,
                    'tahun'        => $analisisPeriode->tahun,
                    'quarter'      => $analisisPeriode->quarter,
                    'bulan'        => $analisisPeriode->bulan,
                ],
                'kas_masuk'  => $arusKas?->kas_masuk,
                'kas_keluar' => $arusKas?->kas_keluar,
            ];
        })->all();

        return [
            'narasi_arus_kas_AI' => $this->trend?->narasi_trend_arus_kas_AI,
            'has_gap'            => $hasGap,
            'periode_data'       => $periodeData,
        ];
    }

}
