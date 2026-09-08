<?php

namespace Database\Seeders;

use App\Models\AiConfiguration;
use Illuminate\Database\Seeder;

class AiConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AiConfiguration::create([
            'name' => 'OpenAI GPT-4o',
            'is_active' => false,
            'llm_provider' => 'openai',
            'base_url' => 'https://api.openai.com/v1',
            'llm_model' => 'gpt-4o',
            'llm_api_key' => 'sk-test-openai-key',
        ]);

        AiConfiguration::create([
            'name' => 'Google Gemini',
            'is_active' => false,
            'llm_provider' => 'gemini',
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'llm_model' => 'gemini-2.0-flash',
            'llm_api_key' => 'test-gemini-api-key',
        ]);

        AiConfiguration::create([
            'name' => 'Anthropic Claude',
            'is_active' => false,
            'llm_provider' => 'anthropic',
            'base_url' => 'https://api.anthropic.com/v1',
            'llm_model' => 'claude-3-5-sonnet-20241022',
            'llm_api_key' => 'sk-ant-test-anthropic-key',
        ]);

        AiConfiguration::create([
            'name' => 'Ollama Local',
            'is_active' => true,
            'llm_provider' => 'ollama',
            'base_url' => 'http://host.docker.internal:11434/api',
            'llm_model' => 'qwen2.5:3b',
            'llm_api_key' => null,
        ]);
    }
}