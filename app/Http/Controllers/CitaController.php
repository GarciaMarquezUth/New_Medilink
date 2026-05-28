<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Servicio;
use App\Models\User;
use App\Services\AppointmentAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CitaController extends Controller
{
    public function __construct(private readonly AppointmentAvailabilityService $availabilityService) {}

    public function index(): View
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user && $user->hasAnyRole(['admin', 'recepcionista'])) {
            $citas = Cita::with(['medico', 'paciente', 'servicio'])->latest()->get();
        } elseif ($user && $user->hasRole('medico')) {
            $citas = Cita::with(['medico', 'paciente', 'servicio'])
                ->whereHas('medico', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->latest()->get();
        } else {
            $citas = Cita::with(['medico', 'paciente', 'servicio'])
                ->whereHas('paciente', function ($query) use ($user) {
                    $query->where('user_id', $user?->id);
                })
                ->latest()->get();
        }

        return view('Citas.index', compact('citas'));
    }

    public function create(): View
    {
        $this->authorizeCitaManagement();

        $medicos = Medico::all();
        $pacientes = Paciente::all();
        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get();

        return view('Citas.create', compact('medicos', 'pacientes', 'servicios'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeCitaManagement();

        $validated = $request->validate([
            'medico_id' => 'required|exists:medicos,id',
            'paciente_id' => 'required|exists:pacientes,id',
            'servicio_id' => 'required|exists:servicios,id',
            'fecha_hora' => 'required|date',
            'motivo' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            [, $inicio] = $this->availabilityService->validateCanSchedule(
                (int) $validated['medico_id'],
                (int) $validated['servicio_id'],
                $validated['fecha_hora'],
                lock: true,
            );

            $validated['fecha_hora'] = $inicio->format('Y-m-d H:i:s');
            $validated['estado'] = 'agendada';

            Cita::create($validated);
        });

        return redirect()->route('citas.index')->with('success', 'Cita agendada correctamente.');
    }

    public function edit(int $id): View
    {
        $this->authorizeCitaManagement();

        $cita = Cita::findOrFail($id);
        $medicos = Medico::all();
        $pacientes = Paciente::all();
        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get();

        return view('Citas.edit', compact('cita', 'medicos', 'pacientes', 'servicios'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeCitaManagement();

        $cita = Cita::findOrFail($id);

        $validated = $request->validate([
            'medico_id' => 'required|exists:medicos,id',
            'paciente_id' => 'required|exists:pacientes,id',
            'servicio_id' => 'required|exists:servicios,id',
            'fecha_hora' => 'required|date',
            'motivo' => 'required|string|max:255',
            'estado' => 'required|in:agendada,confirmada,cancelada,atendida,no_show',
        ]);

        DB::transaction(function () use ($cita, $validated) {
            if (in_array($validated['estado'], AppointmentAvailabilityService::OCCUPYING_STATES, true)) {
                [, $inicio] = $this->availabilityService->validateCanSchedule(
                    (int) $validated['medico_id'],
                    (int) $validated['servicio_id'],
                    $validated['fecha_hora'],
                    $cita->id,
                    true,
                );

                $validated['fecha_hora'] = $inicio->format('Y-m-d H:i:s');
            } else {
                $validated['fecha_hora'] = str_replace('T', ' ', $validated['fecha_hora']);
            }

            $cita->update($validated);
        });

        return redirect()->route('citas.index')->with('success', 'Cita actualizada correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeCitaManagement();

        Cita::findOrFail($id)->delete();

        return redirect()->route('citas.index')->with('success', 'Cita eliminada.');
    }

    public function marcarAtendida(int $id): RedirectResponse
    {
        $cita = Cita::with('medico')->findOrFail($id);

        $this->authorizeMedicoCita($cita);

        $cita->update(['estado' => 'atendida']);

        return redirect()->route('citas.index')->with('success', 'Cita marcada como atendida.');
    }

    public function noPresentada(int $id): RedirectResponse
    {
        $cita = Cita::with('medico')->findOrFail($id);

        $this->authorizeMedicoCita($cita);

        $cita->update(['estado' => 'no_show']);

        return redirect()->route('citas.index')->with('success', 'Cita marcada como no presentada.');
    }

    public function marcarPagoRealizado(int $id): RedirectResponse
    {
        $this->authorizeCitaManagement();

        $cita = Cita::with('servicio')->findOrFail($id);

        $cita->update([
            'estado_pago' => 'pagado',
            'monto_pagado' => $cita->servicio?->precio ?? 0,
            'fecha_pago' => now(),
        ]);

        return back()->with('success', 'Pago marcado como realizado.');
    }

    public function marcarPagoPendiente(int $id): RedirectResponse
    {
        $this->authorizeCitaManagement();

        Cita::findOrFail($id)->update([
            'estado_pago' => 'pendiente',
            'monto_pagado' => null,
            'fecha_pago' => null,
        ]);

        return back()->with('success', 'Pago marcado como pendiente.');
    }

    private function authorizeCitaManagement(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->hasAnyRole(['admin', 'recepcionista'])) {
            abort(403, 'Acceso denegado');
        }
    }

    private function authorizeMedicoCita(Cita $cita): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->hasRole('medico') || $cita->medico?->user_id !== $user->id) {
            abort(403, 'No autorizado.');
        }
    }
}
