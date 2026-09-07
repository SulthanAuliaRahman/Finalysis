<?php

namespace Database\Factories;

use App\Models\Analisis;
use App\Models\Dokumen;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalisisFactory extends Factory
{
    protected $model = Analisis::class;

    public function definition(): array
    {
        return [
            'dokumen_id'        => Dokumen::factory(),
            'ringkasan_laporan' => null,
            'status_generate'   => 'idle',
            'progress_current'  => 0,
            'progress_total'    => count(Analisis::SECTIONS),
            'error_message'     => null,
            'section_status'    => null,
        ];
    }

    public function selesai(): static
    {
        $completedStatus = [];
        foreach (Analisis::SECTIONS as $section) {
            $completedStatus[$section] = ['status' => 'selesai', 'error_message' => null];
        }

        return $this->state(fn (array $attributes) => [
            'status_generate'  => 'selesai',
            'progress_current' => count(Analisis::SECTIONS),
            'section_status'   => $completedStatus,
        ]);
    }
}