<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dokumen extends Model
{
    use HasFactory;

    protected $table = 'dokumen';

    protected $fillable = [
        'perusahaan_id',
        'nama_file',
        'storage_path',
        'periode_type',
        'tahun',
        'quarter',
        'bulan',
        'statement_types',
        'ukuran_file',
        'status',
    ];

    protected $casts = [
        'statement_types' => 'array',
    ];

    protected $appends = ['periode'];

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class);
    }

    public function neraca()
    {
        return $this->hasMany(Neraca::class);
    }

    public function labaRugi()
    {
        return $this->hasMany(LabaRugi::class);
    }

    public function analisis()
    {
        return $this->hasOne(Analisis::class);
    }

    public function getPeriodeAttribute()
    {
        return match ($this->periode_type) {
            'annual' => $this->tahun,
            'quarterly' => "Q{$this->quarter} {$this->tahun}",
            'monthly' =>
                [
                    1=>"Januari",
                    2=>"Februari",
                    3=>"Maret",
                    4=>"April",
                    5=>"Mei",
                    6=>"Juni",
                    7=>"Juli",
                    8=>"Agustus",
                    9=>"September",
                    10=>"Oktober",
                    11=>"November",
                    12=>"Desember",
                ][$this->bulan]." ".$this->tahun,
        };
    }

    /**
     * Ambil 5 dokumen terakhir (termasuk dokumen ini sendiri) dari perusahaan
     * & periode_type yang sama, yang sudah punya hasil analisis. Diurutkan
     * DESC (terbaru dulu) supaya limit(5) mengambil yang paling relevan;
     * caller yang butuh urutan kronologis (lama->baru) tinggal ->reverse().
     */
    private function buildPeriodeQuery()
    {
        $query = Dokumen::where('perusahaan_id', $this->perusahaan_id)
            ->where('periode_type', $this->periode_type)
            ->whereHas('analisis');

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
        $this->loadMissing('analisis.trend');

        $dokumenList = $this->buildPeriodeQuery()
            ->with([
                'analisis.likuiditas:analisis_id,current_ratio,quick_ratio,cash_ratio',
                'analisis.profitabilitas:analisis_id,ROE,ROA,net_profit_margin',
                'analisis.solvabilitas:analisis_id,debt_to_equity,debt_to_asset',
                'analisis.aktivitas:analisis_id,total_asset_turnover',
            ])
            ->get()
            ->reverse()
            ->values();

        $hasGap = $dokumenList->contains(function ($dokumenPeriode) {
            $analisisPeriode = $dokumenPeriode->analisis;
            return $analisisPeriode === null
                || ($analisisPeriode->likuiditas === null
                    && $analisisPeriode->profitabilitas === null
                    && $analisisPeriode->solvabilitas === null
                    && $analisisPeriode->aktivitas === null);
        });

        $periodeData = $dokumenList->map(function ($dokumenPeriode, $index) {
            $analisisPeriode = $dokumenPeriode->analisis;

            return [
                'urutan'   => $index + 1,
                'analisis' => [
                    'id'             => $analisisPeriode?->id,
                    'periode_type'   => $dokumenPeriode->periode_type,
                    'tahun'          => $dokumenPeriode->tahun,
                    'quarter'        => $dokumenPeriode->quarter,
                    'bulan'          => $dokumenPeriode->bulan,
                    'likuiditas'     => $analisisPeriode?->likuiditas ? [
                        'current_ratio' => $analisisPeriode->likuiditas->current_ratio,
                        'quick_ratio'   => $analisisPeriode->likuiditas->quick_ratio,
                        'cash_ratio'    => $analisisPeriode->likuiditas->cash_ratio,
                    ] : null,
                    'profitabilitas' => $analisisPeriode?->profitabilitas ? [
                        'net_profit_margin' => $analisisPeriode->profitabilitas->net_profit_margin,
                        'ROA'               => $analisisPeriode->profitabilitas->ROA,
                        'ROE'               => $analisisPeriode->profitabilitas->ROE,
                    ] : null,
                    'solvabilitas'   => $analisisPeriode?->solvabilitas ? [
                        'debt_to_equity' => $analisisPeriode->solvabilitas->debt_to_equity,
                        'debt_to_asset'  => $analisisPeriode->solvabilitas->debt_to_asset,
                    ] : null,
                    'aktivitas'      => $analisisPeriode?->aktivitas ? [
                        'total_asset_turnover' => $analisisPeriode->aktivitas->total_asset_turnover,
                    ] : null,
                ],
            ];
        })->all();

        return [
            'narasi_trend_rasio_AI' => $this->analisis?->trend?->narasi_trend_rasio_AI,
            'has_gap'               => $hasGap,
            'periode_data'          => $periodeData,
        ];
    }

    public function getDupontTrend(): array
    {
        $this->loadMissing('analisis.trend');

        $dokumenList = $this->buildPeriodeQuery()
            ->with(['analisis.dupont:analisis_id,net_profit_margin,total_asset_turnover,leverage_multiplier,roe'])
            ->get()
            ->reverse()
            ->values();

        $hasGap = $dokumenList->contains(function ($dokumenPeriode) {
            return $dokumenPeriode->analisis === null || $dokumenPeriode->analisis->dupont === null;
        });

        $periodeData = $dokumenList->map(function ($dokumenPeriode, $index) {
            $analisisPeriode = $dokumenPeriode->analisis;

            return [
                'urutan'   => $index + 1,
                'analisis' => [
                    'id'           => $analisisPeriode?->id,
                    'periode_type' => $dokumenPeriode->periode_type,
                    'tahun'        => $dokumenPeriode->tahun,
                    'quarter'      => $dokumenPeriode->quarter,
                    'bulan'        => $dokumenPeriode->bulan,
                    'dupont'       => $analisisPeriode?->dupont ? [
                        'net_profit_margin'    => $analisisPeriode->dupont->net_profit_margin,
                        'total_asset_turnover' => $analisisPeriode->dupont->total_asset_turnover,
                        'leverage_multiplier'  => $analisisPeriode->dupont->leverage_multiplier,
                        'roe'                  => $analisisPeriode->dupont->roe,
                    ] : null,
                ],
            ];
        })->all();

        return [
            'narasi_trend_dupont_AI' => $this->analisis?->trend?->narasi_trend_dupont_AI,
            'has_gap'                => $hasGap,
            'periode_data'           => $periodeData,
        ];
    }

    public function getCommonsizeTrend(): array
    {
        $this->loadMissing('analisis.trend');

        $dokumenList = $this->buildPeriodeQuery()
            ->with([
                'analisis.commonsize:analisis_id,hpp_persen,laba_kotor_persen,beban_lain_pajak_persen,laba_bersih_persen,aset_lancar_persen,aset_tetap_persen,liabilitas_lancar_persen,liabilitas_panjang_persen,ekuitas_persen',
            ])
            ->get()
            ->reverse()
            ->values();

        $hasGap = $dokumenList->contains(function ($dokumenPeriode) {
            return $dokumenPeriode->analisis === null || $dokumenPeriode->analisis->commonsize === null;
        });

        $periodeData = $dokumenList->map(function ($dokumenPeriode, $index) {
            $analisisPeriode = $dokumenPeriode->analisis;

            return [
                'urutan'   => $index + 1,
                'analisis' => [
                    'id'           => $analisisPeriode?->id,
                    'periode_type' => $dokumenPeriode->periode_type,
                    'tahun'        => $dokumenPeriode->tahun,
                    'quarter'      => $dokumenPeriode->quarter,
                    'bulan'        => $dokumenPeriode->bulan,
                    'commonsize'   => $analisisPeriode?->commonsize ? [
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
            'narasi_trend_commonsize_AI' => $this->analisis?->trend?->narasi_trend_commonsize_AI,
            'has_gap'                    => $hasGap,
            'periode_data'               => $periodeData,
        ];
    }

    /**
     * total_* diambil dari baris pertama koleksi neraca/labaRugi milik dokumen
     * (nilainya identik di semua baris per dokumen, redundant by design).
     */
    public function getAkunUtamaTrend(): array
    {
        $this->loadMissing('analisis.trend');

        $dokumenList = $this->buildPeriodeQuery()
            ->with(['neraca', 'labaRugi'])
            ->get()
            ->reverse()
            ->values();

        $hasGap = $dokumenList->contains(function ($dokumenPeriode) {
            return $dokumenPeriode->neraca->isEmpty() && $dokumenPeriode->labaRugi->isEmpty();
        });

        $periodeData = $dokumenList->map(function ($dokumenPeriode, $index) {
            $neraca = $dokumenPeriode->neraca->first();
            $labaRugi = $dokumenPeriode->labaRugi->first();

            return [
                'urutan'                    => $index + 1,
                'analisis'                  => [
                    'id'           => $dokumenPeriode->analisis?->id,
                    'periode_type' => $dokumenPeriode->periode_type,
                    'tahun'        => $dokumenPeriode->tahun,
                    'quarter'      => $dokumenPeriode->quarter,
                    'bulan'        => $dokumenPeriode->bulan,
                ],
                'total_pendapatan'          => $labaRugi?->total_pendapatan,
                'laba_kotor'                => $labaRugi?->laba_kotor,
                'laba_bersih_sesudah_pajak' => $labaRugi?->laba_bersih_sesudah_pajak,
                'total_asset'               => $neraca?->total_asset,
                'total_kas'                 => $neraca?->total_kas,
                'total_equitas'             => $neraca?->total_equitas,
            ];
        })->all();

        foreach ($periodeData as $i => &$data) {
            $prev = $i > 0 ? $periodeData[$i - 1] : null;
            $keys = ['total_pendapatan', 'laba_kotor', 'laba_bersih_sesudah_pajak', 'total_asset', 'total_kas', 'total_equitas'];

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
            'narasi_trend_akun_utama_AI' => $this->analisis?->trend?->narasi_trend_akun_utama_AI,
            'has_gap'      => $hasGap,
            'periode_data' => $periodeData,
        ];
    }
}
