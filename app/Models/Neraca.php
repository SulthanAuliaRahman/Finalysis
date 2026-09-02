<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Neraca extends Model
{
    use HasFactory,HasUuids;

    protected $table = 'neraca';

    protected $fillable = [
        'dokumen_id',
        'total_kas_setara_kas',
        'total_asset_lancar',
        'total_asset_tetap',
        'total_asset',
        'total_liabilities_pendek',
        'total_liabilities_panjang',
        'total_liabilities',
        'total_equitas',
    ];

    protected $casts = [
        'total_kas_setara_kas' => 'decimal:2',
        'total_asset_lancar' => 'decimal:2',
        'total_asset_tetap' => 'decimal:2',
        'total_asset' => 'decimal:2',
        'total_liabilities_pendek' => 'decimal:2',
        'total_liabilities_panjang' => 'decimal:2',
        'total_liabilities' => 'decimal:2',
        'total_equitas' => 'decimal:2',
    ];

    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class);
    }
    
}
