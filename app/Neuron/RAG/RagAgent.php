<?php

namespace App\Neuron\RAG;


use NeuronAI\RAG\RAG;
use NeuronAI\NeuronAI\Agent\SystemPrompt;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Ollama\Ollama;
use NeuronAI\RAG\Embeddings\EmbeddingsProviderInterface;
use NeuronAI\RAG\Embeddings\OllamaEmbeddingsProvider;
use NeuronAI\RAG\VectorStore\FileVectorStore;
use NeuronAI\RAG\PostProcessor\CohereRerankerPostProcessor;
use NeuronAI\RAG\PostProcessor\JinaRerankerPostProcessor;
use NeuronAI\RAG\PostProcessor\LocalAIRerankerPostProcessor;

class RagAgent extends RAG
{
    private const DEFAULT_MODELS = [
        'cohere'  => 'rerank-v3.5',
        'jina'    => 'jina-reranker-v2-base-multilingual',
        'localai' => 'cross-encoder',
    ];

    public function __construct()
    {
        $this->setPostProcessors([$this->buildReranker()]);
    }

    protected function provider(): AIProviderInterface
    {
        return new Ollama(
            url: 'http://host.docker.internal:11434/api',
            model: 'qwen3:8b',
        );
    }

    protected function embeddings(): EmbeddingsProviderInterface
    {
        return new OllamaEmbeddingsProvider(
            url: 'http://host.docker.internal:11434/api',
            model: 'qwen3-embedding:8b',
        );
    }

    protected function vectorStore(): VectorStoreInterface
    {
        return new FileVectorStore(
            directory: __DIR__,
            name: 'demo'
        );
    }

    // protected function instructions(): string
    // {
    //     return (string) new SystemPrompt(
    //         background:[
    //             "Kamu adalah analis keuangan profesional",
    //         ],
    //         steps:[
    //             "Gunakan data dari knowledge base",
    //             "Jika tidak ada data, katakan tidak tersedia",
    //             "Jangan halusinasi"
    //         ]
    //     );
    // }

    private function buildReranker(): FallbackRerankerProcessor
    {
        $config   = config('services.reranker');
        $provider = $config['provider'] ?? 'localai';
        $topN     = $config['top_n'] ?? 3;
        $model    = $config['model'] ?: (self::DEFAULT_MODELS[$provider] ?? null);

        $this->validateConfig($config, $provider, $topN);

        $inner = match ($provider) {
            'cohere'  => new CohereRerankerPostProcessor(
                key:   $config['cohere_api_key'],
                model: $model,
                topN:  (int) $topN,
            ),
            'jina'    => new JinaRerankerPostProcessor(
                key:   $config['jina_api_key'],
                model: $model,
                topN:  (int) $topN,
            ),
            'localai' => new LocalAIRerankerPostProcessor(
                key:   '',
                model: $model,
                topN:  (int) $topN,
                host:  $config['localai_url'],
            ),
        };

        return new FallbackRerankerProcessor($inner);
    }

    /**
     * Validate reranker configuration values.
     *
     * $topN is typed as mixed (not int) intentionally — .env values arrive as
     * strings, so casting to int before this check would silently coerce invalid
     * values (e.g. "abc" → 0) and hide the real error.
     */
    private function validateConfig(array $config, string $provider, mixed $topN): void
    {
        $allowed = ['cohere', 'jina', 'localai'];

        if (!in_array($provider, $allowed, true)) {
            throw new \InvalidArgumentException(
                "Invalid RERANKER_PROVIDER '{$provider}'. Allowed values: " . implode(', ', $allowed) . '.'
            );
        }

        if (!(is_numeric($topN) && (int) $topN == $topN && (int) $topN > 0)) {
            throw new \InvalidArgumentException(
                "RERANKER_TOP_N must be a positive integer, got '{$topN}'."
            );
        }

        if ($provider === 'cohere' && empty($config['cohere_api_key'])) {
            throw new \InvalidArgumentException(
                'COHERE_API_KEY is required when using the cohere provider.'
            );
        }

        if ($provider === 'jina' && empty($config['jina_api_key'])) {
            throw new \InvalidArgumentException(
                'JINA_API_KEY is required when using the jina provider.'
            );
        }

        if ($provider === 'localai' && empty($config['localai_url'])) {
            throw new \InvalidArgumentException(
                'LOCALAI_URL is required when using the localai provider.'
            );
        }
    }
}
