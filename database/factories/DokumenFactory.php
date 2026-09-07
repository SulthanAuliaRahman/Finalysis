<?php

namespace Database\Factories;

use App\Models\Dokumen;
use App\Models\Perusahaan;
use Illuminate\Database\Eloquent\Factories\Factory;

class DokumenFactory extends Factory
{
    protected $model = Dokumen::class;

    public function definition(): array
    {
        return [
            'perusahaan_id' => Perusahaan::factory(),
            'nama_file'     => $this->faker->word() . '.xlsx',
            'storage_path'  => 'dokumen-import/' . $this->faker->uuid() . '.xlsx',
            'periode_type'  => 'annual',
            'tahun'         => 2023,
            'quarter'       => null,
            'bulan'         => null,
        ];
    }

    public function quarterly(int $quarter = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'periode_type' => 'quarterly',
            'quarter'      => $quarter,
            'bulan'        => null,
        ]);
    }

    public function monthly(int $bulan = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'periode_type' => 'monthly',
            'quarter'      => null,
            'bulan'        => $bulan,
        ]);
    }
}