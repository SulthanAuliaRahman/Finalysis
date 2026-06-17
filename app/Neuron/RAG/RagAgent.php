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

class RagAgent extends RAG
{
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


}
