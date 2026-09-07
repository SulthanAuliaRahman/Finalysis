<?php

namespace Database\Factories;

use App\Models\AnalisisProfitabilitas;
use App\Models\Analisis;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalisisProfitabilitasFactory extends Factory
{
    protected $model = AnalisisProfitabilitas::class;

    public function definition(): array
    {
        return [
            'analisis_id'               => Analisis::factory(),
            'net_profit_margin'         => $this->faker->randomFloat(2, 5, 25),
            'ROA'                       => $this->faker->randomFloat(2, 2, 15),
            'ROE'                       => $this->faker->randomFloat(2, 5, 30),
            'narasi_profitabilitas_AI' => $this->faker->paragraph(),
        ];
    }
}