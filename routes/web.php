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

use App\Http\Controllers\Auth\LoginController;


Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/password/reset', function () {
    return view('auth.passwords.reset');
})->name('password.request');

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});