<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Disponibilidad;
use App\Models\Medico;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AppointmentAvailabilityService
{
    public const OCCUPYING_STATES = [Cita::ESTADO_AGENDADA, Cita::ESTADO_CONFIRMADA];

    public const FINAL_STATES = [Cita::ESTADO_ATENDIDA, Cita::ESTADO_NO_SHOW];

    public function validateCanSchedule(int $medicoId, int $servicioId, string $fechaHora, ?int $ignoreCitaId = null, bool $lock = false): array
    {
        $servicio = Servicio::whereKey($servicioId)->where('activo', true)->first();

        if (! $servicio) {
            throw ValidationException::withMessages([
                'servicio_id' => 'Selecciona un servicio activo.',
            ]);
        }

        $this->ensureMedicoCanPerformService($medicoId, $servicioId);

        [$inicio, $fin] = $this->rangeFor($fechaHora, $servicio);

        if (! $this->isWithinAvailability($medicoId, $inicio, $fin)) {
            throw ValidationException::withMessages([
                'fecha_hora' => 'El horario seleccionado está fuera de la disponibilidad del médico.',
            ]);
        }

        if ($this->hasOverlap($medicoId, $inicio, $fin, $ignoreCitaId, $lock)) {
            throw ValidationException::withMessages([
                'fecha_hora' => 'El médico ya tiene una cita agendada o confirmada en ese horario.',
            ]);
        }

        return [$servicio, $inicio, $fin];
    }

    public function availableSlots(int $medicoId, string $fecha, int $servicioId, ?int $ignoreCitaId = null, ?int $stepMinutes = null): array
    {
        $servicio = Servicio::whereKey($servicioId)->where('activo', true)->first();

        if (! $servicio || ! $fecha) {
            return [];
        }

        if (! $this->medicoCanPerformService($medicoId, $servicioId)) {
            return [];
        }

        $stepMinutes = max(1, $stepMinutes ?? $servicio->duracion_minutos);

        $date = Carbon::parse($fecha)->startOfDay();
        $diaSemana = $date->dayOfWeekIso;
        $slots = [];

        $disponibilidades = Disponibilidad::where('medico_id', $medicoId)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->orderBy('hora_inicio')
            ->get();

        foreach ($disponibilidades as $disponibilidad) {
            $cursor = $date->copy()->setTimeFromTimeString($disponibilidad->hora_inicio);
            $limite = $date->copy()->setTimeFromTimeString($disponibilidad->hora_fin);

            while ($cursor->copy()->addMinutes($servicio->duracion_minutos)->lessThanOrEqualTo($limite)) {
                $inicio = $cursor->copy();
                $fin = $inicio->copy()->addMinutes($servicio->duracion_minutos);

                if (! $this->hasOverlap($medicoId, $inicio, $fin, $ignoreCitaId)) {
                    $slots[$inicio->format('Y-m-d\TH:i')] = [
                        'value' => $inicio->format('Y-m-d\TH:i'),
                        'label' => $inicio->format('H:i'),
                        'ends_at' => $fin->format('H:i'),
                    ];
                }

                $cursor->addMinutes($stepMinutes);
            }
        }

        return array_values($slots);
    }

    public function availableFutureSlots(int $medicoId, string $fecha, int $servicioId, ?int $ignoreCitaId = null, ?int $stepMinutes = null): array
    {
        return array_values(array_filter(
            $this->availableSlots($medicoId, $fecha, $servicioId, $ignoreCitaId, $stepMinutes),
            fn (array $slot) => Carbon::parse(str_replace('T', ' ', $slot['value']))->isFuture()
        ));
    }

    public function availableDates(int $medicoId, int $servicioId, int $daysAhead = 30, int $limit = 8, ?Carbon $from = null, ?int $ignoreCitaId = null, ?int $stepMinutes = null): array
    {
        $startDate = ($from ?: Carbon::today())->copy()->startOfDay();
        $dates = [];

        for ($offset = 0; $offset <= $daysAhead && count($dates) < $limit; $offset++) {
            $date = $startDate->copy()->addDays($offset);
            $slots = $this->availableFutureSlots($medicoId, $date->toDateString(), $servicioId, $ignoreCitaId, $stepMinutes);

            if ($slots !== []) {
                $dates[] = [
                    'date' => $date->toDateString(),
                    'slots_count' => count($slots),
                ];
            }
        }

        return $dates;
    }

    public function rangeFor(string $fechaHora, Servicio $servicio): array
    {
        $inicio = Carbon::parse(str_replace('T', ' ', $fechaHora));
        $fin = $inicio->copy()->addMinutes($servicio->duracion_minutos);

        return [$inicio, $fin];
    }

    public function ensureMedicoCanPerformService(int $medicoId, int $servicioId): void
    {
        if (! $this->medicoCanPerformService($medicoId, $servicioId)) {
            throw ValidationException::withMessages([
                'servicio_id' => 'El servicio seleccionado no pertenece al médico seleccionado.',
            ]);
        }
    }

    public function medicoCanPerformService(int $medicoId, int $servicioId): bool
    {
        return Medico::whereKey($medicoId)
            ->whereHas('servicios', fn ($query) => $query->where('servicios.id', $servicioId))
            ->exists();
    }

    public function hasOverlap(int $medicoId, Carbon $inicio, Carbon $fin, ?int $ignoreCitaId = null, bool $lock = false): bool
    {
        $query = Cita::with('servicio')
            ->where('medico_id', $medicoId)
            ->whereIn('estado', self::OCCUPYING_STATES)
            ->whereDate('fecha_hora', $inicio->toDateString())
            ->where('fecha_hora', '<', $fin->format('Y-m-d H:i:s'));

        if ($ignoreCitaId) {
            $query->whereKeyNot($ignoreCitaId);
        }

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get()->contains(function (Cita $cita) use ($inicio, $fin) {
            $inicioExistente = Carbon::parse($cita->fecha_hora);
            $duracion = $cita->servicio?->duracion_minutos ?? 30;
            $finExistente = $inicioExistente->copy()->addMinutes($duracion);

            return $inicio->lessThan($finExistente) && $fin->greaterThan($inicioExistente);
        });
    }

    public function isWithinAvailability(int $medicoId, Carbon $inicio, Carbon $fin): bool
    {
        if (! $inicio->isSameDay($fin->copy()->subSecond())) {
            return false;
        }

        return Disponibilidad::where('medico_id', $medicoId)
            ->where('dia_semana', $inicio->dayOfWeekIso)
            ->where('activo', true)
            ->get()
            ->contains(function (Disponibilidad $disponibilidad) use ($inicio, $fin) {
                $inicioDisponible = $inicio->copy()->setTimeFromTimeString($disponibilidad->hora_inicio);
                $finDisponible = $inicio->copy()->setTimeFromTimeString($disponibilidad->hora_fin);

                return $inicio->greaterThanOrEqualTo($inicioDisponible) && $fin->lessThanOrEqualTo($finDisponible);
            });
    }
}
