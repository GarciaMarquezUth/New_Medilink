<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\CitaController; // <--- Importa el controlador de Citas

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
        Route::resource('citas', CitaController::class); // <--- Nueva línea para Citas
    });
});

require __DIR__.'/auth.php';

// Agregar al archivo web.php existente
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);