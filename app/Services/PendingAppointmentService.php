<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PendingAppointmentService
{
    public const SESSION_KEY = 'pending_appointment';

    public const LEGACY_SESSION_KEY = 'portal_cita_pendiente';

    public const LOGIN_NOTICE = 'Inicia sesión o regístrate para confirmar tu cita. Tus datos ya fueron guardados temporalmente.';

    public const CONFIRMED_MESSAGE = 'Tu cita fue confirmada correctamente.';

    public const UNAVAILABLE_MESSAGE = 'Ese horario ya no está disponible, selecciona otro.';

    public function __construct(
        private AppointmentAvailabilityService $availability,
        private AppointmentEmailService $emails,
    ) {}

    public function hasPending(): bool
    {
        return session()->has(self::SESSION_KEY) || session()->has(self::LEGACY_SESSION_KEY);
    }

    public function pending(): ?array
    {
        return session(self::SESSION_KEY) ?: session(self::LEGACY_SESSION_KEY);
    }

    public function storePending(array $payload): void
    {
        session()->put(self::SESSION_KEY, $payload);
        session()->forget(self::LEGACY_SESSION_KEY);
    }

    public function forgetPending(): void
    {
        session()->forget([self::SESSION_KEY, self::LEGACY_SESSION_KEY]);
    }

    public function validateAppointmentPayload(array $payload, bool $requirePatientFields): array
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

        $validated = validator($payload, [
            'medico_id' => ['required', 'integer', 'exists:medicos,id'],
            'servicio_id' => ['required', 'integer', Rule::exists('servicios', 'id')->where(fn ($query) => $query->where('activo', true)->where('duracion_minutos', '>=', 5))],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'horario' => ['required', 'date'],
            'motivo' => ['required', 'string', 'max:255'],
            ...$patientRules,
        ])->validate();

        $this->availability->ensureMedicoCanPerformService((int) $validated['medico_id'], (int) $validated['servicio_id']);

        return $validated;
    }

    public function validateSelectedDateTime(string $fecha, string $horario): Carbon
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

    public function ensureSlotWasOffered(int $medicoId, int $servicioId, string $fecha, Carbon $inicio): void
    {
        $slotValue = $inicio->format('Y-m-d\TH:i');
        $availableSlots = array_column($this->availability->availableFutureSlots($medicoId, $fecha, $servicioId), 'value');

        if (! in_array($slotValue, $availableSlots, true)) {
            throw ValidationException::withMessages([
                'horario' => self::UNAVAILABLE_MESSAGE,
            ]);
        }
    }

    public function withAuthenticatedPatientData(array $payload, User $user): array
    {
        $paciente = Paciente::where('user_id', $user->id)->first();
        $parts = explode(' ', trim($user->name), 2);

        return [
            ...$payload,
            'nombre' => $paciente?->nombre ?: ($payload['nombre'] ?? null) ?: ($parts[0] ?: 'Paciente'),
            'apellido' => $paciente?->apellido ?: ($payload['apellido'] ?? null) ?: (trim($parts[1] ?? '') ?: 'Registrado'),
            'email' => $paciente?->email ?: $user->email,
            'telefono' => $paciente?->telefono ?: ($payload['telefono'] ?? ''),
        ];
    }

    public function createAppointment(array $payload, User $user): Cita
    {
        $inicio = $this->validateSelectedDateTime($payload['fecha'], $payload['horario']);
        $this->ensureSlotWasOffered((int) $payload['medico_id'], (int) $payload['servicio_id'], $payload['fecha'], $inicio);

        $cita = DB::transaction(function () use ($payload, $inicio, $user) {
            try {
                $this->availability->validateCanSchedule(
                    (int) $payload['medico_id'],
                    (int) $payload['servicio_id'],
                    $inicio->format('Y-m-d H:i:s'),
                    lock: true,
                );
            } catch (ValidationException) {
                throw ValidationException::withMessages([
                    'horario' => self::UNAVAILABLE_MESSAGE,
                ]);
            }

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

        $this->emails->sendConfirmation($cita);

        return $cita;
    }

    public function confirmPendingFor(User $user): Cita
    {
        $payload = $this->pending();

        if (! $payload) {
            throw ValidationException::withMessages([
                'horario' => 'Selecciona un horario para continuar con tu cita.',
            ]);
        }

        $payload = $this->withAuthenticatedPatientData(
            $this->validateAppointmentPayload($payload, requirePatientFields: false),
            $user,
        );

        $cita = $this->createAppointment($payload, $user);
        $this->forgetPending();

        return $cita;
    }

    private function resolvePaciente(array $payload, User $user): Paciente
    {
        $paciente = Paciente::where('user_id', $user->id)->first();

        if (! $paciente) {
            $paciente = Paciente::where(function ($query) use ($payload, $user) {
                $query->where('email', $user->email);

                if (! empty($payload['email'])) {
                    $query->orWhere('email', $payload['email']);
                }

                if (! empty($payload['telefono'])) {
                    $query->orWhere('telefono', $payload['telefono']);
                }
            })->where(function ($query) use ($user) {
                $query->whereNull('user_id')->orWhere('user_id', $user->id);
            })->first();
        }

        if ($paciente) {
            $updates = [];

            if (! $paciente->user_id) {
                $updates['user_id'] = $user->id;
            }

            if (! $paciente->telefono && ! empty($payload['telefono'])) {
                $updates['telefono'] = $payload['telefono'];
            }

            if ($updates !== []) {
                $paciente->update($updates);
            }

            return $paciente;
        }

        return Paciente::create([
            'nombre' => $payload['nombre'],
            'apellido' => $payload['apellido'],
            'email' => $user->email,
            'telefono' => $payload['telefono'] ?? '',
            'user_id' => $user->id,
        ]);
    }
}
