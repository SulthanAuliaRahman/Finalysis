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
            'sektor'    => $this->faker->randomElement(['Teknologi', 'Keuangan', 'Manufaktur', 'Kesehatan', 'Ritel']),
            'deskripsi' => $this->faker->sentence(),
        ];
    }
}