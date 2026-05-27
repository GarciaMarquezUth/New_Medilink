<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Medico;
use App\Models\Cita;
use App\Models\Paciente;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Esto le dice a Laravel que cada vez que se cargue la vista 'dashboard',
        // envíe automáticamente estas variables con los conteos de la BD.
        View::composer('dashboard', function ($view) {
            $view->with([
                'totalMedicos' => Medico::count(),
                'citasPendientes' => Cita::count(),
                'totalPacientes' => Paciente::count(),
            ]);
        });
    }
}