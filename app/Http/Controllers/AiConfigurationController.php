<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAiConfigurationRequest;
use App\Http\Requests\UpdateAiConfigurationRequest;
use App\Models\AiConfiguration;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AiConfigurationController extends Controller
{
    /**
     * Tampilkan daftar semua konfigurasi AI.
     */
    public function index()
    {
        $configurations = AiConfiguration::orderBy('is_active', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        return Inertia::render(
            'Settings/AiConfiguration/Index',
            ['configurations' => $configurations]
        );
    }

    /**
     * Form untuk membuat konfigurasi baru.
     */
    public function create()
    {
        return Inertia::render(
            'Settings/AiConfiguration/Edit',
            [
                'configuration' => null,
                'mode'          => 'create',
            ]
        );
    }

    /**
     * Simpan konfigurasi baru.
     */
    public function store(StoreAiConfigurationRequest $request)
    {
        $data = $request->validated();

        if ($data['llm_provider'] === 'ollama') {
            $data['llm_api_key'] = null;
        } else {
            $data['base_url'] = null;
        }

        // Jika belum ada config lain, set otomatis sebagai aktif
        $data['is_active'] = AiConfiguration::count() === 0;

        AiConfiguration::create($data);

        return redirect()->route('settings.ai.view')->with('success', 'Konfigurasi AI berhasil ditambahkan.');
    }

    /**
     * Form untuk edit konfigurasi.
     */
    public function edit(AiConfiguration $aiConfiguration)
    {
        $configData = $aiConfiguration->toArray();
        $configData['has_api_key'] = !empty($aiConfiguration->llm_api_key);
        $configData['llm_api_key'] = ''; // Masking untuk keamanan frontend

        return Inertia::render(
            'Settings/AiConfiguration/Edit',
            [
                'configuration' => $configData,
                'mode'          => 'edit',
            ]
        );
    }

    /**
     * Update konfigurasi yang sudah ada.
     */
    public function update(UpdateAiConfigurationRequest $request, AiConfiguration $aiConfiguration)
    {
        $data = $request->validated();

        if ($data['llm_provider'] === 'ollama') {
            $data['llm_api_key'] = null;
        } else {
            $data['base_url'] = null;

            if (empty($data['llm_api_key'])) {
                unset($data['llm_api_key']);
            }
        }

        $aiConfiguration->update($data);

        return redirect()->route('settings.ai.view')->with('success', 'Konfigurasi AI berhasil diperbarui.');
    }

    /**
     * Hapus konfigurasi. Tidak bisa hapus konfigurasi yang sedang aktif.
     */
    public function destroy(AiConfiguration $aiConfiguration)
    {
        if ($aiConfiguration->is_active) {
            return back()->with('error', 'Tidak bisa menghapus konfigurasi yang sedang aktif. Aktifkan konfigurasi lain terlebih dahulu.');
        }

        $aiConfiguration->delete();

        return redirect()->route('settings.ai.view')->with('success', 'Konfigurasi AI berhasil dihapus.');
    }

    /**
     * Manual switch: Aktifkan konfigurasi tertentu (deaktivasi konfigurasi lain).
     */
    public function activate(AiConfiguration $aiConfiguration)
    {
        DB::transaction(function () use ($aiConfiguration) {
            AiConfiguration::query()->update(['is_active' => false]);
            $aiConfiguration->update(['is_active' => true]);
        });

        return back()->with('success', "Konfigurasi \"{$aiConfiguration->name}\" berhasil diaktifkan.");
    }
}
