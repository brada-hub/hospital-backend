<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::view('about', 'about')->name('about');

    Route::get('users', [UserController::class, 'index'])->name('web.users.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- RUTA TEMPORAL PARA SEEDER (Public) ---
// --- RUTA TEMPORAL PARA RESETEAR DB (PELIGROSA) ---
Route::get('/reset-db-production-999', function () {
    try {
        // 1. Borrar todas las tablas
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
            '--force' => true,
            '--seed' => true // 2. Ejecutar seeders inmediatamente
        ]);

        return 'ÉXITO TOTAL: Se ha borrado la base de datos y se ha vuelto a llenar con los nuevos datos (Fresh + Seed).';
    } catch (\Exception $e) {
        return 'ERROR CRÍTICO: ' . $e->getMessage() . ' <br> Trace: ' . $e->getTraceAsString();
    }
});

require __DIR__.'/auth.php';
