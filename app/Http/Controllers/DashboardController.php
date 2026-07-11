<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Perusahaan;
use App\Models\Dokumen;
use App\Models\Analisis;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'super_admin') {
            // Super Admin Dashboard Data
            $stats = [
                'total_perusahaan' => Perusahaan::count(),
                'total_users' => User::count(),
                'total_dokumen' => Dokumen::count(),
                'total_analisis' => Analisis::count(),
            ];

            $recent_perusahaan = Perusahaan::withCount('dokumen')->latest()->take(5)->get();
            $recent_dokumen = Dokumen::with('perusahaan:id,nama')->latest()->take(5)->get();

            return Inertia::render('Dashboard', [
                'role' => 'super_admin',
                'stats' => $stats,
                'recentPerusahaan' => $recent_perusahaan,
                'recentDokumen' => $recent_dokumen,
            ]);
        } else {
            // Company Dashboard Data (Manager or User)
            $perusahaanId = $user->perusahaan_id;
            $perusahaan = Perusahaan::find($perusahaanId);

            $stats = [
                'total_dokumen' => Dokumen::where('perusahaan_id', $perusahaanId)->count(),
                'total_analisis' => Analisis::where('perusahaan_id', $perusahaanId)->count(),
                'total_users' => User::where('perusahaan_id', $perusahaanId)->count(),
            ];

            $recent_dokumen = Dokumen::where('perusahaan_id', $perusahaanId)->latest()->take(5)->get();
            $recent_analisis = Analisis::where('perusahaan_id', $perusahaanId)->latest()->take(5)->get();

            return Inertia::render('Dashboard', [
                'role' => $user->role, // manager or user
                'perusahaan' => $perusahaan,
                'stats' => $stats,
                'recentDokumen' => $recent_dokumen,
                'recentAnalisis' => $recent_analisis,
            ]);
        }
    }
}
