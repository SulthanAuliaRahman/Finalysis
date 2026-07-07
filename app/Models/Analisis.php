<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function getRasioTrend(): array
    {
        $query = Analisis::where('perusahaan_id', $this->perusahaan_id)
            ->where('periode_type', $this->periode_type);

        // Filter "periode <= analisis saat ini" sesuai periode_type
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

        // Urutkan kronologis ASC, ambil 5 terakhir via subquery DESC lalu balik
        $periodeList = $query
            ->orderByDesc('tahun')
            ->orderByDesc('quarter')
            ->orderByDesc('bulan')
            ->limit(5)
            ->with([
                'likuiditas:analisis_id,current_ratio,quick_ratio,cash_ratio',
                'profitabilitas:analisis_id,ROE,ROA,net_profit_margin',
                'solvabilitas:analisis_id,debt_to_equity,debt_to_asset',
                'aktivitas:analisis_id,total_asset_turnover',
            ])
            ->get()
            ->reverse()   // balik ke ASC setelah limit
            ->values();

        // Deteksi gap: periode yang ada di tengah tapi semua rasionya null
        $hasGap = $periodeList->contains(function ($a) {
            return $a->likuiditas === null
                && $a->profitabilitas === null
                && $a->solvabilitas === null
                && $a->aktivitas === null;
        });

        // Map ke shape yang dibutuhkan FE
        $periodeData = $periodeList->map(function ($a, $index) {
            return [
                'urutan'  => $index + 1,
                'analisis' => [
                    'id'           => $a->id,
                    'periode_type' => $a->periode_type,
                    'tahun'        => $a->tahun,
                    'quarter'      => $a->quarter,
                    'bulan'        => $a->bulan,
                    'likuiditas'   => $a->likuiditas ? [
                        'current_ratio' => $a->likuiditas->current_ratio,
                        'quick_ratio'   => $a->likuiditas->quick_ratio,
                        'cash_ratio'    => $a->likuiditas->cash_ratio,
                    ] : null,
                    'profitabilitas' => $a->profitabilitas ? [
                        'net_profit_margin' => $a->profitabilitas->net_profit_margin,
                        'ROA'               => $a->profitabilitas->ROA,
                        'ROE'               => $a->profitabilitas->ROE,
                    ] : null,
                    'solvabilitas' => $a->solvabilitas ? [
                        'debt_to_equity' => $a->solvabilitas->debt_to_equity,
                        'debt_to_asset'  => $a->solvabilitas->debt_to_asset,
                    ] : null,
                    'aktivitas' => $a->aktivitas ? [
                        'total_asset_turnover' => $a->aktivitas->total_asset_turnover,
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
}
