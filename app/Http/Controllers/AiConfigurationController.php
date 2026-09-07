<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAiConfigurationRequest;
use App\Http\Requests\UpdateAiConfigurationRequest;
use App\Models\AiConfiguration;
use App\Services\AiConfigurationService;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AiConfigurationController extends Controller
{
    public function __construct(
        private readonly AiConfigurationService $aiConfigurationService
    ) {}


    public function index()
    {
        return Inertia::render('Settings/AiConfiguration/Index', [
            'configurations' => $this->aiConfigurationService->getAllOrdered(),
        ]);
    }


    public function create()
    {
        return Inertia::render('Settings/AiConfiguration/Edit', [
            'configuration' => null,
            'mode'          => 'create',
        ]);
    }

    public function store(StoreAiConfigurationRequest $request)
    {
        $this->aiConfigurationService->create($request->validated());

        return redirect()->route('settings.ai.view')->with('success', 'Konfigurasi AI berhasil ditambahkan.');
    }

    public function edit(AiConfiguration $aiConfiguration)
    {
        return Inertia::render('Settings/AiConfiguration/Edit', [
            'configuration' => $this->aiConfigurationService->prepareForEdit($aiConfiguration),
            'mode'          => 'edit',
        ]);
    }

    public function update(UpdateAiConfigurationRequest $request, AiConfiguration $aiConfiguration)
    {
        $this->aiConfigurationService->update($aiConfiguration, $request->validated());

        return redirect()->route('settings.ai.view')->with('success', 'Konfigurasi AI berhasil diperbarui.');
    }

    public function destroy(AiConfiguration $aiConfiguration)
    {
        try {
            $this->aiConfigurationService->delete($aiConfiguration);
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first('general'));
        }

        return redirect()->route('settings.ai.view')->with('success', 'Konfigurasi AI berhasil dihapus.');
    }

    /**
     * Manual switch: Aktifkan konfigurasi tertentu (deaktivasi konfigurasi lain).
     */
    public function activate(AiConfiguration $aiConfiguration)
    {
        $this->aiConfigurationService->activate($aiConfiguration);

        return back()->with('success', "Konfigurasi \"{$aiConfiguration->name}\" berhasil diaktifkan.");
    }
}
