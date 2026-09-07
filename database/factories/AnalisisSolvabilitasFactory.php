<?php

namespace Database\Factories;

use App\Models\AnalisisSolvabilitas;
use App\Models\Analisis;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalisisSolvabilitasFactory extends Factory
{
    protected $model = AnalisisSolvabilitas::class;

    public function definition(): array
    {
        return [
            'analisis_id'            => Analisis::factory(),
            'debt_to_equity'         => $this->faker->randomFloat(2, 0.2, 1.5),
            'debt_to_asset'          => $this->faker->randomFloat(2, 20, 60),
            'leverage_multiplier'    => $this->faker->randomFloat(2, 1, 2.5),
            'narasi_solvabilitas_AI' => $this->faker->paragraph(),
        ];
    }
}