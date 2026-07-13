<?php

declare(strict_types=1);

namespace App\Neuron\Providers;

use NeuronAI\RAG\Embeddings\OpenAIEmbeddingsProvider;

class ConfigurableOpenAIEmbeddingsProvider extends OpenAIEmbeddingsProvider
{
    public function __construct(string $key, string $model, ?string $baseUrl = null)
    {
        if (filled($baseUrl)) {
            $this->baseUri = rtrim($baseUrl, '/');
        }

        parent::__construct($key, $model);
    }
}
