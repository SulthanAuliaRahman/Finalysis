<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PythonTesterController;
use App\Http\Controllers\AiConfigurationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;

// Real Not Test

use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\AnalisisController;
use App\Http\Controllers\UserController;

//ROLE UMUM
Route::middleware(['auth'])->group(function(){

// Rute Pengelolaan Dokumen Perusahaan
    Route::get('/perusahaan/{perusahaan}/dokumen', [DokumenController::class, 'index'])->name('perusahaan.dokumen.index');
    Route::get('/perusahaan/{perusahaan}/dokumen/create', [DokumenController::class, 'create'])->name('perusahaan.dokumen.create');
    Route::post('/perusahaan/{perusahaan}/dokumen/create', [DokumenController::class, 'importExcel'])->name('perusahaan.dokumen.import-excel.store');
    Route::get('/perusahaan/{perusahaan}/dokumen/{dokumen}/detail', [DokumenController::class, 'detail'])->name('perusahaan.dokumen.detail');
    Route::delete('/perusahaan/{perusahaan}/dokumen/{dokumen}', [DokumenController::class, 'destroy'])->name('perusahaan.dokumen.destroy');

    // Rute Pengelolaan Analisis Perusahaan
    Route::get('/perusahaan/{perusahaan}/analisis', [AnalisisController::class, 'index'])->name('perusahaan.analisis.index');
    Route::get('/perusahaan/{perusahaan}/analisis/{analisis}', [AnalisisController::class, 'detail'])->name('perusahaan.analisis.detail');

    Route::get('/perusahaan/{perusahaan}/analisis/{analisis}/status', [AnalisisController::class, 'statusGenerate'])->name('analisis.status');

    Route::post('/perusahaan/{perusahaan}/analisis/{analisis}/regenerasi', [AnalisisController::class, 'generateAnalisis'])->name('perusahaan.analisis.regenerasi');
    Route::post('/perusahaan/{perusahaan}/analisis/{analisis}/generate', [AnalisisController::class, 'generateSeluruhAnalisis'])->name('analisis.generate');
    Route::get('/perusahaan/{perusahaan}/analisis/{analisis}/status', [AnalisisController::class, 'statusGenerate'])->name('analisis.status');

    //Settings
    Route::prefix('settings')->name('settings.')->group(function () {

        //Ai Configuration
        Route::get('/ai', [AiConfigurationController::class, 'index'])->name('ai.view');
        Route::get('/ai/create', [AiConfigurationController::class, 'create'])->name('ai.create');
        Route::post('/ai', [AiConfigurationController::class, 'store'])->name('ai.store');
        Route::get('/ai/edit', function () {
            $active = \App\Models\AiConfiguration::resolveActiveConfig();
            if ($active) {
                return redirect()->route('settings.ai.edit', $active->id);
            }
            return redirect()->route('settings.ai.view');
        });
        Route::put('/ai', [AiConfigurationController::class, 'update'])->name('ai.update.legacy');
        Route::get('/ai/{aiConfiguration}/edit', [AiConfigurationController::class, 'edit'])->name('ai.edit')->whereNumber('aiConfiguration');
        Route::put('/ai/{aiConfiguration}', [AiConfigurationController::class, 'update'])->name('ai.update')->whereNumber('aiConfiguration');
        Route::delete('/ai/{aiConfiguration}', [AiConfigurationController::class, 'destroy'])->name('ai.destroy')->whereNumber('aiConfiguration');
        Route::post('/ai/{aiConfiguration}/activate', [AiConfigurationController::class, 'activate'])->name('ai.activate')->whereNumber('aiConfiguration');
        Route::post('/ai/{aiConfiguration}/reset-limit', [AiConfigurationController::class, 'resetLimit'])->name('ai.resetLimit')->whereNumber('aiConfiguration');

    });


    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

});

//ROLE SUPER ADMIN
Route::middleware(['auth','role:super_admin'])->group(function(){
    // CRUD Perusahaan
    Route::get('/perusahaan', [PerusahaanController::class, 'index'])->name('perusahaan.index');
    Route::get('/perusahaan/create', [PerusahaanController::class, 'create'])->name('perusahaan.create');
    Route::post('/perusahaan', [PerusahaanController::class, 'store'])->name('perusahaan.store');
    Route::get('/perusahaan/{perusahaan}/edit', [PerusahaanController::class, 'edit'])->name('perusahaan.edit');
    Route::put('/perusahaan/{perusahaan}', [PerusahaanController::class, 'update'])->name('perusahaan.update');
    Route::delete('/perusahaan/{perusahaan}', [PerusahaanController::class, 'destroy'])->name('perusahaan.destroy');

    // CRUD USER
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});


    // Ganti rute lama ini
    Route::get('/', function () {
        return Inertia::render('LandingPage');
    });

require __DIR__.'/auth.php';
