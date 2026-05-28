<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Servicio;
use App\Models\User;
use App\Services\AppointmentAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PortalCitaController extends Controller
{
    private const SESSION_KEY = 'portal_cita_pendiente';

    public function __construct(private AppointmentAvailabilityService $availability) {}

    public function create(Request $request): View
    {
        $medicos = Medico::orderBy('nombre')->orderBy('apellido')->get();
        $servicios = Servicio::where('activo', true)
            ->where('duracion_minutos', '>=', 5)
            ->orderBy('nombre')
            ->get();

        $selectedMedicoId = $request->old('medico_id', $request->query('medico_id'));
        $selectedServicioId = $request->old('servicio_id', $request->query('servicio_id'));
        $selectedFecha = $request->old('fecha', $request->query('fecha'));
        $horarios = [];

        $slotRequest = validator([
            'medico_id' => $selectedMedicoId,
            'servicio_id' => $selectedServicioId,
            'fecha' => $selectedFecha,
        ], [
            'medico_id' => ['nullable', 'integer', 'exists:medicos,id'],
            'servicio_id' => ['nullable', 'integer', Rule::exists('servicios', 'id')->where(fn ($query) => $query->where('activo', true)->where('duracion_minutos', '>=', 5))],
            'fecha' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        if ($selectedMedicoId && $selectedServicioId && $selectedFecha && ! $slotRequest->fails()) {
            $horarios = $this->availableFutureSlots((int) $selectedMedicoId, $selectedFecha, (int) $selectedServicioId);
        }

        return view('PortalCitas.create', compact('medicos', 'servicios', 'selectedMedicoId', 'selectedServicioId', 'selectedFecha', 'horarios'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        $validated = $this->validateAppointmentPayload($request->all(), requirePatientFields: ! $user);
        $inicio = $this->validateSelectedDateTime($validated['fecha'], $validated['horario']);
        $validated['horario'] = $inicio->format('Y-m-d\TH:i');

        $this->ensureSlotWasOffered((int) $validated['medico_id'], (int) $validated['servicio_id'], $validated['fecha'], $inicio);

        if (! $user) {
            session()->put(self::SESSION_KEY, $validated);
            session()->put('url.intended', route('portal-citas.confirm'));

            return redirect()->route('login')->with('status', 'Inicia sesión o regístrate para confirmar tu cita.');
        }

        $validated = $this->withAuthenticatedPatientData($validated, $user);

        $this->createAppointment($validated, $user);
        session()->forget(self::SESSION_KEY);

        return redirect()->route('portal-citas.index')->with('success', 'Tu cita fue solicitada correctamente.');
    }

    public function confirm(): View|RedirectResponse
    {
        $payload = session(self::SESSION_KEY);

        if (! $payload) {
            return redirect()->route('portal-citas.index')->with('status', 'Selecciona un horario para continuar con tu cita.');
        }

        /** @var User $user */
        $user = Auth::user();

        $payload = $this->withAuthenticatedPatientData(
            $this->validateAppointmentPayload($payload, requirePatientFields: false),
            $user,
        );
        $medico = Medico::find($payload['medico_id']);
        $servicio = Servicio::find($payload['servicio_id']);

        return view('PortalCitas.confirm', compact('payload', 'medico', 'servicio'));
    }

    public function confirmStore(): RedirectResponse
    {
        $payload = session(self::SESSION_KEY);

        if (! $payload) {
            return redirect()->route('portal-citas.index')->with('status', 'Selecciona un horario para continuar con tu cita.');
        }

        /** @var User $user */
        $user = Auth::user();

        $payload = $this->withAuthenticatedPatientData(
            $this->validateAppointmentPayload($payload, requirePatientFields: false),
            $user,
        );

        $this->createAppointment($payload, $user);
        session()->forget(self::SESSION_KEY);

        return redirect()->route('portal-citas.index')->with('success', 'Tu cita fue confirmada correctamente.');
    }

    private function validateAppointmentPayload(array $payload, bool $requirePatientFields): array
    {
        $patientRules = $requirePatientFields
            ? [
                'nombre' => ['required', 'string', 'max:255'],
                'apellido' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'telefono' => ['required', 'string', 'max:20'],
            ]
            : [
                'nombre' => ['nullable', 'string', 'max:255'],
                'apellido' => ['nullable', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'telefono' => ['nullable', 'string', 'max:20'],
            ];

        return validator($payload, [
            'medico_id' => ['required', 'integer', 'exists:medicos,id'],
            'servicio_id' => ['required', 'integer', Rule::exists('servicios', 'id')->where(fn ($query) => $query->where('activo', true)->where('duracion_minutos', '>=', 5))],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'horario' => ['required', 'date'],
            'motivo' => ['required', 'string', 'max:255'],
            ...$patientRules,
        ])->validate();
    }

    private function createAppointment(array $payload, User $user): Cita
    {
        $inicio = $this->validateSelectedDateTime($payload['fecha'], $payload['horario']);
        $this->ensureSlotWasOffered((int) $payload['medico_id'], (int) $payload['servicio_id'], $payload['fecha'], $inicio);

        return DB::transaction(function () use ($payload, $inicio, $user) {
            $this->availability->validateCanSchedule(
                (int) $payload['medico_id'],
                (int) $payload['servicio_id'],
                $inicio->format('Y-m-d H:i:s'),
                lock: true,
            );

            $paciente = $this->resolvePaciente($payload, $user);

            return Cita::create([
                'medico_id' => $payload['medico_id'],
                'paciente_id' => $paciente->id,
                'servicio_id' => $payload['servicio_id'],
                'fecha_hora' => $inicio->format('Y-m-d H:i:s'),
                'motivo' => $payload['motivo'],
                'estado' => Cita::ESTADO_AGENDADA,
            ]);
        });
    }

    private function resolvePaciente(array $payload, User $user): Paciente
    {
        $paciente = Paciente::where('user_id', $user->id)->first();

        if (! $paciente) {
            $paciente = Paciente::where(function ($query) use ($payload, $user) {
                $query->where('email', $user->email)
                    ->orWhere('email', $payload['email']);

                if ($payload['telefono'] !== '') {
                    $query->orWhere('telefono', $payload['telefono']);
                }
            })->where(function ($query) use ($user) {
                $query->whereNull('user_id')->orWhere('user_id', $user->id);
            })->first();
        }

        if ($paciente) {
            if (! $paciente->user_id) {
                $paciente->update(['user_id' => $user->id]);
            }

            return $paciente;
        }

        return Paciente::create([
            'nombre' => $this->firstNameFromUser($user),
            'apellido' => $this->lastNameFromUser($user),
            'email' => $user->email,
            'telefono' => $payload['telefono'],
            'user_id' => $user->id,
        ]);
    }

    private function withAuthenticatedPatientData(array $payload, User $user): array
    {
        $paciente = Paciente::where('user_id', $user->id)->first();
        $parts = explode(' ', trim($user->name), 2);

        return [
            ...$payload,
            'nombre' => $paciente?->nombre ?: ($parts[0] ?: 'Paciente'),
            'apellido' => $paciente?->apellido ?: (trim($parts[1] ?? '') ?: 'Registrado'),
            'email' => $user->email,
            'telefono' => $paciente?->telefono ?: ($user->telefono ?? ($payload['telefono'] ?? '')),
        ];
    }

    private function availableFutureSlots(int $medicoId, string $fecha, int $servicioId): array
    {
        return array_values(array_filter(
            $this->availability->availableSlots($medicoId, $fecha, $servicioId),
            fn (array $slot) => Carbon::parse(str_replace('T', ' ', $slot['value']))->isFuture()
        ));
    }

    private function validateSelectedDateTime(string $fecha, string $horario): Carbon
    {
        $fechaSeleccionada = Carbon::parse($fecha)->startOfDay();
        $inicio = Carbon::parse(str_replace('T', ' ', $horario));

        if (! $inicio->isSameDay($fechaSeleccionada)) {
            throw ValidationException::withMessages([
                'horario' => 'Selecciona un horario de la fecha indicada.',
            ]);
        }

        if ($inicio->isPast()) {
            throw ValidationException::withMessages([
                'horario' => 'Selecciona un horario futuro.',
            ]);
        }

        return $inicio;
    }

    private function ensureSlotWasOffered(int $medicoId, int $servicioId, string $fecha, Carbon $inicio): void
    {
        $slotValue = $inicio->format('Y-m-d\TH:i');
        $availableSlots = array_column($this->availableFutureSlots($medicoId, $fecha, $servicioId), 'value');

        if (! in_array($slotValue, $availableSlots, true)) {
            throw ValidationException::withMessages([
                'horario' => 'El horario seleccionado ya no está disponible.',
            ]);
        }
    }

    private function firstNameFromUser(User $user): string
    {
        return trim(explode(' ', $user->name, 2)[0]) ?: 'Paciente';
    }

    private function lastNameFromUser(User $user): string
    {
        $parts = explode(' ', $user->name, 2);

        return trim($parts[1] ?? '') ?: 'Registrado';
    }
}
