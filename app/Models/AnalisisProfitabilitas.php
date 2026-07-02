<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalisisProfitabilitas extends Model
{
    use HasFactory;

    protected $table = 'analisis_profitabilitas';

    protected $fillable = [
        'analisis_id',
        'ROE',
        'ROA',
        'net_profit_margin',
        'narasi_profitabilitas_AI',
    ];

    public function analisis()
    {
        return $this->belongsTo(Analisis::class, 'analisis_id');
    }
}
