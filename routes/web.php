<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PythonTesterController;

use App\Http\Controllers\HomeController;

// Real Not Test
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\DokumenController;


Route::prefix('python-tester')->name('python-tester.')->group(function () {

    Route::get('/',        [PythonTesterController::class, 'index'])->name('index');
    Route::get('/health',  [PythonTesterController::class, 'health'])->name('health');

    // Chunking
    Route::post('/ingest', [PythonTesterController::class, 'ingest'])->name('ingest');

    //embedd
    Route::post('/embed', [PythonTesterController::class, 'embed'])->name('embed');

    Route::get('/chat',       [PythonTesterController::class, 'chatPage'])->name('chat');
    Route::post('/chat/ask',  [PythonTesterController::class, 'chatAsk'])->name('chat.ask');

    // Extractor
    Route::get('/extract',      [PythonTesterController::class, 'index2'])->name('index2');
    Route::post('/extract/run', [PythonTesterController::class, 'extract'])->name('extract');
});



// Antarmuka Utama & Operasi CRUD Perusahaan (Halaman Terpisah)
Route::get('/perusahaan', [PerusahaanController::class, 'index'])->name('perusahaan.index');
Route::get('/perusahaan/create', [PerusahaanController::class, 'create'])->name('perusahaan.create');
Route::post('/perusahaan', [PerusahaanController::class, 'store'])->name('perusahaan.store');
Route::get('/perusahaan/{perusahaan}/edit', [PerusahaanController::class, 'edit'])->name('perusahaan.edit');
Route::put('/perusahaan/{perusahaan}', [PerusahaanController::class, 'update'])->name('perusahaan.update');
Route::delete('/perusahaan/{perusahaan}', [PerusahaanController::class, 'destroy'])->name('perusahaan.destroy');

// Rute Pengelolaan Dokumen Perusahaan
Route::get('/perusahaan/{perusahaan}/dokumen', [DokumenController::class, 'index'])->name('perusahaan.dokumen.index');
Route::get('/perusahaan/{perusahaan}/dokumen/create', [DokumenController::class, 'create'])->name('perusahaan.dokumen.create');
Route::post('/perusahaan/{perusahaan}/dokumen', [DokumenController::class, 'store'])->name('perusahaan.dokumen.store');

// Halaman Alur Proses RAG Terpisah
Route::get('/perusahaan/{perusahaan}/dokumen/{dokumen}/review', [DokumenController::class, 'review'])->name('perusahaan.dokumen.review');
Route::get('/perusahaan/{perusahaan}/dokumen/{dokumen}/view-pdf', [DokumenController::class, 'viewPdf'])->name('perusahaan.dokumen.view-pdf');

Route::post('/perusahaan/{perusahaan}/dokumen/{dokumen}/chunk', [DokumenController::class, 'chunk'])->name('perusahaan.dokumen.chunk');

Route::get('/perusahaan/{perusahaan}/dokumen/{dokumen}/embed', [DokumenController::class, 'embedPage'])->name('perusahaan.dokumen.embed');
Route::post('/perusahaan/{perusahaan}/dokumen/{dokumen}/embed', [DokumenController::class, 'startEmbedding'])->name('perusahaan.dokumen.embed.run');

Route::get('/perusahaan/{perusahaan}/dokumen/{dokumen}/chunks', [DokumenController::class, 'showChunks'])->name('perusahaan.dokumen.chunks');
Route::delete('/perusahaan/{perusahaan}/dokumen/{dokumen}', [DokumenController::class, 'destroy'])->name('perusahaan.dokumen.destroy');



Route::get('/python-health', [DokumenController::class, 'checkPythonHealth'])->name('python.health');
Route::get('/documents/upload', [DocumentController::class, 'create'])->name('documents.create');
Route::post('/documents',       [DocumentController::class, 'store'])->name('documents.store');
Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
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
