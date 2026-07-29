<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LabaRugi extends Model
{
    use HasFactory;

    protected $table = 'laba_rugi';

    protected $fillable = [
        'dokumen_id',
        'nama_akun',
        'kelompok_akun',
        'nilai_akun',
        'total_pendapatan_operasional',
        'total_beban_hpp',
        'total_beban_operasional',
        'total_pendapatan_lainnya',
        'total_beban_lainya',
        'total_biaya_pajak',
        'total_pendapatan',
        'laba_kotor',
        'laba_usaha',
        'laba_bersih_sebelum_pajak',
        'laba_bersih_sesudah_pajak',
        'found_at',
    ];

    protected $casts = [
        'nilai_akun' => 'decimal:2',
        'total_pendapatan_operasional' => 'decimal:2',
        'total_beban_hpp' => 'decimal:2',
        'total_beban_operasional' => 'decimal:2',
        'total_pendapatan_lainnya' => 'decimal:2',
        'total_beban_lainya' => 'decimal:2',
        'total_biaya_pajak' => 'decimal:2',
        'total_pendapatan' => 'decimal:2',
        'laba_kotor' => 'decimal:2',
        'laba_usaha' => 'decimal:2',
        'laba_bersih_sebelum_pajak' => 'decimal:2',
        'laba_bersih_sesudah_pajak' => 'decimal:2',
        'found_at' => 'array',
    ];

    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class);
    }
}
