<?php

namespace App\Services;

use App\Models\AiConfiguration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AiConfigurationService
{
    /**
     * Ambil config aktif yang siap dipakai.
     * Jika sedang limited, otomatis switch ke config berikutnya.
     */
    public function get(): AiConfiguration
    {
        $config = AiConfiguration::resolveActiveConfig();

        if (!$config) {
            throw new \RuntimeException(
                'Tidak ada konfigurasi AI yang tersedia. Semua API sedang rate-limited atau belum ada konfigurasi.'
            );
        }

        return $config;
    }

    /**
     * Tangani rate limit pada config tertentu.
     * Mark config sebagai limited, lalu coba switch ke config berikutnya.
     *
     * @return AiConfiguration|null Config pengganti, atau null jika semua limited
     */
    public function handleRateLimit(AiConfiguration $config, int $cooldownMinutes = 60): ?AiConfiguration
    {
        Log::warning("AI Config rate limited: [{$config->name}] ({$config->llm_provider}/{$config->llm_model}). Cooldown {$cooldownMinutes} menit.");

        $config->markAsLimited($cooldownMinutes);

        // Cari config pengganti
        $next = AiConfiguration::getNextAvailable($config);

        if ($next) {
            Log::info("Auto-switched ke AI Config: [{$next->name}] ({$next->llm_provider}/{$next->llm_model})");
        } else {
            Log::error("Semua AI Config sedang rate-limited. Tidak ada pengganti tersedia.");
        }

        $this->clearCache();

        return $next;
    }

    /**
     * Hapus semua cache terkait AI config.
     */
    public function clearCache(): void
    {
        Cache::forget('ai_configuration');
    }
}