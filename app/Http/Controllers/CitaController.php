<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Servicio;
use App\Models\User;
use App\Services\AppointmentAvailabilityService;
use App\Services\AppointmentEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CitaController extends Controller
{
    public function __construct(
        private AppointmentAvailabilityService $availability,
        private AppointmentEmailService $emails,
    ) {}

    public function index(Request $request): View
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user && $user->hasAnyRole(['admin', 'recepcionista'])) {
            $this->authorizePermission('citas.ver');

            $query = Cita::with(['medico', 'paciente', 'servicio']);
        } elseif ($user && $user->hasRole('medico')) {
            $this->authorizePermission('citas.ver');

            $query = Cita::with(['medico', 'paciente', 'servicio'])
                ->whereHas('medico', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
        } else {
            $query = Cita::with(['medico', 'paciente', 'servicio'])
                ->whereHas('paciente', function ($query) use ($user) {
                    $query->where('user_id', $user?->id);
                });
        }

        $filters = $request->only(['q', 'estado', 'fecha_desde', 'fecha_hasta']);
        $this->applyCitaFilters($query, $filters);

        $citas = $query->latest()->paginate(15)->withQueryString();

        $estadoLabels = Cita::estados();
        $estadosOcupantes = Cita::estadosOcupantes();
        $showEstadoFilter = ! ($user?->hasRole('paciente') && ! $user?->hasAnyRole(['admin', 'recepcionista', 'medico']));

        return view('Citas.index', compact('citas', 'estadoLabels', 'estadosOcupantes', 'filters', 'showEstadoFilter'));
    }

    public function create(Request $request): View
    {
        $this->authorizeCitaManagement('citas.crear');

        $medicos = Medico::with(['servicios' => fn ($query) => $query->where('activo', true)->orderBy('nombre')])
            ->orderBy('nombre')
            ->get();
        $pacientes = Paciente::orderBy('nombre')->get();
        $selectedMedicoId = $request->old('medico_id', $request->query('medico_id'));
        $selectedServicioId = $request->old('servicio_id', $request->query('servicio_id'));
        $servicios = $this->serviciosActivosParaMedico($selectedMedicoId ? (int) $selectedMedicoId : null);

        if ($selectedServicioId && ! $servicios->contains('id', (int) $selectedServicioId)) {
            $selectedServicioId = null;
        }

        return view('Citas.create', compact('medicos', 'pacientes', 'servicios', 'selectedMedicoId', 'selectedServicioId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeCitaManagement('citas.crear');

        $validated = $request->validate([
            'medico_id' => 'required|exists:medicos,id',
            'paciente_id' => 'required|exists:pacientes,id',
            'servicio_id' => ['required', Rule::exists('servicios', 'id')->where(fn ($query) => $query->where('activo', true))],
            'fecha_hora' => 'required|date',
            'motivo' => 'required|string|max:255',
        ]);

        $this->availability->ensureMedicoCanPerformService((int) $validated['medico_id'], (int) $validated['servicio_id']);

        $validated['fecha_hora'] = str_replace('T', ' ', $validated['fecha_hora']);
        $validated['estado'] = Cita::ESTADO_AGENDADA;

        $cita = DB::transaction(function () use ($validated) {
            $this->availability->validateCanSchedule(
                (int) $validated['medico_id'],
                (int) $validated['servicio_id'],
                $validated['fecha_hora'],
                lock: true,
            );

            return Cita::create($validated);
        });

        $this->emails->sendConfirmation($cita);

        return redirect()->route('citas.index')->with('success', 'Cita agendada correctamente.');
    }

    public function edit(Request $request, int $id): View
    {
        $this->authorizeCitaManagement('citas.editar');

        $cita = Cita::findOrFail($id);
        $medicos = Medico::with(['servicios' => fn ($query) => $query->where('activo', true)->orderBy('nombre')])
            ->orderBy('nombre')
            ->get();
        $pacientes = Paciente::orderBy('nombre')->get();
        $selectedMedicoId = $request->old('medico_id', $request->query('medico_id', $cita->medico_id));
        $selectedServicioId = $request->old('servicio_id', $cita->servicio_id);
        $servicios = $this->serviciosActivosParaMedico($selectedMedicoId ? (int) $selectedMedicoId : null);

        if ($selectedServicioId && ! $servicios->contains('id', (int) $selectedServicioId)) {
            $selectedServicioId = null;
        }

        $estados = Cita::estados();

        return view('Citas.edit', compact('cita', 'medicos', 'pacientes', 'servicios', 'estados', 'selectedMedicoId', 'selectedServicioId'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeCitaManagement('citas.editar');

        $cita = Cita::findOrFail($id);
        $wasCancelled = $cita->estado === Cita::ESTADO_CANCELADA;

        $validated = $request->validate([
            'medico_id' => 'required|exists:medicos,id',
            'paciente_id' => 'required|exists:pacientes,id',
            'servicio_id' => ['required', Rule::exists('servicios', 'id')->where(fn ($query) => $query->where('activo', true))],
            'fecha_hora' => 'required|date',
            'motivo' => 'required|string|max:255',
            'estado' => 'required|in:'.implode(',', array_keys(Cita::estados())),
        ]);

        $this->availability->ensureMedicoCanPerformService((int) $validated['medico_id'], (int) $validated['servicio_id']);

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

        $cita->refresh();

        if (! $wasCancelled && $cita->estado === Cita::ESTADO_CANCELADA) {
            $this->emails->sendCancellation($cita);
        }

        return redirect()->route('citas.index')->with('success', 'Cita actualizada correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeCitaManagement('citas.eliminar');

        $cita = Cita::with(['paciente', 'medico', 'servicio'])->findOrFail($id);
        $cita->estado = Cita::ESTADO_CANCELADA;

        $this->emails->sendCancellation($cita);

        $cita->delete();

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

    public function cancelarPaciente(Cita $cita): RedirectResponse
    {
        $cita->load('paciente');

        $this->authorizePacienteCita($cita);

        if (! in_array($cita->estado, Cita::estadosOcupantes(), true) || $cita->fecha_hora->isPast()) {
            return back()->with('error', 'Esta cita ya no puede cancelarse desde el portal del paciente.');
        }

        $cita->update(['estado' => Cita::ESTADO_CANCELADA]);

        $this->emails->sendCancellation($cita->refresh());

        return back()->with('success', 'Cita cancelada correctamente.');
    }

    private function authorizeCitaManagement(string $permission): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->hasAnyRole(['admin', 'recepcionista']) || ! $user->can($permission)) {
            abort(403, 'Acceso denegado');
        }
    }

    private function authorizePermission(string $permission): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->can($permission)) {
            abort(403, 'Acceso denegado');
        }
    }

    private function serviciosActivosParaMedico(?int $medicoId)
    {
        if (! $medicoId) {
            return collect();
        }

        return Servicio::where('activo', true)
            ->whereHas('medicos', fn ($query) => $query->where('medicos.id', $medicoId))
            ->orderBy('nombre')
            ->get();
    }

    private function applyCitaFilters($query, array $filters): void
    {
        if (! empty($filters['q'])) {
            $search = trim($filters['q']);

            $query->where(function ($query) use ($search) {
                $query->where('motivo', 'like', "%{$search}%")
                    ->orWhereHas('paciente', function ($query) use ($search) {
                        $query->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('medico', function ($query) use ($search) {
                        $query->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%")
                            ->orWhere('especialidad', 'like', "%{$search}%");
                    })
                    ->orWhereHas('servicio', function ($query) use ($search) {
                        $query->where('nombre', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['estado']) && array_key_exists($filters['estado'], Cita::estados())) {
            $query->where('estado', $filters['estado']);
        }

        if (! empty($filters['fecha_desde'])) {
            $query->whereDate('fecha_hora', '>=', $filters['fecha_desde']);
        }

        if (! empty($filters['fecha_hasta'])) {
            $query->whereDate('fecha_hora', '<=', $filters['fecha_hasta']);
        }
    }

    private function authorizeMedicoCita(Cita $cita): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->hasRole('medico') || ! $user->can('citas.editar') || $cita->medico?->user_id !== $user->id) {
            abort(403, 'No autorizado.');
        }
    }

    private function authorizePacienteCita(Cita $cita): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->hasRole('paciente') || $cita->paciente?->user_id !== $user->id) {
            abort(403, 'No autorizado.');
        }
    }
}
