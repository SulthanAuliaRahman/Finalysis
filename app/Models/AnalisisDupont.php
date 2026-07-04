<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnalisisDupont extends Model
{
    use HasFactory;

    protected $table = 'analisis_dupont';

    protected $fillable = [
        'analisis_id',
        'net_profit_margin',
        'total_asset_turnover',
        'leverage_multiplier',
        'roe',
        'narasi_dupont_AI',
    ];

    public function analisis()
    {
        return $this->belongsTo(Analisis::class, 'analisis_id');
    }
}