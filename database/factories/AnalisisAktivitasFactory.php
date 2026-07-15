<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Analisis;

class AnalisisAktivitasFactory extends Factory
{
    public function definition(): array
    {
        return [
            'analisis_id'          => Analisis::factory(),
            'total_asset_turnover' => 0.80,
        ];
    }
}