<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AiConfiguration;

class AiConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AiConfiguration::create([
            'llm_provider'       => 'openai',
            'llm_url'            => 'https://api.openai.com/v1',
            'llm_model'          => 'gpt-5.5',

            'embedding_provider' => 'openai',
            'embedding_url'      => 'https://api.openai.com/v1',
            'embedding_model'    => 'text-embedding-3-small',

            'reranker_provider'  => 'jina',
            'reranker_model'     => 'jina-reranker-v2-base-multilingual',
            'reranker_top_n'     => 5,

            'localai_url'        => 'http://localhost:8080',
        ]);
    }
}