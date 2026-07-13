<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAiConfigurationRequest;
use App\Models\AiConfiguration;
use App\Services\AiConfigurationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AiConfigurationController extends Controller
{
    public function index(AiConfigurationService $service){
        $configuration = $service->get();

        return Inertia::render(
            'Settings/AiConfiguration/Index',
            [
                'configuration' => $configuration,
                'keyStatus' => [
                    'llm' => filled($configuration->llm_api_key),
                    'embedding' => filled($configuration->embedding_api_key),
                    'reranker' => filled($configuration->reranker_api_key),
                ],
            ]
        );
    }

    public function edit(AiConfigurationService $service){
        $configuration = $service->get();

        return Inertia::render(
            'Settings/AiConfiguration/Edit',
            compact('configuration')
            );
    }

    public function update(UpdateAiConfigurationRequest $request, AiConfigurationService $service){
        $configuration = $service->get();
        $validated = $request->validated();

        foreach (['llm_api_key', 'embedding_api_key', 'reranker_api_key'] as $keyField) {
            if (blank($validated[$keyField] ?? null)) {
                unset($validated[$keyField]);
            }
        }

        $configuration->update($validated);

        $service->clearCache();

        return redirect()->route('settings.ai.view')->with('success','Configuration Updated');
    }


}
