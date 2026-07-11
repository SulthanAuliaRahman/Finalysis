<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PerusahaanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $perusahaan = Perusahaan::query()
            ->when($search, function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%")
                      ->orWhere('sektor', 'like', "%{$search}%");
            })
            ->withCount('dokumen')
            ->latest()
            ->get();

        return Inertia::render('Perusahaan/Index', [
            'perusahaanList' => $perusahaan,
            'filters' => ['search' => $search ?? '']
        ]);
    }

    public function create()
    {
        return Inertia::render('Perusahaan/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'sektor' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        Perusahaan::create($validated);

        return redirect()->route('perusahaan.index');
    }

    public function edit(Perusahaan $perusahaan)
    {
        return Inertia::render('Perusahaan/Edit', [
            'perusahaan' => $perusahaan
        ]);
    }

    public function update(Request $request, Perusahaan $perusahaan)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'sektor' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $perusahaan->update($validated);

        $user = Auth::user();
        if ($user && $user->role === 'super_admin') {
            return redirect()->route('perusahaan.index');
        }

        return redirect()->route('perusahaan.edit',$perusahaan)
        ->with('success','Data perusahaan berhasil diperbarui');
    }

    public function destroy(Perusahaan $perusahaan)
    {
        $perusahaan->delete();
        return redirect()->route('perusahaan.index');
    }
}
