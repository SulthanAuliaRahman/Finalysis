<?php

namespace Database\Factories;

use App\Models\AnalisisCommonsize;
use App\Models\Analisis;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalisisCommonsizeFactory extends Factory
{
    protected $model = AnalisisCommonsize::class;

    public function definition(): array
    {
        return [
            'analisis_id'               => Analisis::factory(),
            'pendapatan_persen'         => 100.00,
            'beban_persen'              => $this->faker->randomFloat(2, 60, 85),
            'laba_bersih_persen'        => $this->faker->randomFloat(2, 15, 40),
            'aset_lancar_persen'        => $this->faker->randomFloat(2, 30, 50),
            'aset_tetap_persen'         => $this->faker->randomFloat(2, 50, 70),
            'liabilitas_pendek_persen'  => $this->faker->randomFloat(2, 10, 30),
            'liabilitas_panjang_persen' => $this->faker->randomFloat(2, 10, 30),
            'ekuitas_persen'            => $this->faker->randomFloat(2, 40, 70),
            'narasi_commonsize_AI'      => $this->faker->paragraph(),
        ];
    }
}