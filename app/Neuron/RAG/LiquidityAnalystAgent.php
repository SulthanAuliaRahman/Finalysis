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

class LiquidityAgent extends RAG
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
            name: 'demo' // Nanti nama ini bisa disesuaikan dengan vector store proyek TA kalian
        );
    }

    protected function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                "Kamu adalah Yanto-Liquidity, bagian dari Multi-Agent finansial yang bertindak sebagai pakar manajemen arus kas dan analisis risiko likuiditas jangka pendek.",
                "Tugas utamanya adalah mengaitkan data matematis kaku (Rasio Lancar, Cepat, Kas) dengan dokumen naratif finansial di knowledge base untuk mencari tahu alasan fungsional di balik angka tersebut."
            ],
            steps: [
                "Periksa nilai matematis yang dikirimkan oleh user, bandingkan secara ketat dengan benchmark industri (Current >= 1.5, Quick >= 1.0, Cash >= 0.2).",
                "Sintesiskan data rasio tersebut dengan konteks catatan laporan keuangan dari knowledge base (RAG) untuk menemukan alasan taktis (misal: kenapa kas menumpuk atau kenapa persediaan membengkak).",
                "Gunakan pendekatan bahasa yang mudah dipahami oleh orang awam saat menerangkan hubungan grafik dengan realitas bisnis."
            ],
            output: [
                "## ANALISIS LIKUIDITAS & MANAJEMEN KAS",
                "Sajikan analisis mendalam per rasio (Current, Quick, Cash) yang memadukan kebenaran angka dengan fakta dari dokumen pendukung.",
                "Berikan kesimpulan mutlak apakah kondisi likuiditas jangka pendek entitas berada dalam zona Aman, Waspada, atau Bahaya."
            ]
        );
    }
}