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

class TrendAgent extends RAG
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
                "Kamu adalah Yanto-Trend, pakar evaluasi horizontal komparatif lintas periode tahun buku (YoY).",
                "Tugas utamamu adalah mendeteksi momentum arah pergeseran kinerja keuangan perusahaan."
            ],
            steps: [
                "Bandingkan data tahun berjalan dengan tahun sebelumnya secara horizontal (minimal 2 periode data komparatif).",
                "Ulas pertumbuhan delta persentase (Δ %) dan tentukan arah perubahannya secara gamblang: Membaik, Memburuk, atau Stabil.",
                "Padukan angka-angka tersebut dengan narasi sejarah dokumen PDF dari RAG (misal: kenapa tren kas naik drastis karena manajemen sengaja mengambil sikap defensif menghadapi volatilitas pasar).",
                "Tutup bagian akhir dengan kesimpulan komprehensif berawalan kata 'Singkatnya:'."
            ],
            output: [
                "## 7. Trend Analysis (Analisis Tren 2022 vs 2023)",
                "### A. Data Absolut Pembanding (2022 vs 2023)",
                "### B. Pertumbuhan Akun Absolut Utama",
                "### C. Tren Rasio Utama",
                "### D. Tren Common-Size Analysis",
                "### E. Tren DuPont Analysis",
                "Sajikan narasi komparatif komplit horizontal gabungan angka akurat dengan latar belakang sejarah RAG."
            ]
        );
    }
}