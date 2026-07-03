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
        'cash',
        'inventory',
        'total_equity',
        'total_liabilities',
        'current_liabilities',
        'total_assets',
        'current_assets',
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
