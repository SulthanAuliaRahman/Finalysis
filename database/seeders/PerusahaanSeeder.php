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
        $perusahaan = [
            [
                'nama' => 'PT Pilar Wahana Artha',
                'profile' => 'Perusahaan jasa yang menyediakan layanan konsultasi dan solusi bisnis berbasis teknologi informasi.',
                'bidang_jasa' => 'Layanan konsultasi dan solusi bisnis untuk mendukung kebutuhan di bidang IT.',
                'model_pendapatan' => 'Biaya jasa konsultasi, proyek pengembangan solusi, dan kontrak layanan dengan klien.',
            ],
            [
                'nama' => 'PT Cakrawala Jasa Mandiri',
                'profile' => 'Perusahaan yang menyediakan layanan profesional dan solusi pendukung untuk kebutuhan operasional bisnis.',
                'bidang_jasa' => 'Layanan profesional dan solusi pendukung bagi berbagai kebutuhan bisnis.',
                'model_pendapatan' => 'Biaya jasa, proyek layanan, dan kontrak kerja sama dengan pelanggan.',
            ],
            [
                'nama' => 'PT Pilar Karya Sejahtera',
                'profile' => 'Perusahaan jasa yang bergerak dalam bidang pemasaran dan komunikasi digital.',
                'bidang_jasa' => 'Agensi digital untuk pemasaran dan komunikasi digital, meliputi branding, desain grafis, social media management, pembuatan konten, dan digital advertising.',
                'model_pendapatan' => 'Biaya jasa proyek, kontrak layanan berkala, dan pengelolaan kampanye digital.',
            ],
            [
                'nama' => 'PT Mitra Finansial Abadi',
                'profile' => 'Perusahaan jasa profesional yang menyediakan layanan konsultasi dan pendampingan di bidang keuangan.',
                'bidang_jasa' => 'Konsultasi keuangan, penyusunan laporan keuangan, analisis kondisi keuangan, dan pendampingan keputusan finansial bagi pelaku usaha.',
                'model_pendapatan' => 'Biaya jasa konsultasi dan kontrak layanan dengan klien.',
            ],
            [
                'nama' => 'PT Sinar Usaha Persada',
                'profile' => 'Perusahaan jasa yang menyediakan layanan pendampingan operasional dan pengembangan proses bisnis.',
                'bidang_jasa' => 'Pendampingan operasional, pengembangan proses bisnis, konsultasi pengelolaan usaha, dan layanan pendukung manajemen.',
                'model_pendapatan' => 'Biaya jasa, proyek pendampingan, dan kontrak layanan dengan klien.',
            ],
            [
                'nama' => 'PT Lentera Bisnis Nusantara',
                'profile' => 'Perusahaan jasa konsultasi yang membantu usaha dalam perencanaan dan pengembangan bisnis.',
                'bidang_jasa' => 'Konsultasi strategi bisnis, perencanaan pengembangan usaha, evaluasi proses bisnis, dan pendampingan implementasi strategi.',
                'model_pendapatan' => 'Biaya jasa konsultasi, proyek pendampingan, dan kontrak layanan dengan klien.',
            ],
            [
                'nama' => 'PT Prima Solusi Indonesia',
                'profile' => 'Perusahaan jasa profesional yang menyediakan konsultasi dan solusi bisnis berbasis proyek.',
                'bidang_jasa' => 'Konsultasi bisnis, pendampingan operasional, pengembangan solusi, dan layanan profesional berbasis proyek.',
                'model_pendapatan' => 'Biaya jasa, proyek konsultasi, dan kontrak layanan dengan pelanggan.',
            ],
            [
                'nama' => 'PT Karya Cakrawala Mandiri',
                'profile' => 'Perusahaan jasa yang menyediakan layanan profesional dan pendampingan pengembangan usaha.',
                'bidang_jasa' => 'Pendampingan operasional, konsultasi pengembangan usaha, dan layanan profesional sesuai kebutuhan pelanggan.',
                'model_pendapatan' => 'Biaya jasa, proyek layanan, dan kontrak kerja sama dengan pelanggan.',
            ],
            [
                'nama' => 'PT Awan Teknologi Indonesia',
                'profile' => 'Perusahaan jasa teknologi yang menyediakan solusi infrastruktur dan layanan berbasis cloud.',
                'bidang_jasa' => 'Cloud migration, cloud management, dan infrastructure monitoring untuk perusahaan.',
                'model_pendapatan' => 'Biaya jasa implementasi, layanan pengelolaan cloud, dan kontrak layanan berlangganan.',
            ],
            [
                'nama' => 'PT Arsitektur Ruang Indonesia',
                'profile' => 'Perusahaan jasa profesional yang bergerak dalam bidang arsitektur dan desain interior.',
                'bidang_jasa' => 'Jasa arsitektur dan desain interior untuk proyek desain bangunan, renovasi, dan interior komersial.',
                'model_pendapatan' => 'Biaya jasa desain, biaya proyek, dan kontrak jasa perancangan dengan klien.',
            ],
            [
                'nama' => 'PT Prima Rekrutmen Indonesia',
                'profile' => 'Perusahaan jasa profesional yang menyediakan layanan pencarian dan seleksi tenaga kerja.',
                'bidang_jasa' => 'Recruitment dan executive search untuk membantu perusahaan mencari serta menyeleksi tenaga kerja.',
                'model_pendapatan' => 'Biaya jasa rekrutmen dan recruitment fee berdasarkan layanan atau kandidat yang berhasil ditempatkan.',
            ],
            [
                'nama' => 'PT Solusi Pelatihan Teknologi',
                'profile' => 'Perusahaan jasa pendidikan dan pelatihan yang berfokus pada pengembangan kompetensi teknologi.',
                'bidang_jasa' => 'Pelatihan teknologi dan program upskilling untuk karyawan perusahaan serta institusi pendidikan.',
                'model_pendapatan' => 'Biaya pelatihan, biaya program, dan kontrak penyelenggaraan pelatihan dengan perusahaan atau institusi.',
            ],
            [
                'nama' => 'PT Konsultan Bisnis Indonesia',
                'profile' => 'Perusahaan jasa konsultasi yang membantu perusahaan dalam pengembangan strategi dan operasional bisnis.',
                'bidang_jasa' => 'Konsultasi bisnis untuk membantu perusahaan mengembangkan strategi dan solusi operasional.',
                'model_pendapatan' => 'Biaya konsultasi, proyek pendampingan, dan kontrak layanan konsultasi.',
            ],
            [
                'nama' => 'PT Inovasi Digital Indonesia',
                'profile' => 'Perusahaan jasa teknologi yang menyediakan solusi digital untuk mendukung efisiensi proses bisnis.',
                'bidang_jasa' => 'Solusi inovasi digital untuk mengoptimalkan proses bisnis dan meningkatkan efisiensi operasional.',
                'model_pendapatan' => 'Biaya pengembangan solusi digital, proyek implementasi, dan kontrak layanan dengan pelanggan.',
            ],
            [
                'nama' => 'PT Kreatif Media Indonesia',
                'profile' => 'Perusahaan jasa kreatif yang menyediakan layanan pemasaran dan komunikasi digital.',
                'bidang_jasa' => 'Agensi digital yang menyediakan jasa branding, desain, social media management, dan digital advertising.',
                'model_pendapatan' => 'Biaya jasa proyek, kontrak layanan berkala, dan pengelolaan kampanye digital.',
            ],
        ];

        foreach ($perusahaan as $data) {
            Perusahaan::updateOrCreate(
                ['nama' => $data['nama']],
                $data
            );
        }
    }
}
