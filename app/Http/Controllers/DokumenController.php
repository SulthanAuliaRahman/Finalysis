<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DokumenController extends Controller
{
    // Halaman Utama: Daftar Dokumen Berdasarkan Perusahaan
    public function index(Perusahaan $perusahaan)
    {
        // DATA DUMMY: Simulasi daftar dokumen dengan berbagai status untuk melihat tombol aksi dinamis
        $dummyDokumen = [
            [
                'id' => 101,
                'nama_file' => 'Draf LK Pilar 2024.pdf',
                'periode' => '2024',
                'ukuran_file' => 4404019, // ~4.2 MB
                'status' => 'diekstrak', // Akan memunculkan tombol "Lanjut Review"
                'created_at' => now()->subDays(2)->toIsoString()
            ],
            [
                'id' => 102,
                'nama_file' => 'Laporan_Keuangan_Q3_Asli.pdf',
                'periode' => '2024',
                'ukuran_file' => 1887436, // ~1.8 MB
                'status' => 'dichunk', // Akan memunculkan tombol "Lanjut Embed"
                'created_at' => now()->subDays(5)->toIsoString()
            ],
            [
                'id' => 103,
                'nama_file' => 'Annual_Report_Final_V2.pdf',
                'periode' => '2023',
                'ukuran_file' => 5767168, // ~5.5 MB
                'status' => 'selesai', // Akan memunculkan tombol "Lihat Chunks"
                'created_at' => now()->subMonths(1)->toIsoString()
            ],
        ];

        return Inertia::render('Perusahaan/Dokumen/Index', [
            'perusahaan' => $perusahaan,
            'dokumenList' => $dummyDokumen
        ]);
    }

    // Step 1: Menampilkan Halaman Upload
    public function create(Perusahaan $perusahaan)
    {
        return Inertia::render('Perusahaan/Dokumen/Create', [
            'perusahaan' => $perusahaan
        ]);
    }

    // Step 1 (Action): Simulasi Terima File & Redirect ke Halaman Review (Step 2)
    public function store(Request $request, Perusahaan $perusahaan)
    {
        // Validasi formalitas agar useForm di Front End tidak error
        $request->validate([
            'file' => 'required',
            'periode' => 'required',
            'statement_types' => 'required|array'
        ]);

        // Simulasi ID Dokumen Baru yang berhasil dibuat di database dengan status "diekstrak"
        $mockNewDokumenId = 101;

        // Setelah upload, langsung arahkan user ke halaman Review (Step 2)
        return redirect()->route('perusahaan.dokumen.review', [
            'perusahaan' => $perusahaan->id,
            'dokumen' => $mockNewDokumenId
        ]);
    }

    // STEP 2: Halaman Verifikasi & Review Finansial (Memuat Data Dummy Hasil Extract Python)
    public function review(Perusahaan $perusahaan, $dokumen)
    {
        $mockDokumenModel = [
            'id' => $dokumen,
            'nama_file' => 'Draf LK Pilar 2024.pdf',
            'status' => 'diekstrak'
        ];

        // DATA DUMMY: Disesuaikan dengan skema database baru Anda
        $dummyExtractedData = [
            "neraca" => [
                "total_equity" => 177925766,
                "total_liabilities" => 1603932233,
                "current_liabilities" => 1426006467,
                "total_assets" => 1603932233,
                "current_assets" => 1597428191,
            ],
            "laba_rugi" => [
                "pendapatan" => null, // Bisa null jika tidak ke extrak
                "laba_kotor" => 1443364106,
                "laba_bersih" => 411158584
            ],
            "arus_kas" => [
                "kas_masuk" => 520000000,
                "kas_keluar" => 380000000
            ]
        ];

        // DATA DUMMY FOUND AT: Menyimpan posisi deteksi untuk setiap field numerik
        $dummyFoundAt = [
            "total_equity" => ["page" => 1, "label_in_pdf" => "Jumlah Ekuitas", "all_numbers_on_row" => ["177.925.766"]],
            "total_liabilities" => ["page" => 1, "label_in_pdf" => "Total Kewajiban", "all_numbers_on_row" => ["1.603.932.233"]],
            "current_liabilities" => ["page" => 1, "label_in_pdf" => "Jumlah Kewajiban Lancar", "all_numbers_on_row" => ["1.426.006.467"]],
            "total_assets" => ["page" => 1, "label_in_pdf" => "TOTAL AKTIVA", "all_numbers_on_row" => ["1.603.932.233"]],
            "current_assets" => ["page" => 1, "label_in_pdf" => "Jumlah Aktiva Lancar", "all_numbers_on_row" => ["1.597.428.191"]],

            "pendapatan" => ["page" => 2, "label_in_pdf" => "Pendapatan Bersih", "all_numbers_on_row" => ["3.676.484.627"]],
            "laba_kotor" => ["page" => 2, "label_in_pdf" => "Laba Kotor", "all_numbers_on_row" => ["1.443.364.106", "2.100.000"]],
            "laba_bersih" => ["page" => 2, "label_in_pdf" => "Laba Tahun Berjalan", "all_numbers_on_row" => ["411.158.584"]],

            "kas_masuk" => ["page" => 3, "label_in_pdf" => "Arus Kas Masuk dari Operasional", "all_numbers_on_row" => ["520.000.000"]],
            "kas_keluar" => ["page" => 3, "label_in_pdf" => "Arus Kas Keluar untuk Investasi", "all_numbers_on_row" => ["380.000.000"]]
        ];

        return Inertia::render('Perusahaan/Dokumen/Review', [
            'perusahaan' => $perusahaan,
            'dokumen' => $mockDokumenModel,
            'extractedData' => $dummyExtractedData,
            'foundAt' => $dummyFoundAt
        ]);
    }

    // Step 2 (Action): Simulasi Tombol "Proses Chunking" diklik
    public function embedPage(Perusahaan $perusahaan, $dokumen)
    {
        $mockDokumenModel = [
            'id' => $dokumen,
            'nama_file' => 'Draf LK Pilar 2024.pdf',
            'status' => 'dichunk',
            'ukuran_file' => 4404019
        ];

        // DATA DUMMY: Hasil dari proses Chunking Python
        $dummyChunks = [
            [
                "id" => 1,
                "text" => "## Laporan Posisi Keuangan (Neraca)\n\nAset Lancar:\n- Kas dan Setara Kas: 1.597.428.191\n- Piutang Usaha: 9.800.000\n\nTotal Aset: 1.603.932.233\n\nLiabilitas dan Ekuitas:\n- Liabilitas Jangka Pendek: 1.426.006.467\n- Total Ekuitas: 177.925.766",
                "metadata" => [
                    "company" => $perusahaan->nama,
                    "period" => "2024",
                    "statement_type" => "neraca",
                    "statement_label" => "Laporan Posisi Keuangan",
                    "page_start" => 1,
                    "page_end" => 1,
                    "chunk_index" => 0
                ]
            ],
            [
                "id" => 2,
                "text" => "## Laporan Laba Rugi\n\nPendapatan Usaha: 3.676.484.627\nBeban Pokok Pendapatan: (2.233.120.521)\n\nLaba Kotor: 1.443.364.106\nBeban Usaha: (979.461.545)\n\nLaba Operasional: 463.902.561\nLaba Bersih Tahun Berjalan: 411.158.584",
                "metadata" => [
                    "company" => $perusahaan->nama,
                    "period" => "2024",
                    "statement_type" => "laba_rugi",
                    "statement_label" => "Laporan Laba Rugi Komprehensif",
                    "page_start" => 2,
                    "page_end" => 2,
                    "chunk_index" => 1
                ]
            ],

        ];

        return Inertia::render('Perusahaan/Dokumen/Embed', [
            'perusahaan' => $perusahaan,
            'dokumen' => $mockDokumenModel,
            'chunks' => $dummyChunks
        ]);
    }

    // Step 3 (Action): Simulasi Proses Embedding ke Vector DB (NeuronAI / Chroma / Milvus dll)
    public function startEmbedding(Request $request, Perusahaan $perusahaan, $dokumen)
    {
        // Di sini nantinya controller Anda akan mengirim request ke NeuronAI DataLoader
        // Update dokumen status menjadi 'selesai'

        return redirect()->route('perusahaan.dokumen.index', $perusahaan->id);
    }

    // STEP 4: Halaman Arsip - Hanya Lihat Chunks yang Sudah Selesai Diembed
    public function showChunks(Perusahaan $perusahaan, $dokumen)
    {
        $mockDokumenModel = [
            'id' => $dokumen,
            'nama_file' => 'Draf LK Pilar 2024.pdf',
            'status' => 'selesai', // Statusnya sudah selesai
            'ukuran_file' => 4404019
        ];

        // Data Dummy Chunks yang sama untuk dibaca
        $dummyChunks = [
            [
                "id" => 1,
                "text" => "## Laporan Posisi Keuangan (Neraca)\n\nAset Lancar:\n- Kas dan Setara Kas: 1.597.428.191\n- Piutang Usaha: 9.800.000\n\nTotal Aset: 1.603.932.233",
                "metadata" => ["statement_type" => "neraca", "page_start" => 1, "page_end" => 1, "chunk_index" => 0]
            ],
            [
                "id" => 2,
                "text" => "## Laporan Laba Rugi\n\nPendapatan Usaha: 3.676.484.627\nLaba Kotor: 1.443.364.106\nLaba Bersih Tahun Berjalan: 411.158.584",
                "metadata" => ["statement_type" => "laba_rugi", "page_start" => 2, "page_end" => 2, "chunk_index" => 1]
            ]
        ];

        return Inertia::render('Perusahaan/Dokumen/ShowChunks', [
            'perusahaan' => $perusahaan,
            'dokumen' => $mockDokumenModel,
            'chunks' => $dummyChunks
        ]);
    }
}
