<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\PacienteController; // <--- Importa el nuevo controlador

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
        Route::resource('pacientes', PacienteController::class); // <--- Nueva línea para Pacientes
    });
});

require __DIR__.'/auth.php';

use App\Http\Controllers\Admin\ServiceController;

// Rutas protegidas para administradores y recepcionistas
Route::middleware(['auth', 'role:admin,receptionist'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
    
    // CRUD completo de Servicios
    Route::resource('services', ServiceController::class);
});