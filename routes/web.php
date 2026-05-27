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

// Rutas de login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Ruta temporal
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');