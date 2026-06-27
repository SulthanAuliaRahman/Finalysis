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
        'pendapatan',
        'laba_kotor',
        'laba_bersih',
        'found_at',
    ];

    protected $casts = [
        'found_at' => 'array',
    ];

    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class);
    }
}
