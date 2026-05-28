<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Disponibilidad;
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

    public function availableSlots(int $medicoId, string $fecha, int $servicioId, ?int $ignoreCitaId = null, int $stepMinutes = 15): array
    {
        $servicio = Servicio::whereKey($servicioId)->where('activo', true)->first();

        if (! $servicio || ! $fecha) {
            return [];
        }

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

    public function rangeFor(string $fechaHora, Servicio $servicio): array
    {
        $inicio = Carbon::parse(str_replace('T', ' ', $fechaHora));
        $fin = $inicio->copy()->addMinutes($servicio->duracion_minutos);

        return [$inicio, $fin];
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
