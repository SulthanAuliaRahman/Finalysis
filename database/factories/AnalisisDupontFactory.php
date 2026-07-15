<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Analisis;

class AnalisisDupontFactory extends Factory
{
    public function definition(): array
    {
        return [
            'analisis_id'          => Analisis::factory(),
            'net_profit_margin'    => 10.00,
            'total_asset_turnover' => 0.80,
            'leverage_multiplier'  => 2.50,
            'roe'                  => 20.00,
        ];
    }
}