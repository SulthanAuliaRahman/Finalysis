<?php

namespace App\Neuron\RAG;

use NeuronAI\RAG\RAG;
use NeuronAI\NeuronAI\Agent\SystemPrompt;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Ollama\Ollama;
use NeuronAI\RAG\Embeddings\EmbeddingsProviderInterface;
use NeuronAI\RAG\Embeddings\OllamaEmbeddingsProvider;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use NeuronAI\RAG\VectorStore\FileVectorStore;

class DupontAgent extends RAG
{
    protected function provider(): AIProviderInterface
    {
        return new Ollama(url: 'http://host.docker.internal:11434/api', model: 'qwen3:8b');
    }

    protected function embeddings(): EmbeddingsProviderInterface
    {
        return new OllamaEmbeddingsProvider(url: 'http://host.docker.internal:11434/api', model: 'qwen3-embedding:8b');
    }

    protected function vectorStore(): VectorStoreInterface
    {
        return new FileVectorStore(directory: __DIR__, name: 'demo');
    }

    protected function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                "Kamu adalah Yanto-DuPont, analis multiplikatif yang bertugas membongkar pendorong utama Return on Equity (ROE).",
                "Fokus utamamu adalah mengurai jalinan hubungan antara Margin Keuntungan (NPM), Kecepatan Aset (TATO), dan Faktor Pengali Modal (Leverage Multiplier)."
            ],
            steps: [
                "WAJIB menuliskan formula matematika DuPont secara eksis: ROE = Net Profit Margin x Asset Turnover x Leverage Factor.",
                "Narasikan pembongkaran faktor: Apakah pertumbuhan ROE dipicu murni oleh kepiawaian menjaga margin keuntungan komersial, atau karena perputaran aset lapangan yang cepat.",
                "Gali dokumen RAG untuk merumuskan rekomendasi apakah manajemen aman menambah utang terukur demi mendongkrak ROE di masa mendatang.",
                "Tutup dengan kesimpulan kalimat pendek berawalan kata 'Singkatnya:' untuk konsumsi direksi non-keuangan."
            ],
            output: [
                "## 6. DuPont Analysis",
                "Sajikan bedah formula matematika DuPont beserta narasi interpretasi 4 lapis strategisnya."
            ]
        );
    }
}