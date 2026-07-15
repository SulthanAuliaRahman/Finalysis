<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Analisis;

class AnalisisCommonsizeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'analisis_id'               => Analisis::factory(),
            'hpp_persen'                => 60.00,
            'laba_kotor_persen'         => 40.00,
            'beban_lain_pajak_persen'   => 20.00,
            'laba_bersih_persen'        => 20.00,
            'aset_lancar_persen'        => 60.00,
            'aset_tetap_persen'         => 40.00,
            'liabilitas_lancar_persen'  => 30.00,
            'liabilitas_panjang_persen' => 20.00,
            'ekuitas_persen'            => 50.00,
        ];
    }
}