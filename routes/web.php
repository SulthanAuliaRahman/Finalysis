<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PythonTesterController;
use App\Http\Controllers\AiConfigurationController;
use App\Http\Controllers\HomeController;

// Real Not Test

use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\AnalisisController;

// CRUD Perusahaan
Route::get('/perusahaan', [PerusahaanController::class, 'index'])->name('perusahaan.index');
Route::get('/perusahaan/create', [PerusahaanController::class, 'create'])->name('perusahaan.create');
Route::post('/perusahaan', [PerusahaanController::class, 'store'])->name('perusahaan.store');
Route::get('/perusahaan/{perusahaan}/edit', [PerusahaanController::class, 'edit'])->name('perusahaan.edit');
Route::put('/perusahaan/{perusahaan}', [PerusahaanController::class, 'update'])->name('perusahaan.update');
Route::delete('/perusahaan/{perusahaan}', [PerusahaanController::class, 'destroy'])->name('perusahaan.destroy');

// Rute Pengelolaan Dokumen Perusahaan
Route::get('/perusahaan/{perusahaan}/dokumen', [DokumenController::class, 'index'])->name('perusahaan.dokumen.index');

// Halaman Alur Pros
Route::get('/python-health', [DokumenController::class, 'checkPythonHealth'])->name('python.health');

// Extract
Route::get('/perusahaan/{perusahaan}/dokumen/create', [DokumenController::class, 'create'])->name('perusahaan.dokumen.create');
Route::post('/perusahaan/{perusahaan}/dokumen', [DokumenController::class, 'store'])->name('perusahaan.dokumen.store');

//Review & StartChunking
Route::get('/perusahaan/{perusahaan}/dokumen/{dokumen}/view-pdf', [DokumenController::class, 'viewPdf'])->name('perusahaan.dokumen.view-pdf');
Route::get('/perusahaan/{perusahaan}/dokumen/{dokumen}/review', [DokumenController::class, 'review'])->name('perusahaan.dokumen.review');
Route::post('/perusahaan/{perusahaan}/dokumen/{dokumen}/chunk', [DokumenController::class, 'chunk'])->name('perusahaan.dokumen.chunk');

// Start Embedding
Route::get('/perusahaan/{perusahaan}/dokumen/{dokumen}/embed', [DokumenController::class, 'embedPage'])->name('perusahaan.dokumen.embed');
Route::post('/perusahaan/{perusahaan}/dokumen/{dokumen}/embed', [DokumenController::class, 'startEmbedding'])->name('perusahaan.dokumen.embed.run');

//Data Loading Done Sudah ada di vectorstore
Route::get('/perusahaan/{perusahaan}/dokumen/{dokumen}/chunks', [DokumenController::class, 'showChunks'])->name('perusahaan.dokumen.chunks');

Route::delete('/perusahaan/{perusahaan}/dokumen/{dokumen}', [DokumenController::class, 'destroy'])->name('perusahaan.dokumen.destroy');

// Rute Pengelolaan Analisis Perusahaan
Route::get('/perusahaan/{perusahaan}/analisis', [AnalisisController::class, 'index'])->name('perusahaan.analisis.index');
Route::get('/perusahaan/{perusahaan}/analisis/{analisis}', [AnalisisController::class, 'analisis'])->name('perusahaan.analisis.detail');

//Settings
Route::prefix('settings')->name('settings.')->group(function () {

    //Ai Configuration
    Route::get('/ai',[AiConfigurationController::class, 'index'])->name('ai.view');
    Route::get('/ai/edit',[AiConfigurationController::class, 'edit'])->name('ai.edit');
    Route::put('/ai',[AiConfigurationController::class, 'update'])->name('ai.update');

});

// Alur Proses Generate Analisis RAG
Route::post('/perusahaan/{perusahaan}/analisis/{analisis}/regenerasi', [AnalisisController::class, 'regenerasi'])->name('perusahaan.analisis.regenerasi');

// Ganti rute lama ini
Route::get('/', function () {
    return Inertia::render('LandingPage');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
