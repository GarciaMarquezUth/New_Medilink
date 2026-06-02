<?php

use App\Http\Controllers\CitaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\PacienteCitaController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\PortalCitaController;
use App\Http\Controllers\ServicioController;
use Illuminate\Support\Facades\Route;

// Redirección inicial
Route::get('/', function () {
    return redirect()->route('portal-citas.index');
});

// Portal publico para solicitud de citas
Route::get('/portal-citas', [PortalCitaController::class, 'create'])->name('portal-citas.index');
Route::get('/portal-citas/medicos/{medico}/servicios', [PortalCitaController::class, 'serviciosPorMedico'])->name('portal-citas.servicios');
Route::get('/portal-citas/medicos/{medico}/servicios/{servicio}/fechas', [PortalCitaController::class, 'fechasDisponibles'])->name('portal-citas.fechas');
Route::get('/portal-citas/medicos/{medico}/servicios/{servicio}/fechas/{fecha}/horarios', [PortalCitaController::class, 'horariosDisponibles'])
    ->where('fecha', '[0-9]{4}-[0-9]{2}-[0-9]{2}')
    ->name('portal-citas.horarios');
Route::post('/portal-citas', [PortalCitaController::class, 'store'])->name('portal-citas.store');
Route::middleware('auth')->group(function () {
    Route::get('/portal-citas/confirmar', [PortalCitaController::class, 'confirm'])->name('portal-citas.confirm');
    Route::post('/portal-citas/confirmar', [PortalCitaController::class, 'confirmStore'])->name('portal-citas.confirm.store');
});

// Grupo de rutas protegidas
Route::middleware('auth')->group(function () {

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::view('profile', 'profile')->name('profile');

    Route::prefix('dashboard')->group(function () {
        Route::middleware('role:admin')->group(function () {
            Route::get('permisos', [PermisoController::class, 'index'])->name('permisos.index');
            Route::put('permisos', [PermisoController::class, 'update'])->name('permisos.update');
        });

        Route::resource('medicos', MedicoController::class)
            ->except(['show'])
            ->middleware('role:admin|recepcionista|medico')
            ->middlewareFor('index', 'permission:medicos.ver')
            ->middlewareFor(['create', 'store'], 'permission:medicos.crear')
            ->middlewareFor(['edit', 'update'], 'permission:medicos.editar')
            ->middlewareFor('destroy', 'permission:medicos.eliminar');

        Route::middleware('role:medico')->group(function () {
            Route::get('mi-perfil-medico', [MedicoController::class, 'profile'])->name('medicos.profile');
            Route::put('mi-perfil-medico', [MedicoController::class, 'updateProfile'])->name('medicos.profile.update');
            Route::get('mis-pacientes/{paciente}', [PacienteController::class, 'showForMedico'])->name('medicos.pacientes.show');
        });

        Route::resource('pacientes', PacienteController::class)
            ->except(['show'])
            ->middleware('role:admin|recepcionista')
            ->middlewareFor('index', 'permission:pacientes.ver')
            ->middlewareFor(['create', 'store'], 'permission:pacientes.crear')
            ->middlewareFor(['edit', 'update'], 'permission:pacientes.editar')
            ->middlewareFor('destroy', 'permission:pacientes.eliminar');

        Route::resource('servicios', ServicioController::class)
            ->except(['show'])
            ->middleware('role:admin|recepcionista|medico')
            ->middlewareFor('index', 'permission:servicios.ver')
            ->middlewareFor(['create', 'store'], 'permission:servicios.crear')
            ->middlewareFor(['edit', 'update'], 'permission:servicios.editar')
            ->middlewareFor('destroy', 'permission:servicios.eliminar');

        Route::resource('disponibilidades', DisponibilidadController::class)
            ->except(['show'])
            ->parameters(['disponibilidades' => 'disponibilidad'])
            ->middleware('role:admin|recepcionista|medico')
            ->middlewareFor('index', 'permission:disponibilidades.ver')
            ->middlewareFor(['create', 'store'], 'permission:disponibilidades.crear')
            ->middlewareFor(['edit', 'update'], 'permission:disponibilidades.editar')
            ->middlewareFor('destroy', 'permission:disponibilidades.eliminar');

        Route::get('citas', [CitaController::class, 'index'])
            ->name('citas.index')
            ->middleware('role_or_permission:paciente|citas.ver');

        Route::middleware(['role:admin|recepcionista'])->group(function () {
            Route::get('citas/create', [CitaController::class, 'create'])->name('citas.create')->middleware('permission:citas.crear');
            Route::post('citas', [CitaController::class, 'store'])->name('citas.store')->middleware('permission:citas.crear');
            Route::delete('citas/{cita}', [CitaController::class, 'destroy'])->name('citas.destroy')->middleware('permission:citas.eliminar');
        });

        Route::middleware(['role:admin|recepcionista', 'permission:citas.editar'])->group(function () {
            Route::get('citas/{cita}/edit', [CitaController::class, 'edit'])->name('citas.edit');
            Route::match(['put', 'patch'], 'citas/{cita}', [CitaController::class, 'update'])->name('citas.update');
        });

        Route::post('citas/{cita}/cancelar', [CitaController::class, 'cancelarPaciente'])
            ->name('citas.cancelar-paciente')
            ->middleware('role:paciente');

        Route::middleware('role:paciente')->group(function () {
            Route::get('mi-perfil-paciente', [PacienteController::class, 'profile'])->name('pacientes.profile');
            Route::put('mi-perfil-paciente', [PacienteController::class, 'updateProfile'])->name('pacientes.profile.update');
            Route::get('mis-citas/crear', [PacienteCitaController::class, 'create'])->name('pacientes.citas.create');
            Route::post('mis-citas', [PacienteCitaController::class, 'store'])->name('pacientes.citas.store');
            Route::get('mis-citas/medicos/{medico}/servicios', [PacienteCitaController::class, 'serviciosPorMedico'])->name('pacientes.citas.servicios');
            Route::get('mis-citas/medicos/{medico}/servicios/{servicio}/fechas', [PacienteCitaController::class, 'fechasDisponibles'])->name('pacientes.citas.fechas');
            Route::get('mis-citas/medicos/{medico}/servicios/{servicio}/fechas/{fecha}/horarios', [PacienteCitaController::class, 'horariosDisponibles'])
                ->where('fecha', '[0-9]{4}-[0-9]{2}-[0-9]{2}')
                ->name('pacientes.citas.horarios');
        });

        // --- RUTAS PERSONALIZADAS PARA EL MÉDICO ---
        // Estas van dentro del grupo dashboard para mantener el prefijo
        Route::middleware(['role:medico', 'permission:citas.editar'])->group(function () {
            Route::post('citas/{id}/atendida', [CitaController::class, 'marcarAtendida'])->name('citas.atendida');
            Route::post('citas/{id}/no-presentada', [CitaController::class, 'noPresentada'])->name('citas.no-presentada');
        });
    });
});

require __DIR__.'/auth.php';
