<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Neraca extends Model
{
    use HasFactory;

    protected $table = 'neraca';

    protected $fillable = [
        'dokumen_id',
        'nama_akun',
        'kelompok_akun',
        'nilai_akun',
        'total_kas',
        'total_asset_lancar',
        'total_asset_tetap',
        'total_asset',
        'total_liabilities_pendek',
        'total_liabilities_panjang',
        'total_liabilities',
        'total_equitas',
        'found_at',
    ];

    protected $casts = [
        'nilai_akun' => 'decimal:2',
        'total_kas' => 'decimal:2',
        'total_asset_lancar' => 'decimal:2',
        'total_asset_tetap' => 'decimal:2',
        'total_asset' => 'decimal:2',
        'total_liabilities_pendek' => 'decimal:2',
        'total_liabilities_panjang' => 'decimal:2',
        'total_liabilities' => 'decimal:2',
        'total_equitas' => 'decimal:2',
        'found_at' => 'array',
    ];

    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class);
    }
}
