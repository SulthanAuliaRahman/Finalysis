<?php

namespace Database\Factories;

use App\Models\AnalisisTrend;
use App\Models\Analisis;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalisisTrendFactory extends Factory
{
    protected $model = AnalisisTrend::class;

    public function definition(): array
    {
        return [
            'analisis_id'                 => Analisis::factory(),
            'narasi_trend_akun_utama_AI'  => $this->faker->paragraph(),
            'narasi_trend_rasio_AI'       => $this->faker->paragraph(),
            'narasi_trend_dupont_AI'      => $this->faker->paragraph(),
            'narasi_trend_commonsize_AI' => $this->faker->paragraph(),
        ];
    }
}