<?php

namespace Database\Factories;

use App\Models\AnalisisAktivitas;
use App\Models\Analisis;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalisisAktivitasFactory extends Factory
{
    protected $model = AnalisisAktivitas::class;

    public function definition(): array
    {
        return [
            'analisis_id'             => Analisis::factory(),
            'total_asset_turnover'     => $this->faker->randomFloat(2, 0.5, 2),
            'working_capital_turnover' => $this->faker->randomFloat(2, 1, 5),
            'fixed_asset_turnover'     => $this->faker->randomFloat(2, 1, 4),
            'narasi_aktivitas_AI'      => $this->faker->paragraph(),
        ];
    }
}