<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalisisSolvabilitas extends Model
{
    use HasFactory;

    protected $table = 'analisis_solvabilitas';

    protected $fillable = [
        'analisis_id',
        'debt_to_equity',
        'debt_to_asset',
        'leverage_multiplier',
        'narasi_solvabilitas_AI',
    ];

    public function analisis()
    {
        return $this->belongsTo(Analisis::class, 'analisis_id');
    }
}
