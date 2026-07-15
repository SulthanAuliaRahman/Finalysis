<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Analisis;

class AnalisisSolvabilitasFactory extends Factory
{
    public function definition(): array
    {
        return [
            'analisis_id'    => Analisis::factory(),
            'debt_to_equity' => 65.00,
            'debt_to_asset'  => 43.00,
        ];
    }
}