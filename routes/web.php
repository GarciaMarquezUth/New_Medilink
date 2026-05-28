<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PortalCitaController;
use Illuminate\Support\Facades\Route;

// Redirección inicial
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas de autenticación
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/portal-paciente/citas', [PortalCitaController::class, 'index'])->name('portal.citas.index');

// Grupo de rutas protegidas
Route::middleware(['auth', 'verified'])->group(function () {

    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');

    Route::prefix('dashboard')->group(function () {
        Route::resource('medicos', MedicoController::class)
            ->except(['show'])
            ->middleware('role:admin|recepcionista');

        Route::resource('pacientes', PacienteController::class)
            ->except(['show'])
            ->middleware('role:admin|recepcionista');

        Route::get('citas', [CitaController::class, 'index'])->name('citas.index');

        Route::middleware(['role:admin|recepcionista'])->group(function () {
            Route::get('citas/create', [CitaController::class, 'create'])->name('citas.create');
            Route::post('citas', [CitaController::class, 'store'])->name('citas.store');
            Route::get('citas/{cita}/edit', [CitaController::class, 'edit'])->name('citas.edit');
            Route::match(['put', 'patch'], 'citas/{cita}', [CitaController::class, 'update'])->name('citas.update');
            Route::delete('citas/{cita}', [CitaController::class, 'destroy'])->name('citas.destroy');
        });

        // --- RUTAS PERSONALIZADAS PARA EL MÉDICO ---
        // Estas van dentro del grupo dashboard para mantener el prefijo
        Route::middleware(['role:medico'])->group(function () {
            Route::post('citas/{id}/atendida', [CitaController::class, 'marcarAtendida'])->name('citas.atendida');
            Route::post('citas/{id}/no-presentada', [CitaController::class, 'noPresentada'])->name('citas.no-presentada');
        });
    });
});

require __DIR__.'/auth.php';
