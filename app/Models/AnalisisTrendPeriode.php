<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnalisisTrendPeriode extends Model
{
    use HasFactory;

    protected $table = 'analisis_trend_periode';

    protected $fillable = [
        'analisis_trend_id',
        'analisis_id',
        'urutan',
        'pendapatan',
        'laba_kotor',
        'laba_bersih',
        'total_assets',
        'kas_setara_kas',
        'total_equity',
        'net_cash_flow',
        'growth_pendapatan',
        'growth_laba_kotor',
        'growth_laba_bersih',
        'growth_total_assets',
        'growth_kas_setara_kas',
        'growth_total_equity',
        'growth_net_cash_flow',
    ];

    public function trend()
    {
        return $this->belongsTo(AnalisisTrend::class, 'analisis_trend_id');
    }

    // Periode historis yang dirujuk — dari sini kita ambil rasio (CR, ROE, dst)
    // tanpa perlu simpan ulang, cukup eager-load relasi ini.
    public function analisis()
    {
        return $this->belongsTo(Analisis::class, 'analisis_id');
    }
}