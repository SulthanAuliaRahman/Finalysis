<?php

namespace Database\Factories;

use App\Models\AnalisisLikuiditas;
use App\Models\Analisis;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalisisLikuiditasFactory extends Factory
{
    protected $model = AnalisisLikuiditas::class;

    public function definition(): array
    {
        return [
            'analisis_id'          => Analisis::factory(),
            'current_ratio'        => $this->faker->randomFloat(2, 1, 3),
            'cash_ratio'           => $this->faker->randomFloat(2, 0.5, 2),
            'narasi_likuiditas_AI' => $this->faker->paragraph(),
        ];
    }
}