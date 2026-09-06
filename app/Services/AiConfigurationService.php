<?php

namespace App\Services;

use App\Models\AiConfiguration;

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
}