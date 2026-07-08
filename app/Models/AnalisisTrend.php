<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnalisisTrend extends Model
{
    use HasFactory;

    protected $table = 'analisis_trend';

    protected $fillable = [
        'analisis_id',
        'narasi_trend_akun_utama_AI',
        'narasi_trend_rasio_AI',
        'narasi_trend_dupont_AI',
        'narasi_trend_commonsize_AI',
        'narasi_trend_arus_kas_AI',
    ];

    public function analisis()
    {
        return $this->belongsTo(Analisis::class, 'analisis_id');
    }

}
