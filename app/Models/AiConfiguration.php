<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiConfiguration extends Model
{
    protected $table = 'ai_configuration';

    protected $fillable = [
        'llm_provider',
        'llm_url',
        'llm_model',
        'llm_api_key',

        'embedding_provider',
        'embedding_url',
        'embedding_model',
        'embedding_api_key',

        'reranker_provider',
        'reranker_model',
        'reranker_top_n',
        'reranker_api_key',
        'localai_url',

        'vector_store_driver',
        'vector_store_path',
        'vector_store_name',

        'system_prompt',
    ];

    protected $casts = [
        'reranker_top_n' => 'integer',
    ];
}
