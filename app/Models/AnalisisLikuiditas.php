<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalisisLikuiditas extends Model
{
    use HasFactory;

    protected $table = 'analisis_likuiditas';

    protected $fillable = [
        'analisis_id',
        'current_ratio',
        'quick_ratio',
        'cash_ratio',
        'narasi_likuiditas_AI',
    ];

    public function analisis()
    {
        return $this->belongsTo(Analisis::class, 'analisis_id');
    }
}
