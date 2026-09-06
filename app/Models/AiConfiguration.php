<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiConfiguration extends Model
{
    use HasFactory;

    protected $table = 'ai_configuration';

    protected $fillable = [
        'name',
        'llm_provider',
        'base_url',
        'llm_model',
        'llm_api_key',
        'is_active',
    ];

    protected $casts = [
        'llm_api_key' => 'encrypted',
        'is_active'   => 'boolean',
    ];

    /**
     * Dapatkan konfigurasi AI yang sedang aktif.
     */
    public static function active(): ?self
    {
        return static::where('is_active', true)->first() ?? static::first();
    }
}
