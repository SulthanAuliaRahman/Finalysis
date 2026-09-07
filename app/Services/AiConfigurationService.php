<?php

namespace App\Services;

use App\Models\AiConfiguration;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AiConfigurationService
{
    /**
     * Ambil konfigurasi AI yang sedang aktif saat ini.
     */
    public function get(): AiConfiguration
    {
        $config = AiConfiguration::active();

        if (!$config) {
            throw new \RuntimeException(
                'Tidak ada konfigurasi AI. Silakan buat konfigurasi terlebih dahulu di menu Pengaturan AI.'
            );
        }

        return $config;
    }

    /**
     * Ambil semua konfigurasi, aktif di urutan pertama.
     */
    public function getAllOrdered()
    {
        return AiConfiguration::orderBy('is_active', 'desc')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Siapkan data konfigurasi untuk form edit (mask api key).
     */
    public function prepareForEdit(AiConfiguration $configuration): array
    {
        $configData = $configuration->toArray();
        $configData['has_api_key'] = !empty($configuration->llm_api_key);
        $configData['llm_api_key'] = ''; // Masking untuk keamanan frontend

        return $configData;
    }

    /**
     * Buat konfigurasi baru.
     */
    public function create(array $data): AiConfiguration
    {
        $data = $this->normalizeProviderFields($data);

        // Jika belum ada config lain, set otomatis sebagai aktif
        $data['is_active'] = AiConfiguration::count() === 0;

        return AiConfiguration::create($data);
    }

    /**
     * Update konfigurasi yang sudah ada.
     */
    public function update(AiConfiguration $configuration, array $data): AiConfiguration
    {
        $data = $this->normalizeProviderFields($data, forUpdate: true);

        $configuration->update($data);

        return $configuration;
    }

    /**
     * Hapus konfigurasi. Melempar exception jika konfigurasi sedang aktif.
     */
    public function delete(AiConfiguration $configuration): void
    {
        if ($configuration->is_active) {
            throw ValidationException::withMessages([
                'general' => 'Tidak bisa menghapus konfigurasi yang sedang aktif. Aktifkan konfigurasi lain terlebih dahulu.',
            ]);
        }

        $configuration->delete();
    }

    /**
     * Aktifkan konfigurasi tertentu, nonaktifkan sisanya.
     */
    public function activate(AiConfiguration $configuration): void
    {
        DB::transaction(function () use ($configuration) {
            AiConfiguration::query()->update(['is_active' => false]);
            $configuration->update(['is_active' => true]);
        });
    }

    /**
     * Normalisasi field terkait provider: ollama pakai base_url,
     * provider lain pakai api_key.
     */
    private function normalizeProviderFields(array $data, bool $forUpdate = false): array
    {
        if ($data['llm_provider'] === 'ollama') {
            $data['llm_api_key'] = null;
        } else {
            $data['base_url'] = null;

            if ($forUpdate && empty($data['llm_api_key'])) {
                unset($data['llm_api_key']);
            }
        }

        return $data;
    }
}
