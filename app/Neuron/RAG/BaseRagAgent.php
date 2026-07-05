<?php

namespace App\Neuron\RAG;

use NeuronAI\RAG\RAG;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\RAG\Embeddings\EmbeddingsProviderInterface;
use NeuronAI\RAG\VectorStore\FileVectorStore;

use NeuronAI\Providers\Ollama\Ollama;
use NeuronAI\RAG\Embeddings\OllamaEmbeddingsProvider;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\RAG\Embeddings\GeminiEmbeddingsProvider;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\RAG\Embeddings\OpenAIEmbeddingsProvider;

abstract class BaseRagAgent extends RAG
{
    protected string $providerName;
    protected string $apiKey;
    protected string $aiModel;

    protected string $embeddingProviderName;
    protected ?string $embeddingApiKey;
    protected string $embeddingModel;

    public function __construct(
        ?string $providerName = null,
        ?string $apiKey = null,
        ?string $aiModel = null,
        ?string $embeddingProviderName = null,
        ?string $embeddingApiKey = null,
        ?string $embeddingModel = null
    ) {
        $this->providerName   = $providerName   ?? 'gemini';
        $this->apiKey         = $apiKey         ?? '#';
        $this->aiModel        = $aiModel        ?? 'gemini-3.5-flash';

        $this->embeddingProviderName = $embeddingProviderName ?? 'ollama';
        $this->embeddingApiKey = $embeddingApiKey ?? $this->apiKey;
        $this->embeddingModel = $embeddingModel ?? 'qwen3-embedding:8b';

        parent::__construct();
    }

    protected function provider(): AIProviderInterface
    {
        return match (strtolower($this->providerName)) {
            'gemini' => new Gemini(
                key: $this->apiKey,
                model: $this->aiModel
            ),

            'openai' => new OpenAI(
                key: $this->apiKey,
                model: $this->aiModel
            ),

            default => new Ollama(
                url: 'http://host.docker.internal:11434/api',
                model: $this->aiModel
            ),
        };
    }

    protected function embeddings(): EmbeddingsProviderInterface
    {
        return match (strtolower($this->embeddingProviderName)) {
            'gemini' => new GeminiEmbeddingsProvider(
                key: $this->embeddingApiKey,
                model: $this->embeddingModel
            ),

            'openai' => new OpenAIEmbeddingsProvider(
                key: $this->embeddingApiKey,
                model: $this->embeddingModel
            ),

            default => new OllamaEmbeddingsProvider(
                url: 'http://host.docker.internal:11434/api',
                model: $this->embeddingModel
            ),
        };
    }

    protected function vectorStore(): VectorStoreInterface
    {
        return new FileVectorStore(
            directory: __DIR__,
            name: 'demo'
        );
    }

}
