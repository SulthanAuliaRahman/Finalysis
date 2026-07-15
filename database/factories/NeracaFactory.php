<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Dokumen;

class NeracaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'dokumen_id'          => Dokumen::factory(),
            'cash_equivalent'     => 150000000,
            'inventory'           => 100000000,
            'total_equity'        => 400000000,
            'total_liabilities'   => 600000000,
            'current_liabilities' => 250000000,
            'total_assets'        => 1000000000,
            'current_assets'      => 500000000,
        ];
    }
}