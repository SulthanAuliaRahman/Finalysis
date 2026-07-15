<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Analisis;

class AnalisisLikuiditasFactory extends Factory
{
    public function definition(): array
    {
        return [
            'analisis_id'   => Analisis::factory(),
            'current_ratio' => 150.00,
            'quick_ratio'   => 100.00,
            'cash_ratio'    => 20.00,
        ];
    }
}