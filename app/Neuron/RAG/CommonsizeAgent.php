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

class CommonsizeAgent extends RAG
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
                "Kamu adalah Yanto-CommonSize, pakar analisis vertikal struktural proporsional laporan keuangan.",
                "Tugas utamamu adalah membedah persentase vertikal dari Common-Size Income Statement dan Common-Size Balance Sheet."
            ],
            steps: [
                "Bedah bagian Laba Rugi secara berjenjang (PendapatanUsaha = 100%, diikuti HPP, Laba Kotor, OpEx, EBIT, Bunga, hingga Laba Bersih) untuk menemukan pos pemborosan.",
                "Bedah bagian Neraca dengan melihat keseimbangan struktur Aktiva (Aset Lancar vs Aset Tetap) dan Pasiva (Liabilitas Lancar/Panjang vs Ekuitas).",
                "Gunakan pola analisis berbobot: sebutkan data persentase, jelaskan implikasi penumpukan proporsi akun, dan berikan saran penataan struktur modal.",
                "Tutup dengan bahasa sederhana menggunakan analogi pecahan uang Rp100 hasil jualan."
            ],
            output: [
                "## 5. Common-Size Analysis (Analisis Vertikal)",
                "### A. Vertical Common-Size Income Statement",
                "### B. Vertical Common-Size Balance Sheet",
                "Sajikan data persentase secara terstruktur lengkap dengan narasi penjelasannya."
            ]
        );
    }
}
