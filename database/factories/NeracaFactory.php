<?php

namespace Database\Factories;

use App\Models\Neraca;
use App\Models\Dokumen;
use Illuminate\Database\Eloquent\Factories\Factory;

class NeracaFactory extends Factory
{
    protected $model = Neraca::class;

    public function definition(): array
    {
        $kas = $this->faker->numberBetween(10000000, 50000000);
        $asetLancar = $kas + $this->faker->numberBetween(20000000, 50000000);
        $asetTetap = $this->faker->numberBetween(50000000, 200000000);
        
        $liabPendek = $this->faker->numberBetween(10000000, 40000000);
        $liabPanjang = $this->faker->numberBetween(20000000, 60000000);
        $totalLiab = $liabPendek + $liabPanjang;
        
        $totalAset = $asetLancar + $asetTetap;
        $totalEquitas = $totalAset - $totalLiab;

        return [
            'dokumen_id'                => Dokumen::factory(),
            'total_kas_setara_kas'      => $kas,
            'total_asset_lancar'        => $asetLancar,
            'total_asset_tetap'         => $asetTetap,
            'total_asset'               => $totalAset,
            'total_liabilities_pendek'  => $liabPendek,
            'total_liabilities_panjang' => $liabPanjang,
            'total_liabilities'         => $totalLiab,
            'total_equitas'             => $totalEquitas,
        ];
    }
}