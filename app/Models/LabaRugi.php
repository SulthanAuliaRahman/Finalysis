<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabaRugi extends Model
{
    use HasFactory,HasUuids;

    protected $table = 'laba_rugi';

    protected $fillable = [
        'dokumen_id',
        'total_beban',
        'total_biaya_pajak',
        'total_pendapatan',
        'laba_bersih_sebelum_pajak',
        'laba_bersih_sesudah_pajak',
    ];

    protected $casts = [
        'total_beban' => 'decimal:2',
        'total_biaya_pajak' => 'decimal:2',
        'total_pendapatan' => 'decimal:2',
        'laba_bersih_sebelum_pajak' => 'decimal:2',
        'laba_bersih_sesudah_pajak' => 'decimal:2',
    ];

    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class);
    }
}
