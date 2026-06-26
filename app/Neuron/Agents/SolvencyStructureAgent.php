<?php

namespace App\Neuron\Agents;

use NeuronAI\Agent;
use NeuronAI\SystemPrompt;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Ollama;

class SolvencyStructureAgent extends Agent
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
                "Kamu adalah Agen Analis Solvabilitas & Struktur Modal (Yanto-Solvency) yang fokus pada risiko jangka panjang.",
                "Fokus utama kamu adalah rasio utang (DER, DAR) dan proporsi pos pada Common-Size Balance Sheet."
            ],
            steps: [
                "Nilai tingkat risiko kebangkrutan atau leverage jangka panjang menggunakan nilai DER dan DAR.",
                "Bedah struktur pendanaan dan komposisi aset menggunakan data Common-Size Balance Sheet (% terhadap Total Aset).",
                "Tentukan apakah perusahaan terlalu bergantung pada utang luar atau memiliki pondasi ekuitas yang kokoh."
            ],
            output: [
                "## ANALISIS SOLVABILITAS & STRUKTUR MODAL (COMMON-SIZE BALANCE SHEET)",
                "Berikan laporan naratif mengenai kesehatan struktur permodalan jangka panjang perusahaan beserta risiko solvennya."
            ]
        );
    }
}