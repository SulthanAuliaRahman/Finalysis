<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use App\Models\Dokumen;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnalisisController extends Controller
{

    public function index(Perusahaan $perusahaan)
    {
        $dummyAnalisisList = [
            [
                'id' => 1,
                'periode_label' => 'Q1 2025',
                'tahun' => 2025,
                'tipe_periode' => 'quarterly',
                'jumlah_dokumen' => 1,
                'status' => 'sudah_dianalisis',
            ],
            [
                'id' => 2,
                'periode_label' => 'Q2 2025',
                'tahun' => 2025,
                'tipe_periode' => 'quarterly',
                'jumlah_dokumen' => 2,
                'status' => 'sudah_dianalisis',
            ],
            [
                'id' => 3,
                'periode_label' => 'Q3 2025',
                'tahun' => 2025,
                'tipe_periode' => 'quarterly',
                'jumlah_dokumen' => 3,
                'status' => 'perubahan_data',
            ],
            [
                'id' => 4,
                'periode_label' => 'September 2025',
                'tahun' => 2025,
                'tipe_periode' => 'monthly',
                'jumlah_dokumen' => 1,
                'status' => 'belum_dimulai',
            ],
            [
                'id' => 5,
                'periode_label' => 'Oktober 2025',
                'tahun' => 2025,
                'tipe_periode' => 'monthly',
                'jumlah_dokumen' => 2,
                'status' => 'sudah_dianalisis',
            ],
            [
                'id' => 6,
                'periode_label' => '2024',
                'tahun' => 2024,
                'tipe_periode' => 'annual',
                'jumlah_dokumen' => 4,
                'status' => 'sudah_dianalisis',
            ],
            [
                'id' => 7,
                'periode_label' => 'Q1 2026',
                'tahun' => 2026,
                'tipe_periode' => 'quarterly',
                'jumlah_dokumen' => 1,
                'status' => 'belum_dimulai',
            ],
        ];

        return Inertia::render('Perusahaan/Analisis/Index', [
            'perusahaan' => $perusahaan,
            'analisisList' => $dummyAnalisisList,
        ]);
    }

    public function analisis(Perusahaan $perusahaan, $analisis)
    {
        $dummyPeriodeMap = [
            1 => 'Q1 2025',
            2 => 'Q2 2025',
            3 => 'Q3 2025',
            4 => 'September 2025',
            5 => 'Oktober 2025',
            6 => '2024',
            7 => 'Q1 2026',
        ];

        $periodeLabel = $dummyPeriodeMap[$analisis] ?? 'Tidak Diketahui';

        return Inertia::render('Perusahaan/Analisis/Detail', [
            'perusahaan' => $perusahaan,
            'periodeLabel' => $periodeLabel,
        ]);
    }
}
