<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Perusahaan;

class DokumenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'perusahaan_id'   => Perusahaan::factory(),
            'nama_file'       => $this->faker->word() . '.pdf',
            'storage_path'    => 'dokumen/test/' . $this->faker->uuid() . '.pdf',
            'periode_type'    => 'annual',
            'tahun'           => $this->faker->numberBetween(2018, 2024),
            'quarter'         => null,
            'bulan'           => null,
            'statement_types' => ['balance_sheet', 'income_statement', 'cash_flow'],
            'ukuran_file'     => 1000000,
            'status'          => 'selesai',
        ];
    }
}