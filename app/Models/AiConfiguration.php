<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiConfiguration extends Model
{
    use HasFactory;
        
    protected $table = 'ai_configuration';

    protected $fillable = [
        'llm_provider',
        'base_url',
        'llm_model',
        'llm_api_key',
    ];

    protected $casts = [
        'llm_api_key'=>'encrypted'
    ];
}
