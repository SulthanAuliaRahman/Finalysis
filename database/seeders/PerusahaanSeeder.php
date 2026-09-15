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
                'deskripsi' => 'Agensi digital yang bergerak di bidang jasa pemasaran dan komunikasi digital untuk perusahaan. Perusahaan menyediakan layanan branding, desain grafis, social media management, pembuatan konten digital, serta digital advertising. Pendapatan terutama berasal dari biaya jasa proyek, kontrak layanan berkala, dan pengelolaan kampanye digital. Operasional perusahaan terutama melibatkan biaya tenaga kerja, operasional kantor, perangkat lunak dan tools digital, serta biaya pelaksanaan proyek dan kampanye klien.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Mitra Finansial Abadi',
                'deskripsi' => 'Perusahaan jasa yang menyediakan layanan konsultasi keuangan dan solusi pengelolaan finansial bagi pelaku usaha. Layanan perusahaan meliputi konsultasi perencanaan dan pengelolaan keuangan, penyusunan laporan keuangan, analisis kondisi keuangan, serta pendampingan dalam pengambilan keputusan finansial. Pendapatan perusahaan terutama berasal dari biaya jasa konsultasi dan kontrak layanan dengan klien. Kegiatan operasional perusahaan terutama melibatkan tenaga profesional di bidang keuangan, biaya operasional kantor, serta perangkat lunak dan layanan pendukung konsultasi.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Sinar Usaha Persada',
                'deskripsi' => 'Perusahaan jasa yang menyediakan layanan pendukung operasional dan pengembangan usaha bagi berbagai pelaku bisnis untuk meningkatkan efektivitas dan efisiensi operasional. Layanan perusahaan meliputi pendampingan operasional, pengembangan proses bisnis, konsultasi pengelolaan usaha, serta layanan pendukung manajemen. Pendapatan perusahaan terutama berasal dari biaya jasa, proyek pendampingan, dan kontrak layanan dengan klien. Kegiatan operasional terutama melibatkan tenaga profesional, biaya operasional kantor, serta biaya pelaksanaan proyek dan layanan kepada klien.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Lentera Bisnis Nusantara',
                'deskripsi' => 'Perusahaan jasa yang menyediakan layanan konsultasi dan pengembangan strategi bisnis bagi pelaku usaha untuk mendukung pertumbuhan dan peningkatan kinerja bisnis. Layanan perusahaan meliputi konsultasi strategi bisnis, perencanaan pengembangan usaha, evaluasi proses bisnis, serta pendampingan implementasi strategi. Pendapatan perusahaan terutama berasal dari biaya jasa konsultasi, proyek pendampingan, dan kontrak layanan dengan klien. Kegiatan operasional perusahaan terutama melibatkan tenaga konsultan dan profesional, biaya operasional kantor, serta biaya pendukung pelaksanaan proyek dan layanan konsultasi.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Prima Solusi Indonesia',
                'deskripsi' => 'Perusahaan jasa yang menyediakan layanan profesional dan solusi bisnis sesuai dengan kebutuhan pelanggan. Layanan perusahaan meliputi konsultasi bisnis, pendampingan operasional, pengembangan solusi untuk kebutuhan perusahaan, serta layanan profesional berbasis proyek. Pendapatan perusahaan terutama berasal dari biaya jasa, proyek konsultasi, dan kontrak layanan dengan pelanggan. Kegiatan operasional terutama melibatkan tenaga profesional, biaya operasional kantor, serta sumber daya dan perangkat pendukung untuk pelaksanaan proyek dan layanan kepada pelanggan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Karya Cakrawala Mandiri',
                'deskripsi' => 'Perusahaan jasa yang menyediakan layanan profesional untuk mendukung kegiatan operasional dan pengembangan bisnis bagi berbagai pelanggan. Layanan perusahaan meliputi pendampingan operasional, konsultasi pengembangan usaha, serta penyediaan layanan profesional sesuai kebutuhan pelanggan. Pendapatan perusahaan terutama berasal dari biaya jasa, proyek layanan, dan kontrak kerja sama dengan pelanggan. Kegiatan operasional terutama melibatkan tenaga profesional, biaya operasional kantor, serta sumber daya pendukung dalam pelaksanaan layanan kepada pelanggan.',
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
