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

class CommonsizeAgent extends BaseRagAgent
{
    protected function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                "Kamu adalah agent, pakar analisis vertikal struktural proporsional laporan keuangan.",
                "Tugas utamamu adalah membedah persentase vertikal dari Common-Size Income Statement dan Common-Size Balance Sheet.",
                "Dokumen final memiliki struktur tetap: 1=Likuiditas, 2=Profitabilitas, 3=Solvabilitas, 4=Aktivitas, 5=Common-Size (kamu), 6=DuPont, 7=Trend, 8=Kesimpulan."
            ],
            steps: [
                "Tulis TEPAT 2 paragraf mengalir (dipisah baris kosong) untuk seluruh bagian ini — TANPA bullet point, TANPA sub-heading eksplisit di badan narasi.",
                "Paragraf pertama (Laba Rugi): bedah HANYA 4 pos yang tersedia — Pendapatan Usaha=100%, HPP, Beban Lain-lain & Pajak (gabungan), hingga Laba Bersih — dengan angka persentasenya, JANGAN mengasumsikan atau memecah pos OpEx/EBIT/Bunga secara terpisah jika datanya tidak diberikan eksplisit di prompt, cukup jelaskan pos itu tergabung.",
                "Paragraf kedua (Neraca): bedah keseimbangan struktur Aktiva (Aset Lancar vs Aset Tetap) dan Pasiva (Liabilitas Lancar/Panjang vs Ekuitas). Jika proporsi ekuitas dominan, WAJIB rujuk eksplisit 'lihat bagian 3' (Solvabilitas) di dalam kalimat, lalu tutup paragraf ini dengan 1 kalimat analogi pecahan uang Rp100 hasil jualan sebagai bagian dari alur paragraf.",
                "Tulis ringkas dan padat. Jangan bertele-tele — hindari pengulangan kalimat pembuka yang mirip antar paragraf."
            ],
            output: [
                "## 5. Common-Size Analysis (Analisis Vertikal)",
                "Sajikan sebagai 2 paragraf prosa mengalir tanpa bullet: paragraf 1 = Common-Size Income Statement, paragraf 2 = Common-Size Balance Sheet."
            ]
        );
    }
}