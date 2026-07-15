<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Analisis;

class AnalisisProfitabilitasFactory extends Factory
{
    public function definition(): array
    {
        return [
            'analisis_id'       => Analisis::factory(),
            'ROE'               => 15.00,
            'ROA'               => 8.00,
            'net_profit_margin' => 10.00,
        ];
    }
}