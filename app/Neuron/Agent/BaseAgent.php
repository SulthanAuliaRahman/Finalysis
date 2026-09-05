<?php

namespace App\Neuron\Agent;

use App\Models\AiConfiguration;
use App\Services\AiConfigurationService;
use NeuronAI\Agent\Agent;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\Ollama\Ollama;
use Illuminate\Support\Facades\Log;

abstract class BaseAgent extends Agent
{
    protected ?AiConfiguration $currentConfig = null;

    protected function provider(): AIProviderInterface
    {
        $service = app(AiConfigurationService::class);
        $this->currentConfig = $service->get();

        return $this->buildProvider($this->currentConfig);
    }

    /**
     * Build AI provider instance dari AiConfiguration.
     */
    protected function buildProvider(AiConfiguration $setting): AIProviderInterface
    {
        $driver  = strtolower($setting->llm_provider ?? 'gemini');
        $apiKey  = $setting->llm_api_key ?? '';
        $model   = $setting->llm_model ?? '';
        $baseUrl = $setting->base_url ?? 'http://localhost:11434';

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

    /**
     * Override chat untuk menangani rate limit dan auto-switch.
     * Jika terjadi 429/rate limit, tandai config saat ini lalu retry dengan config berikutnya.
     */
    public function chat(mixed $message): mixed
    {
        $maxRetries = 2;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                // Catat request
                if ($this->currentConfig) {
                    $this->currentConfig->recordRequest();
                }

                return parent::chat($message);

            } catch (\Throwable $e) {
                if ($this->isRateLimitError($e) && $attempt < $maxRetries) {
                    Log::warning("Rate limit terdeteksi pada config [{$this->currentConfig?->name}]. Mencoba auto-switch... (attempt {$attempt}/{$maxRetries})");

                    $service = app(AiConfigurationService::class);
                    $nextConfig = $service->handleRateLimit($this->currentConfig);

                    if ($nextConfig) {
                        // Re-initialize provider dengan config baru
                        $this->currentConfig = $nextConfig;
                        $this->provider = $this->buildProvider($nextConfig);
                        continue;
                    }

                    // Tidak ada config pengganti
                    throw new \RuntimeException(
                        'Semua konfigurasi AI sedang rate-limited. Coba lagi nanti.',
                        429,
                        $e
                    );
                }

                // Bukan rate limit error, atau sudah habis retry → lempar exception asli
                throw $e;
            }
        }

        throw new \RuntimeException('Gagal mengirim request ke AI setelah beberapa percobaan.');
    }

    /**
     * Cek apakah exception merupakan rate limit error (HTTP 429).
     */
    protected function isRateLimitError(\Throwable $e): bool
    {
        // Cek HTTP status code 429
        if (method_exists($e, 'getCode') && $e->getCode() === 429) {
            return true;
        }

        // Cek pesan error yang umum untuk rate limit
        $message = strtolower($e->getMessage());
        $rateLimitPatterns = [
            'rate limit',
            'rate_limit',
            'too many requests',
            '429',
            'quota exceeded',
            'resource_exhausted',
            'rate-limited',
        ];

        foreach ($rateLimitPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }
}