<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Dokumen;

class ArusKasFactory extends Factory
{
    public function definition(): array
    {
        return [
            'dokumen_id' => Dokumen::factory(),
            'kas_masuk'  => 200000000,
            'kas_keluar' => 150000000,
            // cash_flow_from_operations/investing/financing dibiarkan null,
            // tidak dipakai di alur analisis saat ini (lihat pembahasan sebelumnya).
        ];
    }
}