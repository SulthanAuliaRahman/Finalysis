<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAiConfigurationRequest;
use App\Http\Requests\UpdateAiConfigurationRequest;
use App\Models\AiConfiguration;
use App\Services\AiConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AiConfigurationController extends Controller
{
    /**
     * Tampilkan daftar semua konfigurasi AI.
     */
    public function index()
    {
        $configurations = AiConfiguration::orderBy('priority', 'asc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($config) {
                $config->is_currently_limited = $config->isCurrentlyLimited();
                return $config;
            });

        return Inertia::render(
            'Settings/AiConfiguration/Index',
            compact('configurations')
        );
    }

    /**
     * Form untuk membuat konfigurasi baru.
     */
    public function create()
    {
        $existingCount = AiConfiguration::count();
        $maxPriority = AiConfiguration::max('priority') ?? 0;
        $nextPriority = max($maxPriority, $existingCount) + 1;

        // Daftar opsi prioritas: dari 1 sampai nextPriority
        $availablePriorities = range(1, $nextPriority);

        return Inertia::render(
            'Settings/AiConfiguration/Edit',
            [
                'configuration'       => null,
                'nextPriority'        => $nextPriority,
                'availablePriorities' => $availablePriorities,
                'mode'                => 'create',
            ]
        );
    }

    /**
     * Simpan konfigurasi baru.
     */
    public function store(StoreAiConfigurationRequest $request, AiConfigurationService $service)
    {
        $data = $request->validated();

        if ($data['llm_provider'] === 'ollama') {
            $data['llm_api_key'] = null;
        } else {
            $data['base_url'] = null;
        }

        // Jika belum ada config lain, set sebagai aktif
        $isFirst = AiConfiguration::count() === 0;
        $data['is_active'] = $isFirst;

        DB::transaction(function () use (&$data) {
            $newPriority = (int) ($data['priority'] ?? 1);

            // Cek apakah prioritas ini sudah dipakai config lain, jika ada pindahkan conflict ke slot terakhir
            $conflict = AiConfiguration::where('priority', $newPriority)->first();
            if ($conflict) {
                $maxPriority = AiConfiguration::max('priority') ?? 0;
                $conflict->update(['priority' => $maxPriority + 1]);
            }

            AiConfiguration::create($data);
        });

        $service->clearCache();

        return redirect()->route('settings.ai.view')->with('success', 'Konfigurasi AI berhasil ditambahkan.');
    }

    /**
     * Form untuk edit konfigurasi.
     */
    public function edit(AiConfiguration $aiConfiguration)
    {
        $configData = $aiConfiguration->toArray();
        $configData['has_api_key'] = !empty($aiConfiguration->llm_api_key);
        $configData['llm_api_key'] = ''; // Mask for security on frontend

        $existingCount = AiConfiguration::count();
        $maxPriority = max(AiConfiguration::max('priority') ?? 1, $existingCount);
        $availablePriorities = range(1, $maxPriority);

        return Inertia::render(
            'Settings/AiConfiguration/Edit',
            [
                'configuration'       => $configData,
                'nextPriority'        => $aiConfiguration->priority,
                'availablePriorities' => $availablePriorities,
                'mode'                => 'edit',
            ]
        );
    }

    /**
     * Update konfigurasi yang sudah ada.
     */
    public function update(UpdateAiConfigurationRequest $request, ?AiConfiguration $aiConfiguration = null, ?AiConfigurationService $service = null)
    {
        $service = $service ?? app(AiConfigurationService::class);
        $configuration = $aiConfiguration ?? AiConfiguration::resolveActiveConfig() ?? AiConfiguration::first();

        if (!$configuration) {
            $configuration = AiConfiguration::create([
                'name'      => 'Default',
                'is_active' => true,
                'priority'  => 1,
            ]);
        }

        $data = $request->validated();

        if (empty($data['name'])) {
            unset($data['name']);
        }
        if (!isset($data['priority'])) {
            unset($data['priority']);
        }

        if ($data['llm_provider'] === 'ollama') {
            $data['llm_api_key'] = null;
        } else {
            $data['base_url'] = null;

            if (empty($data['llm_api_key'])) {
                unset($data['llm_api_key']);
            }
        }

        DB::transaction(function () use ($configuration, $data) {
            // Jika ada perubahan prioritas, swap dengan config yang memegang prioritas tersebut
            if (isset($data['priority']) && (int) $data['priority'] !== (int) $configuration->priority) {
                $newPriority = (int) $data['priority'];
                $oldPriority = (int) $configuration->priority;

                $conflict = AiConfiguration::where('id', '!=', $configuration->id)
                    ->where('priority', $newPriority)
                    ->first();

                if ($conflict) {
                    $conflict->update(['priority' => $oldPriority]);
                }
            }

            $configuration->update($data);
        });

        $service->clearCache();

        return redirect()->route('settings.ai.view')->with('success', 'Konfigurasi AI berhasil diperbarui.');
    }

    /**
     * Hapus konfigurasi. Tidak bisa hapus yang sedang aktif.
     */
    public function destroy(AiConfiguration $aiConfiguration)
    {
        if ($aiConfiguration->is_active) {
            return back()->with('error', 'Tidak bisa menghapus konfigurasi yang sedang aktif. Aktifkan config lain terlebih dahulu.');
        }

        DB::transaction(function () use ($aiConfiguration) {
            $deletedPriority = $aiConfiguration->priority;
            $aiConfiguration->delete();

            // Geser urutan prioritas di atasnya agar tidak ada nomor yang bolong
            AiConfiguration::where('priority', '>', $deletedPriority)
                ->decrement('priority');
        });

        return redirect()->route('settings.ai.view')->with('success', 'Konfigurasi AI berhasil dihapus.');
    }

    /**
     * Aktifkan konfigurasi tertentu (deaktivasi yang lain).
     */
    public function activate(AiConfiguration $aiConfiguration, AiConfigurationService $service)
    {
        // Deaktivasi semua
        AiConfiguration::query()->update(['is_active' => false]);

        // Aktifkan yang dipilih
        $aiConfiguration->update(['is_active' => true]);

        $service->clearCache();

        return back()->with('success', "Konfigurasi \"{$aiConfiguration->name}\" berhasil diaktifkan.");
    }

    /**
     * Manual reset rate limit flag.
     */
    public function resetLimit(AiConfiguration $aiConfiguration, AiConfigurationService $service)
    {
        $aiConfiguration->clearLimit();

        $service->clearCache();

        return back()->with('success', "Rate limit untuk \"{$aiConfiguration->name}\" berhasil direset.");
    }
}
