<?php

namespace Database\Seeders;

use App\Models\Perusahaan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PerusahaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Perusahaan::insert([
                'nama'=>'PT ABC',
                'sektor'=>'Jasa',
                'deskripsi'=>'Perusahaan studi kasus untuk aplikasi analisis laporan keuangan.',
                'created_at'=>now(),
                'updated_at'=>now()
            ],
            [
                'nama' => 'PT Telkom Indonesia',
                'sektor' => 'Telekomunikasi',
                'deskripsi' => 'Perusahaan telekomunikasi nasional.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Bank Central Asia Tbk',
                'sektor' => 'Perbankan',
                'deskripsi' => 'Perusahaan perbankan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Unilever Indonesia Tbk',
                'sektor' => 'Consumer Goods',
                'deskripsi' => 'Perusahaan barang konsumsi.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }
}
