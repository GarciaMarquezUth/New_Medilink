<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\DisponibilidadController; // <--- Importa el controlador

// Ruta pública
Route::view('/', 'welcome');

// Grupo de rutas protegidas por autenticación
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Rutas directas del dashboard
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');

    // CRUDs bajo el prefijo /dashboard/
    Route::prefix('dashboard')->group(function () {
        Route::resource('medicos', MedicoController::class);
        Route::resource('pacientes', PacienteController::class);
        Route::resource('citas', CitaController::class);
        Route::resource('servicios', ServicioController::class);
        
        // Rutas anidadas para disponibilidad (index, store, destroy)
        Route::resource('medicos.disponibilidades', DisponibilidadController::class)
             ->only(['index', 'store', 'destroy']);
    });
});

require __DIR__.'/auth.php';