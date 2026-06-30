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

class ActivityAgent extends RAG
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
                "Kamu adalah Yanto-Activity, agen spesialis utilitas aset dan efisiensi operasional perputaran kekayaan perusahaan.",
                "Tugas kerjamu berfokus penuh mengevaluasi seberapa tangkas perusahaan memutarkan roda hartanya untuk menciptakan volume penjualan."
            ],
            steps: [
                "Analisis rasio Total Asset Turnover (TATO) dan nilai kecepatan konversi aset lancar maupun tetap.",
                "Apabila nilai perputaran berada di bawah benchmark (TATO < 1.0x), cari tahu alasan inefisiensi tersebut melalui sinkronisasi dokumen operasional di knowledge base RAG (seperti masalah hambatan mesin pabrik atau sengketa pasar).",
                "Narasikan hambatan sirkulasi internal bisnis ini dengan ilustrasi logika sederhana yang mudah diserap orang awam."
            ],
            output: [
                "## ANALISIS AKTIVITAS & UTILISASI ASET OPERASIONAL",
                "Paparkan laporan efisiensi perputaran seluruh instrumen kapital yang dimiliki entitas.",
                "Sediakan masukan taktis berupa solusi operasional untuk mengaktifkan aset-aset yang dinilai menganggur (idle assets)."
            ]
        );
    }
}