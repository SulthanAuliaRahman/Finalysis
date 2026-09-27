<?php

namespace Database\Factories;

use App\Models\Perusahaan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PerusahaanFactory extends Factory
{
    protected $model = Perusahaan::class;

    public function definition(): array
    {
        return [
            'nama'      => $this->faker->company(),
            'profile' => $this->faker->sentence(),
            'bidang_jasa' => $this->faker->sentence(),
            'model_pendapatan' => $this->faker->sentence(),
        ];
    }
}

