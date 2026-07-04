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
        'is_data_ilustratif',
        'narasi_trend_AI',
        'narasi_rasio_AI',
        'narasi_dupont_AI',
        'narasi_commonsize_AI',
    ];

    protected $casts = [
        'is_data_ilustratif' => 'boolean',
    ];

    public function analisis()
    {
        return $this->belongsTo(Analisis::class, 'analisis_id');
    }

    // Titik-titik data periode, diurutkan kronologis
    public function periodeData()
    {
        return $this->hasMany(AnalisisTrendPeriode::class, 'analisis_trend_id')->orderBy('urutan');
    }
}