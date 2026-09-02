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
        'roe_dupont',
        'narasi_dupont_AI',
    ];

    public function analisis()
    {
        return $this->belongsTo(Analisis::class, 'analisis_id');
    }
}