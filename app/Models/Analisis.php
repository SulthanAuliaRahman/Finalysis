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

    // Accessor periode yang konsisten dengan model Dokumen
    public function getPeriodeAttribute()
    {
        return match ($this->periode_type) {
            'annual' => (string) $this->tahun,
            'quarterly' => "Q{$this->quarter} {$this->tahun}",
            'monthly' => [
                1 => "Januari",
                2 => "Februari",
                3 => "Maret",
                4 => "April",
                5 => "Mei",
                6 => "Juni",
                7 => "Juli",
                8 => "Agustus",
                9 => "September",
                10 => "Oktober",
                11 => "November",
                12 => "Desember",
            ][$this->bulan] . " " . $this->tahun,
            default => '-',
        };
    }
}
