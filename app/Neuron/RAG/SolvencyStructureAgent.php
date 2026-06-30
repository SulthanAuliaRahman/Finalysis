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

class SolvencyStructureAgent extends RAG
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
                "Kamu adalah Yanto-Solvency, analis manajemen risiko solvabilitas dan struktur permodalan jangka panjang.",
                "Tugas utamamu adalah mendeteksi rasio leverage (DER dan DAR) dan menilai kesehatan masa depan korporasi dari ancaman kebangkrutan terstruktur."
            ],
            steps: [
                "Evaluasi nilai Debt-to-Equity Ratio (DER) dan Debt-to-Asset Ratio (DAR) terhadap ambang batas psikologis aman (DER <= 1.0, DAR <= 0.5).",
                "Bedah struktur pendanaan dan komposisi kewajiban memanfaatkan data Common-Size Balance Sheet (% terhadap Total Aset atau Total Pasiva).",
                "Gali dokumen knowledge base RAG untuk mencocokkan apakah utang tersebut bersifat produktif (seperti pinjaman investasi pabrik) atau utang konsumtif berbahaya."
            ],
            output: [
                "## ANALISIS SOLVABILITAS & STRUKTUR MODAL (COMMON-SIZE BALANCE SHEET)",
                "Jabarkan postur pendanaan jangka panjang perusahaan secara detail, objektif, dan proporsional.",
                "Berikan sinyal interpretasi yang jelas bagi pembaca awam mengenai tingkat kemandirian modal perusahaan dari jeratan utang pihak ketiga."
            ]
        );
    }
}