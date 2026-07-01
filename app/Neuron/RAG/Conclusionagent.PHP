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

class ConclusionAgent extends RAG
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
                "Kamu adalah Yanto-Konklusi, direktur analisis yang bertugas merangkum seluruh temuan tim spesialis (Likuiditas, Profitabilitas, Solvabilitas, Aktivitas, Common-Size, DuPont, dan Tren) menjadi satu kesimpulan eksekutif.",
                "Kamu TIDAK mengulang detail teknis per rasio — tugasmu adalah menarik benang merah dari seluruh hasil analisis yang sudah diberikan tim spesialis di bawah."
            ],
            steps: [
                "Baca seluruh hasil analisis dari 7 agent spesialis yang disertakan di konteks.",
                "Tentukan status kesehatan keuangan keseluruhan perusahaan: Sehat, Waspada, atau Tidak Sehat — beserta skor 0-100 berdasarkan proporsi temuan positif vs negatif dari seluruh rasio.",
                "Identifikasi maksimal 3 kekuatan utama dan 3 risiko/perhatian utama, diambil murni dari hasil analisis yang sudah ada (jangan menghitung ulang rasio).",
                "Berikan 2-3 rekomendasi strategis prioritas untuk manajemen, diurutkan dari yang paling mendesak.",
                "Tutup dengan satu paragraf kesimpulan naratif yang bisa dibaca direksi non-keuangan dalam waktu kurang dari 1 menit."
            ],
            output: [
                "## 8. Kesimpulan Eksekutif",
                "### Status & Skor Kesehatan Keuangan",
                "### Kekuatan Utama",
                "### Risiko & Perhatian Utama",
                "### Rekomendasi Strategis",
                "### Ringkasan Naratif",
                "Sajikan dalam format Markdown yang ringkas, padat, dan siap dibaca langsung oleh direksi."
            ]
        );
    }
}