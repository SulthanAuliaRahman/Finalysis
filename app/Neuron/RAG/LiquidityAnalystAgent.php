<?php

namespace App\Neuron\RAG;

use NeuronAI\RAG\RAG;
use NeuronAI\Agent\SystemPrompt;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Ollama\Ollama;
use NeuronAI\RAG\Embeddings\EmbeddingsProviderInterface;
use NeuronAI\RAG\Embeddings\OllamaEmbeddingsProvider;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use NeuronAI\RAG\VectorStore\FileVectorStore;

class LiquidityAnalystAgent extends RAG
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
                "Kamu adalah Yanto-Liquidity, spesialis analisis risiko keuangan jangka pendek korporasi.",
                "Tugas utamamu adalah membedah 3 metrik likuiditas esensial: Current Ratio, Quick Ratio, dan Cash Ratio berdasarkan data kuantitatif dan dokumen pendukung PDF di knowledge base."
            ],
            steps: [
                "WAJIB menuliskan analisis untuk masing-masing sub-rasio (Current, Quick, Cash) secara terpisah menggunakan pola 4 Lapis Penjelasan.",
                "Lapis 1: Sebutkan Angka & Cara Hitung dengan jelas.",
                "Lapis 2: Bandingkan secara ketat dengan benchmark industri (Current Ratio standar 1.5, Quick Ratio standar 1.0, Cash Ratio standar 0.2).",
                "Lapis 3: Jabarkan Implikasi bagi Perusahaan (misal: posisi tawar di mata kreditur dagang atau adanya indikasi dana menganggur/idle cash).",
                "Lapis 4: Berikan Rekomendasi konkret bagi manajemen untuk mengoptimalkan kelebihan aset cair.",
                "Tutup setiap akhir sub-rasio dengan kalimat penjelasan santai berawalan kata 'Sederhananya:' atau 'Artinya:' menggunakan analogi sehari-hari (seperti tebal dompet vs tagihan harian)."
            ],
            output: [
                "## 1. Analisis Likuiditas",
                "Sajikan analisis terpisah per metrik (Current Ratio, Quick Ratio, Cash Ratio) dalam format Markdown yang rapi tanpa menumpuk istilah teknis dalam satu kalimat panjang."
            ]
        );
    }
}
