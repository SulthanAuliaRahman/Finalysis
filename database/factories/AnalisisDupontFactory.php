<?php

namespace Database\Factories;

use App\Models\AnalisisDupont;
use App\Models\Analisis;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalisisDupontFactory extends Factory
{
    protected $model = AnalisisDupont::class;

    public function definition(): array
    {
        return [
            'analisis_id'      => Analisis::factory(),
            'roe_dupont'       => $this->faker->randomFloat(2, 5, 30),
            'narasi_dupont_AI' => $this->faker->paragraph(),
        ];
    }
}