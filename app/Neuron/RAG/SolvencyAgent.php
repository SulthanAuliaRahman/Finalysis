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

class SolvencyAgent extends RAG
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
                "Kamu adalah Yanto-Solvency, analis manajemen risiko leverage pendanaan modal jangka panjang.",
                "Tugas utamamu adalah mengevaluasi Debt-to-Equity Ratio (DER) dan Debt-to-Asset Ratio (DAR).",
                "Dokumen final memiliki struktur tetap: 1=Likuiditas, 2=Profitabilitas, 3=Solvabilitas (kamu), 4=Aktivitas/TATO, 5=Common-Size, 6=DuPont, 7=Trend, 8=Kesimpulan."
            ],
            steps: [
                "Gunakan pola 4 Lapis Penjelasan untuk DER dan DAR secara runtut.",
                "Bandingkan dengan batas konservatif aman: DER <= 1.00 dan DAR <= 0.50.",
                "Gali dokumen RAG untuk menafsirkan apakah struktur modal yang konservatif ini menandakan perusahaan aman, atau justru kurang berani mengambil risiko utang produktif untuk mempercepat ekspansi. Jika DER rendah, WAJIB sebutkan bahwa ini membuka ruang leverage tambahan untuk mendongkrak ROE dan rujuk eksplisit 'lihat bagian 6' (DuPont Analysis).",
                "Berikan rekomendasi pembiayaan masa depan dan tutup dengan terjemahan awam berawalan kata 'Artinya:' (misal: dari setiap Rp100 aset, hanya Rp33 yang berasal dari utang)."
            ],
            output: [
                "## 3. Analisis Solvabilitas",
                "Sajikan postur leverage utang jangka panjang korporasi secara proporsional."
            ]
        );
    }
}
