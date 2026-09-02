<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAiConfigurationRequest;
use App\Models\AiConfiguration;
use App\Services\AiConfigurationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AiConfigurationController extends Controller
{
    public function index()
    {
        $configuration = AiConfiguration::firstOrCreate([
            'llm_provider' => 'gemini',
            'llm_model'    => 'gemini-1.5-pro',
        ]);

        return Inertia::render(
            'Settings/AiConfiguration/Index',
            compact('configuration')
        );
    }

    public function edit()
    {
        $configuration = AiConfiguration::firstOrCreate([
            'llm_provider' => 'gemini',
            'llm_model'    => 'gemini-1.5-pro',
        ]);

        $configData = $configuration->toArray();
        $configData['has_api_key'] = !empty($configuration->llm_api_key);
        $configData['llm_api_key'] = ''; // Mask for security on frontend

        return Inertia::render(
            'Settings/AiConfiguration/Edit',
            [
                'configuration' => $configData,
            ]
        );
    }

    public function update(UpdateAiConfigurationRequest $request, AiConfigurationService $service)
    {
        $configuration = AiConfiguration::firstOrCreate([]);

        $data = $request->validated();

        if ($data['llm_provider'] === 'ollama') {
            $data['llm_api_key'] = null;
        } else {
            $data['base_url'] = null;

            if (empty($data['llm_api_key'])) {
                unset($data['llm_api_key']);
            }
        }

        $configuration->update($data);

        $service->clearCache();

        return redirect()->route('settings.ai.view')->with('success', 'Configuration Updated');
    }


}
