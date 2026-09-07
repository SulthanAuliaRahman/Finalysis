<?php

namespace Database\Factories;

use App\Models\ChartOfAccount;
use App\Models\Dokumen;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChartOfAccountFactory extends Factory
{
    protected $model = ChartOfAccount::class;

    public function definition(): array
    {
        return [
            'dokumen_id'        => Dokumen::factory(),
            'nama_akun'         => 'Kas dan Bank',
            'kelompok_akun'     => 'aset',
            'sub_kelompok_akun' => 'kas_setara_kas',
            'nilai_akun'        => $this->faker->numberBetween(10000000, 50000000),
        ];
    }
}