<?php

namespace Database\Seeders;

use App\Models\Perusahaan;
use Illuminate\Database\Seeder;

class PerusahaanSeeder extends Seeder
{
    private const LABEL_BAGIAN = [
        'identitas' => 'Profil & skala usaha',
        'bidang' => 'Bidang dan jasa utama',
        'pendapatan' => 'Model pendapatan & pola pembayaran',
    ];

    private static function deskripsi(array $bagian): string
    {
        $hasil = [];

        foreach (self::LABEL_BAGIAN as $key => $label) {
            if (!empty($bagian[$key])) {
                $hasil[] = $label . "\n" . trim($bagian[$key]);
            }
        }

        return implode("\n\n", $hasil);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perusahaan = ([
            [

                'nama' => 'PT Pilar Wahana Artha',
                'deskripsi' => self::deskripsi(['bidang' => 'Layanan konsultasi dan solusi bisnis untuk mendukung kebutuhan di bidang IT.']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'nama' => 'PT Cakrawala Jasa Mandiri',
                'deskripsi' => self::deskripsi(['bidang' => 'Layanan profesional dan solusi pendukung bagi berbagai kebutuhan bisnis.']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Pilar Karya Sejahtera',
                'deskripsi' => self::deskripsi([
                    'bidang' => 'Agensi digital untuk pemasaran dan komunikasi digital: branding, desain grafis, social media management, pembuatan konten, dan digital advertising.',
                    'pendapatan' => 'Biaya jasa proyek, kontrak layanan berkala, dan pengelolaan kampanye digital.',
                    'operasional' => 'Tenaga kerja, operasional kantor, perangkat lunak dan tools digital, serta pelaksanaan proyek dan kampanye klien.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Mitra Finansial Abadi',
                'deskripsi' => self::deskripsi([
                    'bidang' => 'Konsultasi keuangan, penyusunan laporan keuangan, analisis kondisi keuangan, dan pendampingan keputusan finansial bagi pelaku usaha.',
                    'pendapatan' => 'Biaya jasa konsultasi dan kontrak layanan dengan klien.',
                    'operasional' => 'Tenaga profesional keuangan, operasional kantor, perangkat lunak, dan layanan pendukung konsultasi.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Sinar Usaha Persada',
                'deskripsi' => self::deskripsi([
                    'bidang' => 'Pendampingan operasional, pengembangan proses bisnis, konsultasi pengelolaan usaha, dan layanan pendukung manajemen.',
                    'pendapatan' => 'Biaya jasa, proyek pendampingan, dan kontrak layanan dengan klien.',
                    'operasional' => 'Tenaga profesional, operasional kantor, serta pelaksanaan proyek dan layanan kepada klien.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Lentera Bisnis Nusantara',
                'deskripsi' => self::deskripsi([
                    'bidang' => 'Konsultasi strategi bisnis, perencanaan pengembangan usaha, evaluasi proses bisnis, dan pendampingan implementasi strategi.',
                    'pendapatan' => 'Biaya jasa konsultasi, proyek pendampingan, dan kontrak layanan dengan klien.',
                    'operasional' => 'Tenaga konsultan dan profesional, operasional kantor, serta pendukung pelaksanaan proyek dan layanan konsultasi.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Prima Solusi Indonesia',
                'deskripsi' => self::deskripsi([
                    'bidang' => 'Konsultasi bisnis, pendampingan operasional, pengembangan solusi, dan layanan profesional berbasis proyek.',
                    'pendapatan' => 'Biaya jasa, proyek konsultasi, dan kontrak layanan dengan pelanggan.',
                    'operasional' => 'Tenaga profesional, operasional kantor, serta sumber daya dan perangkat pendukung pelaksanaan proyek.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Karya Cakrawala Mandiri',
                'deskripsi' => self::deskripsi([
                    'bidang' => 'Pendampingan operasional, konsultasi pengembangan usaha, dan layanan profesional sesuai kebutuhan pelanggan.',
                    'pendapatan' => 'Biaya jasa, proyek layanan, dan kontrak kerja sama dengan pelanggan.',
                    'operasional' => 'Tenaga profesional, operasional kantor, dan sumber daya pendukung pelaksanaan layanan.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Awan Teknologi Indonesia',
                'deskripsi' => self::deskripsi(['bidang' => 'Cloud migration, cloud management, dan infrastructure monitoring untuk perusahaan.']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Arsitektur Ruang Indonesia',
                'deskripsi' => self::deskripsi(['bidang' => 'Jasa arsitektur dan desain interior untuk proyek desain bangunan, renovasi, dan interior komersial.']),
                'created_at' => now(),
                'updated_at' => now(),
            ],[
                'nama' => 'PT Prima Rekrutmen Indonesia',
                'deskripsi' => self::deskripsi(['bidang' => 'Recruitment dan executive search untuk membantu perusahaan mencari serta menyeleksi tenaga kerja.']),
                'created_at' => now(),
                'updated_at' => now(),
            ],[
                'nama' => 'PT Solusi Pelatihan Teknologi',
                'deskripsi' => self::deskripsi(['bidang' => 'Pelatihan teknologi dan program upskilling untuk karyawan perusahaan serta institusi pendidikan.']),
                'created_at' => now(),
                'updated_at' => now(),
            ],[
                'nama' => 'PT Konsultan Bisnis Indonesia',
                'deskripsi' => self::deskripsi(['bidang' => 'Konsultasi bisnis untuk membantu perusahaan mengembangkan strategi dan solusi operasional.']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Inovasi Digital Indonesia',
                'deskripsi' => self::deskripsi(['bidang' => 'Solusi inovasi digital untuk mengoptimalkan proses bisnis dan meningkatkan efisiensi operasional.']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Kreatif Media Indonesia',
                'deskripsi' => self::deskripsi(['bidang' => 'Agensi digital yang menyediakan jasa branding, desain, social media management, dan digital advertising.']),
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);

        foreach ($perusahaan as $data){
            Perusahaan::updateOrCreate(['nama' => $data['nama']], $data);
        }
    }
}
