<?php

namespace App\Services;
use App\Models\AiConfiguration;
use Illuminate\Support\Facades\Cache;

class AiConfigurationService{
    public function get(): AiConfiguration{
        return Cache::rememberForever(
            'ai_configuration',
            fn() => AiConfiguration::firstOrFail()
            );
    }

    public function clearCache(): void{
        Cache::forget('ai_configuration');
    }
}