<?php

namespace App\Http\Controllers;

use App\Models\Analisis;
use App\Models\Dokumen;
use App\Models\Perusahaan;
use App\Models\User;
use App\Services\DashboardFinancialService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardFinancialService $dashboardFinancial)
    {
        $user = $request->user();

        if ($user->role === 'super_admin') {
            return $this->superAdminDashboard();
        }

        return Inertia::render('Dashboard', [
            'role' => $user->role,
            'dashboard' => $dashboardFinancial->build($user->perusahaan, $request->string('dokumen')->toString()),
        ]);
    }

    private function superAdminDashboard()
    {
        return Inertia::render('Dashboard', [
            'role' => 'super_admin',
            'stats' => [
                'total_perusahaan' => Perusahaan::count(),
                'total_users' => User::count(),
                'total_dokumen' => Dokumen::count(),
                'total_analisis' => Analisis::count(),
            ],
            'recentPerusahaan' => Perusahaan::withCount('dokumen')->latest()->take(5)->get(),
            'recentDokumen' => Dokumen::with('perusahaan:id,nama')->latest()->take(5)->get(),
        ]);
    }
}