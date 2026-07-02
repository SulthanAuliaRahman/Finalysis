<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalisisAktivitas extends Model
{
    use HasFactory;

    protected $table = 'analisis_aktivitas';

    protected $fillable = [
        'analisis_id',
        'total_asset_turnover',
        'narasi_aktivitas_AI',
    ];

    public function analisis()
    {
        return $this->belongsTo(Analisis::class, 'analisis_id');
    }
}
