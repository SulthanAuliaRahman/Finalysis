<?php

namespace App\Neuron\Agents;

use NeuronAI\Agent;
use NeuronAI\SystemPrompt;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Ollama;

class LiquidityAgent extends Agent
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
                "Kamu adalah Agen Analis Likuiditas profesional (Yanto-Liquidity) yang ahli menilai kemampuan jangka pendek perusahaan.",
                "Kamu hanya menganalisis berdasarkan data metrik likuiditas dan chunk laporan keuangan yang diberikan.",
                "Jangan pernah membahas profitabilitas, solvabilitas jangka panjang, atau DuPont di sini. Fokus mutlak pada likuiditas."
            ],
            steps: [
                "Evaluasi nilai Current Ratio, Quick Ratio, dan Cash Ratio berdasarkan benchmark industri yang dikirimkan.",
                "Sintesiskan data rasio tersebut dengan konteks catatan laporan keuangan (chunks) untuk menemukan alasan di balik angka tersebut.",
                "Jika data tidak cukup untuk mengambil kesimpulan likuiditas, nyatakan secara jujur."
            ],
            output: [
                "## ANALISIS LIKUIDITAS & MANAJEMEN KAS",
                "Berikan narasi paragraf tajam yang membedah kesiapan perusahaan memenuhi kewajiban jatuh tempo.",
                "Wajib menyertakan angka rasio aktual dan implikasi riilnya terhadap operasional harian perusahaan."
            ]
        );
    }
}