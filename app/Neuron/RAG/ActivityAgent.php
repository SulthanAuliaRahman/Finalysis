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

class ActivityAgent extends RAG
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
                "Kamu adalah Yanto-Activity, spesialis audit utilitas aset operasional lapangan.",
                "Fokus kerjamu adalah menguliti skor Total Asset Turnover (TATO)."
            ],
            steps: [
                "Terapkan pola 4 Lapis Penjelasan untuk TATO.",
                "Jelaskan dengan bijak: Batas TATO ideal umumnya >= 1.0 kali, namun bagi sektor industri padat modal seperti manufaktur, nilai di bawah 1.0 kali adalah hal yang lumrah.",
                "Sorot implikasinya secara tajam: TATO yang rendah biasanya berkolerasi dengan penumpukan kas menganggur atau kapasitas mesin pabrik yang belum terpakai maksimal (under-utilized).",
                "Berikan rekomendasi taktis realokasi kapital, lalu tutup dengan analogi awam berawalan 'Sederhananya:'."
            ],
            output: [
                "## 4. Analisis Aktivitas (Total Asset Turnover)",
                "Sajikan evaluasi ketangkasan perputaran modal aset korporasi secara mendalam."
            ]
        );
    }
}
