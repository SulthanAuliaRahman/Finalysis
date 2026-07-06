<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAiConfigurationRequest;
use App\Models\AiConfiguration;
use App\Services\AiConfigurationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AiConfigurationController extends Controller
{
    public function index(){
        $configuration = AiConfiguration::first();

        return Inertia::render(
            'Settings/AiConfiguration/Index',
            compact('configuration')
        );
    }

    public function edit(){
        $configuration = AiConfiguration::first();

        return Inertia::render(
            'Settings/AiConfiguration/Edit',
            compact('configuration')
            );
    }

    public function update(UpdateAiConfigurationRequest $request, AiConfigurationService $service){
        $configuration = AiConfiguration::firstOrfail();
        $configuration->update($request->validated());

        $service->clearCache();

        return redirect()->route('settings.ai.view')->with('success','Configuration Updated');
    }


}
