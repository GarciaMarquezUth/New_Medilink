<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $estadoLabels = Cita::estados();
        $estadosOcupantes = Cita::estadosOcupantes();
        $isPacienteDashboard = $user->hasRole('paciente') && ! $user->hasAnyRole(['admin', 'recepcionista', 'medico']);
        $isMedicoDashboard = $user->hasRole('medico') && ! $user->hasAnyRole(['admin', 'recepcionista']);
        $estadoClasses = static fn ($estado) => match ($estado) {
            Cita::ESTADO_ATENDIDA => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
            Cita::ESTADO_NO_SHOW => 'bg-orange-50 text-orange-700 ring-orange-100',
            Cita::ESTADO_CANCELADA => 'bg-rose-50 text-rose-700 ring-rose-100',
            Cita::ESTADO_CONFIRMADA => 'bg-blue-50 text-blue-700 ring-blue-100',
            default => 'bg-amber-50 text-amber-700 ring-amber-100',
        };
        $canCancelCita = static fn (Cita $cita) => in_array($cita->estado, $estadosOcupantes, true) && $cita->fecha_hora->isFuture();

        $data = compact(
            'user',
            'estadoLabels',
            'estadosOcupantes',
            'isPacienteDashboard',
            'isMedicoDashboard',
            'estadoClasses',
            'canCancelCita',
        );

        if ($isPacienteDashboard) {
            $data += $this->patientDashboardData($user, $estadosOcupantes);
        } elseif ($isMedicoDashboard) {
            $data += $this->doctorDashboardData($user, $estadosOcupantes);
        } else {
            $data += $this->administrativeDashboardData($estadosOcupantes);
        }

        return view('dashboard', $data);
    }

    private function patientDashboardData(User $user, array $estadosOcupantes): array
    {
        $pacientePerfil = Paciente::where('user_id', $user->id)->first();
        $perfilPacienteIncompleto = ! $pacientePerfil?->isProfileComplete();
        $citasPaciente = Cita::with(['medico', 'servicio'])
            ->whereHas('paciente', fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('fecha_hora')
            ->get();

        $proximasCitas = $citasPaciente
            ->filter(fn (Cita $cita) => $cita->fecha_hora->isFuture() && in_array($cita->estado, $estadosOcupantes, true))
            ->values();

        $historialCitas = $citasPaciente
            ->reject(fn (Cita $cita) => $cita->fecha_hora->isFuture() && in_array($cita->estado, $estadosOcupantes, true))
            ->sortByDesc('fecha_hora')
            ->values();

        return [
            'pacientePerfil' => $pacientePerfil,
            'perfilPacienteIncompleto' => $perfilPacienteIncompleto,
            'citasPaciente' => $citasPaciente,
            'proximasCitas' => $proximasCitas,
            'historialCitas' => $historialCitas,
            'citasCanceladas' => $citasPaciente->where('estado', Cita::ESTADO_CANCELADA)->count(),
        ];
    }

    private function doctorDashboardData(User $user, array $estadosOcupantes): array
    {
        $medico = Medico::with('servicios')->where('user_id', $user->id)->first();
        $citasMedico = $medico
            ? Cita::with(['paciente', 'servicio'])
                ->where('medico_id', $medico->id)
                ->orderBy('fecha_hora')
                ->get()
            : collect();

        $pacientesMedico = $citasMedico
            ->pluck('paciente')
            ->filter()
            ->unique('id')
            ->sortBy([['apellido', 'asc'], ['nombre', 'asc']])
            ->values();

        return [
            'medico' => $medico,
            'citasMedico' => $citasMedico,
            'citasHoy' => $citasMedico->filter(fn (Cita $cita) => $cita->fecha_hora->isToday())->values(),
            'proximasCitasMedico' => $citasMedico
                ->filter(fn (Cita $cita) => $cita->fecha_hora->isFuture() && in_array($cita->estado, $estadosOcupantes, true))
                ->values(),
            'citasPendientesMedico' => $citasMedico
                ->filter(fn (Cita $cita) => in_array($cita->estado, $estadosOcupantes, true))
                ->values(),
            'citasAtendidasMedico' => $citasMedico->where('estado', Cita::ESTADO_ATENDIDA)->count(),
            'pacientesMedico' => $pacientesMedico,
            'pacientesMedicoData' => $pacientesMedico->map(function (Paciente $paciente) use ($citasMedico, $estadosOcupantes) {
                $citasDelPaciente = $citasMedico->where('paciente_id', $paciente->id);
                $ultima = $citasDelPaciente
                    ->filter(fn (Cita $cita) => $cita->fecha_hora->isPast() || ! in_array($cita->estado, $estadosOcupantes, true))
                    ->sortByDesc('fecha_hora')
                    ->first();
                $proxima = $citasDelPaciente
                    ->filter(fn (Cita $cita) => $cita->fecha_hora->isFuture() && in_array($cita->estado, $estadosOcupantes, true))
                    ->sortBy('fecha_hora')
                    ->first();

                return [
                    'paciente' => $paciente,
                    'ultima' => $ultima,
                    'proxima' => $proxima,
                    'estado_cita' => $proxima ?: $ultima ?: $citasDelPaciente->sortByDesc('fecha_hora')->first(),
                ];
            })->values(),
        ];
    }

    private function administrativeDashboardData(array $estadosOcupantes): array
    {
        return [
            'totalMedicos' => Medico::count(),
            'totalPacientes' => Paciente::count(),
            'totalCitas' => Cita::count(),
            'citasPendientes' => Cita::whereIn('estado', $estadosOcupantes)->count(),
        ];
    }
}
