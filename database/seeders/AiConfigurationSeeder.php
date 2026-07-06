<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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

            'llm_provider' => env('LLM_PROVIDER'),

            'llm_url' => env('LLM_URL'),

            'llm_model' => env('LLM_MODEL'),

            'embedding_provider' => env('EMBEDDING_PROVIDER'),

            'embedding_url' => env('EMBEDDING_URL'),

            'embedding_model' => env('EMBEDDING_MODEL'),

            'reranker_provider' => env('RERANKER_PROVIDER'),

            'reranker_model' => env('RERANKER_MODEL'),

            'reranker_top_n' => env('RERANKER_TOP_N'),

            'localai_url' => env('LOCALAI_URL'),

        ]);
    }
}
