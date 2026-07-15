<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Dokumen;

class LabaRugiFactory extends Factory
{
    public function definition(): array
    {
        return [
            'dokumen_id'  => Dokumen::factory(),
            'pendapatan'  => 800000000,
            'laba_kotor'  => 400000000,
            'laba_bersih' => 80000000,
        ];
    }
}