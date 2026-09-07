<?php

namespace Database\Factories;

use App\Models\LabaRugi;
use App\Models\Dokumen;
use Illuminate\Database\Eloquent\Factories\Factory;

class LabaRugiFactory extends Factory
{
    protected $model = LabaRugi::class;

    public function definition(): array
    {
        $pendapatan = $this->faker->numberBetween(100000000, 500000000);
        $beban = -$this->faker->numberBetween(50000000, 200000000); // Nilai beban negatif sesuai sistem import
        $bebanPajak = -$this->faker->numberBetween(5000000, 20000000);
        
        $labaSebelumPajak = $pendapatan + $beban;
        $labaSesudahPajak = $labaSebelumPajak + $bebanPajak;

        return [
            'dokumen_id'                => Dokumen::factory(),
            'total_pendapatan'          => $pendapatan,
            'total_beban'               => $beban,
            'total_biaya_pajak'         => $bebanPajak,
            'laba_bersih_sebelum_pajak' => $labaSebelumPajak,
            'laba_bersih_sesudah_pajak' => $labaSesudahPajak,
        ];
    }
}