<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PerusahaanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama'      => $this->faker->company(),
            'sektor'    => $this->faker->randomElement(['Manufaktur', 'Jasa', 'Dagang']),
            'deskripsi' => $this->faker->sentence(),
        ];
    }
}