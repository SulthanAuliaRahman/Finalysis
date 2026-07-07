<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArusKas extends Model
{
    use HasFactory;

    protected $table = 'arus_kas';

    protected $fillable = [
        'dokumen_id',
        'cash_flow_from_operations',
        'cash_flow_from_investing',
        'cash_flow_from_financing',
        'kas_masuk',
        'kas_keluar',
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
