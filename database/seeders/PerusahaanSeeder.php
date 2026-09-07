<?php

namespace Database\Seeders;

use App\Models\Perusahaan;
use Illuminate\Database\Seeder;

class PerusahaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perusahaan = ([
            [

                'nama' => 'PT Pilar Wahana Artha',
                'sektor' => 'Jasa',
                'deskripsi' => 'Perusahaan yang menyediakan layanan konsultasi dan solusi bisnis untuk mendukung kebutuhan di bidang IT.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'nama' => 'PT Cakrawala Jasa Mandiri',
                'sektor' => 'Jasa',
                'deskripsi' => 'Perusahaan yang bergerak dalam penyediaan layanan profesional dan solusi pendukung bagi berbagai kebutuhan bisnis.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Pilar Karya Sejahtera',
                'sektor' => 'Jasa',
                'deskripsi' => 'Perusahaan yang menyediakan layanan konsultasi, pengelolaan usaha, dan pendampingan untuk meningkatkan kinerja bisnis.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Mitra Finansial Abadi',
                'sektor' => 'Jasa',
                'deskripsi' => 'Perusahaan yang menyediakan layanan konsultasi keuangan dan solusi pengelolaan finansial bagi pelaku usaha.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Sinar Usaha Persada',
                'sektor' => 'Jasa',
                'deskripsi' => 'Perusahaan yang menyediakan layanan pendukung operasional dan pengembangan usaha untuk meningkatkan efektivitas bisnis.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Lentera Bisnis Nusantara',
                'sektor' => 'Jasa',
                'deskripsi' => 'Perusahaan yang bergerak dalam layanan konsultasi dan pengembangan strategi bisnis untuk mendukung pertumbuhan usaha.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Prima Solusi Indonesia',
                'sektor' => 'Jasa',
                'deskripsi' => 'Perusahaan yang menyediakan berbagai layanan profesional dan solusi bisnis sesuai dengan kebutuhan pelanggan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Karya Cakrawala Mandiri',
                'sektor' => 'Jasa',
                'deskripsi' => 'Perusahaan yang menyediakan layanan profesional dalam mendukung kegiatan operasional dan pengembangan bisnis.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        foreach ($perusahaan as $data){
            Perusahaan::create($data);
        }
    }
}