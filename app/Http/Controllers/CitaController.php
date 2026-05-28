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
    public function __construct(private AppointmentAvailabilityService $availability) {}

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

        $estadoLabels = Cita::estados();
        $estadosOcupantes = Cita::estadosOcupantes();

        return view('Citas.index', compact('citas', 'estadoLabels', 'estadosOcupantes'));
    }

    public function create(): View
    {
        $this->authorizeCitaManagement();

        $medicos = Medico::orderBy('nombre')->get();
        $pacientes = Paciente::orderBy('nombre')->get();
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

        $validated['fecha_hora'] = str_replace('T', ' ', $validated['fecha_hora']);
        $validated['estado'] = Cita::ESTADO_AGENDADA;

        DB::transaction(function () use ($validated) {
            $this->availability->validateCanSchedule(
                (int) $validated['medico_id'],
                (int) $validated['servicio_id'],
                $validated['fecha_hora'],
                lock: true,
            );

            Cita::create($validated);
        });

        return redirect()->route('citas.index')->with('success', 'Cita agendada correctamente.');
    }

    public function edit(int $id): View
    {
        $this->authorizeCitaManagement();

        $cita = Cita::findOrFail($id);
        $medicos = Medico::orderBy('nombre')->get();
        $pacientes = Paciente::orderBy('nombre')->get();
        $servicios = Servicio::where(function ($query) use ($cita) {
            $query->where('activo', true);

            if ($cita->servicio_id) {
                $query->orWhereKey($cita->servicio_id);
            }
        })->orderBy('nombre')->get();
        $estados = Cita::estados();

        return view('Citas.edit', compact('cita', 'medicos', 'pacientes', 'servicios', 'estados'));
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
            'estado' => 'required|in:'.implode(',', array_keys(Cita::estados())),
        ]);

        $validated['fecha_hora'] = str_replace('T', ' ', $validated['fecha_hora']);

        DB::transaction(function () use ($cita, $validated) {
            if (in_array($validated['estado'], Cita::estadosOcupantes(), true)) {
                $this->availability->validateCanSchedule(
                    (int) $validated['medico_id'],
                    (int) $validated['servicio_id'],
                    $validated['fecha_hora'],
                    $cita->id,
                    true,
                );
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

        $cita->update(['estado' => Cita::ESTADO_ATENDIDA]);

        return redirect()->route('citas.index')->with('success', 'Cita marcada como atendida.');
    }

    public function noPresentada(int $id): RedirectResponse
    {
        $cita = Cita::with('medico')->findOrFail($id);

        $this->authorizeMedicoCita($cita);

        $cita->update(['estado' => Cita::ESTADO_NO_SHOW]);

        return redirect()->route('citas.index')->with('success', 'Cita marcada como no presentada.');
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
