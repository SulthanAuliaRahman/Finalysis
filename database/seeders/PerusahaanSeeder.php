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
                'deskripsi' => 'Perusahaan yang menyediakan layanan konsultasi dan solusi bisnis untuk mendukung kebutuhan di bidang IT.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'nama' => 'PT Cakrawala Jasa Mandiri',
                'deskripsi' => 'Perusahaan yang bergerak dalam penyediaan layanan profesional dan solusi pendukung bagi berbagai kebutuhan bisnis.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Pilar Karya Sejahtera',
                'deskripsi' => 'Perusahaan yang menyediakan layanan konsultasi, pengelolaan usaha, dan pendampingan untuk meningkatkan kinerja bisnis.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Mitra Finansial Abadi',
                'deskripsi' => 'Perusahaan yang menyediakan layanan konsultasi keuangan dan solusi pengelolaan finansial bagi pelaku usaha.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Sinar Usaha Persada',
                'deskripsi' => 'Perusahaan yang menyediakan layanan pendukung operasional dan pengembangan usaha untuk meningkatkan efektivitas bisnis.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Lentera Bisnis Nusantara',
                'deskripsi' => 'Perusahaan yang bergerak dalam layanan konsultasi dan pengembangan strategi bisnis untuk mendukung pertumbuhan usaha.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Prima Solusi Indonesia',
                'deskripsi' => 'Perusahaan yang menyediakan berbagai layanan profesional dan solusi bisnis sesuai dengan kebutuhan pelanggan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Karya Cakrawala Mandiri',
                'deskripsi' => 'Perusahaan yang menyediakan layanan profesional dalam mendukung kegiatan operasional dan pengembangan bisnis.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Awan Teknologi Indonesia',
                'deskripsi' => 'Perusahaan penyedia jasa cloud migration, cloud management, dan infrastructure monitoring untuk perusahaan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Arsitektur Ruang Indonesia',
                'deskripsi' => 'Biro jasa arsitektur dan desain interior yang mengerjakan proyek desain bangunan, renovasi, dan interior komersial.',
                'created_at' => now(),
                'updated_at' => now(),
            ],[
                'nama' => 'PT Prima Rekrutmen Indonesia',
                'deskripsi' => 'Perusahaan jasa recruitment dan executive search yang membantu perusahaan mencari dan menyeleksi tenaga kerja.',
                'created_at' => now(),
                'updated_at' => now(),
            ],[
                'nama' => 'PT Solusi Pelatihan Teknologi',
                'deskripsi' => 'Perusahaan penyedia jasa pelatihan teknologi dan program upskilling untuk karyawan perusahaan dan institusi pendidikan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],[
                'nama' => 'PT Konsultan Bisnis Indonesia',
                'deskripsi' => 'Perusahaan jasa konsultasi bisnis yang membantu perusahaan dalam mengembangkan strategi dan solusi operasional.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Inovasi Digital Indonesia',
                'deskripsi' => 'Perusahaan jasa inovasi digital yang menyediakan solusi teknologi untuk mengoptimalkan proses bisnis dan meningkatkan efisiensi operasional.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Kreatif Media Indonesia',
                'deskripsi' => 'Agensi digital yang menyediakan jasa branding, desain, social media management, dan digital advertising untuk berbagai perusahaan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);

        foreach ($perusahaan as $data){
            Perusahaan::create($data);
        }
    }
}
