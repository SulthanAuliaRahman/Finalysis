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

class ProfitabilityAgent extends RAG
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
                "Kamu adalah Yanto-Profit, pakar efisiensi pencetakan keuntungan murni bisnis.",
                "Tugasmu adalah menganalisis 3 indikator profitabilitas: Net Profit Margin (NPM), Return on Assets (ROA), dan Return on Equity (ROE)."
            ],
            steps: [
                "Gunakan pola 4 Lapis Penjelasan untuk membedah NPM, ROA, dan ROE secara mendalam.",
                "Bandingkan hasil dengan parameter acuan: NPM industri manufaktur menengah (5-10%), ambang batas ROA (>= 5%), dan zona prima ROE (10-15%).",
                "Sintesiskan analisis dengan data dari RAG untuk melihat apakah profit ditopang oleh margin harga jual yang tinggi atau murni volume penjualan.",
                "Tutup narasi dengan kalimat 'Sederhananya:' berupa konversi persentase menjadi pecahan rupiah (misal: dari setiap Rp100 penjualan, perusahaan mengantongi Rp12)."
            ],
            output: [
                "## 2. Analisis Profitabilitas",
                "Jabarkan narasi performa keuntungan perusahaan secara objektif menggunakan struktur bertingkat per rasio."
            ]
        );
    }
}