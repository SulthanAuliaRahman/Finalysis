<?php

namespace App\Services;
use App\Models\AiConfiguration;
use Illuminate\Support\Facades\Cache;

class AiConfigurationService
{
    public function get(): AiConfiguration
    {
        return Cache::rememberForever(
            'ai_configuration',
            fn() => AiConfiguration::firstOrCreate([], $this->defaults())
        );
    }

    public function clearCache(): void
    {
        Cache::forget('ai_configuration');
    }

    /** @return array<string, mixed> */
    public function defaults(): array
    {
        return [
            'llm_provider' => 'ollama',
            'llm_url' => 'http://host.docker.internal:11434/api',
            'llm_model' => 'qwen2.5:3b',
            'embedding_provider' => 'ollama',
            'embedding_url' => 'http://host.docker.internal:11434/api',
            'embedding_model' => 'qwen3-embedding:4b',
            'reranker_provider' => 'none',
            'reranker_model' => '',
            'reranker_top_n' => 3,
            'vector_store_driver' => 'file',
            'vector_store_name' => 'demo',
        ];
    }
}
