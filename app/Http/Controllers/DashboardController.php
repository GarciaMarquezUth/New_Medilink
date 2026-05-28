<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $today = now()->toDateString();

        $data = [
            'user' => $user,
            'today' => $today,
            'roleLabel' => $this->roleLabel($user),
        ];

        if ($user->hasRole('medico')) {
            return view('dashboard', $data + $this->medicoDashboard($user, $today));
        }

        if ($user->hasRole('paciente')) {
            return view('dashboard', $data + $this->pacienteDashboard($user, $today));
        }

        return view('dashboard', $data + $this->operacionDashboard($user, $today));
    }

    private function medicoDashboard(User $user, string $today): array
    {
        $medico = Medico::with('disponibilidades')
            ->where('user_id', $user->id)
            ->first();

        $baseQuery = Cita::with(['paciente', 'servicio'])
            ->when(
                $medico,
                fn ($query) => $query->where('medico_id', $medico->id),
                fn ($query) => $query->whereRaw('1 = 0')
            );

        $citasHoy = (clone $baseQuery)
            ->whereDate('fecha_hora', $today)
            ->orderBy('fecha_hora')
            ->get();

        $proximaCita = (clone $baseQuery)
            ->whereIn('estado', ['agendada', 'confirmada'])
            ->where('fecha_hora', '>=', now())
            ->orderBy('fecha_hora')
            ->first();

        $citasRecientes = (clone $baseQuery)
            ->whereIn('estado', ['atendida', 'no_show'])
            ->latest('fecha_hora')
            ->limit(5)
            ->get();

        return [
            'dashboardType' => 'medico',
            'medico' => $medico,
            'citasHoy' => $citasHoy,
            'proximaCita' => $proximaCita,
            'citasRecientes' => $citasRecientes,
            'resumenMedico' => [
                'hoy' => $citasHoy->count(),
                'pendientes' => $citasHoy->whereIn('estado', ['agendada', 'confirmada'])->count(),
                'atendidas' => $citasHoy->where('estado', 'atendida')->count(),
                'no_show' => $citasHoy->where('estado', 'no_show')->count(),
            ],
        ];
    }

    private function pacienteDashboard(User $user, string $today): array
    {
        $paciente = Paciente::where('user_id', $user->id)->first();

        $baseQuery = Cita::with(['medico', 'servicio'])
            ->when(
                $paciente,
                fn ($query) => $query->where('paciente_id', $paciente->id),
                fn ($query) => $query->whereRaw('1 = 0')
            );

        $proximaCita = (clone $baseQuery)
            ->whereIn('estado', ['agendada', 'confirmada'])
            ->where('fecha_hora', '>=', now())
            ->orderBy('fecha_hora')
            ->first();

        $proximasCitas = (clone $baseQuery)
            ->whereIn('estado', ['agendada', 'confirmada'])
            ->where('fecha_hora', '>=', Carbon::parse($today)->startOfDay())
            ->orderBy('fecha_hora')
            ->limit(5)
            ->get();

        $historialCitas = (clone $baseQuery)
            ->whereIn('estado', ['atendida', 'cancelada', 'no_show'])
            ->latest('fecha_hora')
            ->limit(5)
            ->get();

        return [
            'dashboardType' => 'paciente',
            'paciente' => $paciente,
            'proximaCita' => $proximaCita,
            'proximasCitas' => $proximasCitas,
            'historialCitas' => $historialCitas,
            'resumenPaciente' => [
                'proximas' => $proximasCitas->count(),
                'confirmadas' => $proximasCitas->where('estado', 'confirmada')->count(),
                'historial' => $historialCitas->count(),
            ],
        ];
    }

    private function operacionDashboard(User $user, string $today): array
    {
        $citasHoy = Cita::with(['medico', 'paciente', 'servicio'])
            ->whereDate('fecha_hora', $today)
            ->orderBy('fecha_hora')
            ->limit(8)
            ->get();

        $pagosPendientes = Cita::with(['medico', 'paciente', 'servicio'])
            ->where('estado_pago', 'pendiente')
            ->whereIn('estado', ['agendada', 'confirmada', 'atendida'])
            ->orderBy('fecha_hora')
            ->limit(6)
            ->get();

        $pagosRealizados = Cita::with(['medico', 'paciente', 'servicio'])
            ->where('estado_pago', 'pagado')
            ->latest('fecha_pago')
            ->limit(6)
            ->get();

        return [
            'dashboardType' => 'operacion',
            'showRecepcionPayments' => $user->hasRole('recepcionista'),
            'totalMedicos' => Medico::count(),
            'totalPacientes' => Paciente::count(),
            'totalCitas' => Cita::count(),
            'citasHoy' => $citasHoy,
            'citasPendientes' => Cita::whereIn('estado', ['agendada', 'confirmada'])->count(),
            'citasCanceladasHoy' => Cita::whereDate('fecha_hora', $today)->where('estado', 'cancelada')->count(),
            'pagosPendientes' => $pagosPendientes,
            'pagosRealizados' => $pagosRealizados,
            'totalPagosPendientes' => Cita::where('estado_pago', 'pendiente')
                ->whereIn('estado', ['agendada', 'confirmada', 'atendida'])
                ->count(),
            'totalPagosRealizados' => Cita::where('estado_pago', 'pagado')->count(),
            'montoPagadoHoy' => Cita::where('estado_pago', 'pagado')
                ->whereDate('fecha_pago', $today)
                ->sum('monto_pagado'),
        ];
    }

    private function roleLabel(User $user): string
    {
        if ($user->hasRole('admin')) {
            return 'Administrador';
        }

        if ($user->hasRole('recepcionista')) {
            return 'Recepcion';
        }

        if ($user->hasRole('medico')) {
            return 'Medico';
        }

        if ($user->hasRole('paciente')) {
            return 'Paciente';
        }

        return 'Usuario';
    }
}
