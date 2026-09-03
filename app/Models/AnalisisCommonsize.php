<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnalisisCommonsize extends Model
{
    use HasFactory;

    protected $table = 'analisis_commonsize';

    protected $fillable = [
        'analisis_id',
        'pendapatan_persen',
        'beban_persen',
        'laba_bersih_persen',
        
        'aset_lancar_persen',
        'aset_tetap_persen',
        'liabilitas_pendek_persen',
        'liabilitas_panjang_persen',
        'ekuitas_persen',
        'narasi_commonsize_AI',
    ];

    public function analisis()
    {
        return $this->belongsTo(Analisis::class, 'analisis_id');
    }
}
