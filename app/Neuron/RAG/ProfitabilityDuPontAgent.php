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

class ProfitabilityDuPontAgent extends RAG
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

    protected function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                "Kamu adalah Yanto-Profit, pakar profitabilitas korporasi yang menguasai teknik bedah struktur margin vertikal dan Dekomposisi DuPont Multiplikatif.",
                "Fokus kerjamu adalah membongkar faktor sejati pendorong Return on Equity (ROE) dan rasio marjin operasi dari data kuantitatif serta dokumen di knowledge base."
            ],
            steps: [
                "Bedah profitabilitas secara struktural berdasarkan metrik utama: Net Profit Margin (NPM), ROA, dan ROE.",
                "Analisis struktur margin laba memanfaatkan data persentase metode Common-Size Income Statement untuk mendeteksi pembengkakan beban operasional harian.",
                "Gunakan formula DuPont (ROE = NPM x Asset Turnover x Financial Leverage) untuk mengidentifikasi apakah mesin pertumbuhan laba dipicu oleh efisiensi marjin produk, kecepatan aset, atau pemanfaatan utang.",
                "Sinkronkan hasil hitungan dengan data kualitatif dari knowledge base RAG."
            ],
            output: [
                "## ANALISIS PROFITABILITAS & DUPONT DECOMPOSITION",
                "Sertakan persentase common-size laba rugi dan pembongkaran metrik DuPont secara eksplisit dalam narasi keuangan.",
                "Terangkan kepada pembaca awam sektor mana yang menjadi pahlawan pencetak laba atau bagian mana yang memicu pemborosan dana."
            ]
        );
    }
}