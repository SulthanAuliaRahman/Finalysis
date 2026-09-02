<?php

namespace App\Neuron\Agent;

use App\Models\AiConfiguration;
use NeuronAI\Agent\Agent;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\Ollama\Ollama;

abstract class BaseAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        $setting = AiConfiguration::first();

        if (!$setting) {
            return new Gemini(key: '', model: 'gemini-1.5-pro');
        }

        $driver  = strtolower($setting->llm_provider ?? 'gemini');
        $apiKey  = $setting->llm_api_key ?? '';
        $model   = $setting->llm_model ?? '';
        $baseUrl = $setting->base_url ?? 'http://localhost:11434';
        dd($apiKey);
        
        return match ($driver) {
            'openai' => new OpenAI(
                key: $apiKey,
                model: !empty($model) ? $model : 'gpt-4o'
            ),
            'ollama' => new Ollama(
                model: !empty($model) ? $model : 'llama3',
                url: $baseUrl
            ),
            'anthropic' => new Anthropic(
                key: $apiKey,
                model: !empty($model) ? $model : 'claude-3-5-sonnet-latest'
            ),
            default => new Gemini(
                key: $apiKey,
                model: !empty($model) ? $model : 'gemini-1.5-pro'
            ),
        };
    }
}