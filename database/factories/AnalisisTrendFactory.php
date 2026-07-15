<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Analisis;

class AnalisisTrendFactory extends Factory
{
    public function definition(): array
    {
        return [
            'analisis_id' => Analisis::factory(),
        ];
    }
}