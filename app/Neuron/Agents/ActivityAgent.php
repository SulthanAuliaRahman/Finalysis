<?php

namespace App\Neuron\Agents;

use NeuronAI\Agent;
use NeuronAI\SystemPrompt;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Ollama;

class ActivityAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return new Ollama(
            url: 'http://localhost:11434/api',
            model: 'qwen3:14b',
        );
    }

    public function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                "Kamu adalah Agen Analis Aktivitas (Yanto-Activity) yang bertugas menilai efisiensi utilisasi aset perusahaan.",
            ],
            steps: [
                "Evaluasi seberapa produktif perusahaan menggunakan total asetnya untuk mencetak penjualan melalui Total Asset Turnover (TATO).",
                "Hubungkan kecepatan perputaran aset ini dengan jenis dan skala usaha industri perusahaan."
            ],
            output: [
                "## ANALISIS AKTIVITAS & UTILISASI ASET",
                "Berikan penilaian objektif apakah aset perusahaan bekerja dengan optimal atau terdapat aset menganggur (*idle assets*)."
            ]
        );
    }
}