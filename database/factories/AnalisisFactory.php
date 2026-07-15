<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Perusahaan;

class AnalisisFactory extends Factory
{
    public function definition(): array
    {
        return [
            'perusahaan_id' => Perusahaan::factory(),
            'periode_type'  => 'annual',
            'tahun'         => $this->faker->numberBetween(2018, 2024),
            'quarter'       => null,
            'bulan'         => null,
            'status'        => 'belum dihitung',
        ];
    }

    public function quarterly(int $tahun, int $quarter): static
    {
        return $this->state([
            'periode_type' => 'quarterly',
            'tahun'        => $tahun,
            'quarter'      => $quarter,
            'bulan'        => null,
        ]);
    }
}