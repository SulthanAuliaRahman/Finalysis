<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DocumentController;

use App\Http\Controllers\PythonTesterController;

Route::prefix('python-tester')->name('python-tester.')->group(function () {

    Route::get('/',        [PythonTesterController::class, 'index'])->name('index');
    Route::get('/health',  [PythonTesterController::class, 'health'])->name('health');

    // Chunking
    Route::post('/ingest', [PythonTesterController::class, 'ingest'])->name('ingest');

    // Extractor
    Route::get('/extract',      [PythonTesterController::class, 'index2'])->name('index2');
    Route::post('/extract/run', [PythonTesterController::class, 'extract'])->name('extract');
});




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
