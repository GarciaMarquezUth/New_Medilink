<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Disponibilidad;
use App\Models\Medico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DisponibilidadController extends Controller
{
    public function index(): View
    {
        $disponibilidades = Disponibilidad::with('medico')
            ->whereIn('medico_id', $this->visibleMedicosQuery()->pluck('id'))
            ->orderBy('medico_id')
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();

        return view('Disponibilidades.index', compact('disponibilidades'));
    }

    public function create(Request $request): View
    {
        $medicos = $this->visibleMedicosQuery()->orderBy('nombre')->get();
        $diasSemana = Disponibilidad::diasSemana();
        $selectedMedicoId = (int) $request->old('medico_id', $request->query('medico_id', $medicos->count() === 1 ? $medicos->first()->id : null));

        if ($selectedMedicoId && ! $medicos->contains('id', $selectedMedicoId)) {
            $selectedMedicoId = 0;
        }

        $semana = $this->buildSemana($selectedMedicoId ?: null);

        return view('Disponibilidades.create', compact('medicos', 'diasSemana', 'semana', 'selectedMedicoId'));
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->has('dias')) {
            return $this->storeSemana($request);
        }

        $validated = $this->validateDisponibilidad($request);
        $this->ensureCanUseMedico((int) $validated['medico_id']);

        $this->ensureNoOverlap($validated);

        Disponibilidad::create($validated);

        return redirect()->route('disponibilidades.index')->with('success', 'Disponibilidad registrada correctamente.');
    }

    public function edit(int $id): View
    {
        $disponibilidad = Disponibilidad::findOrFail($id);
        $this->ensureCanUseMedico($disponibilidad->medico_id);

        $medicos = $this->visibleMedicosQuery()->orderBy('nombre')->get();
        $diasSemana = Disponibilidad::diasSemana();

        return view('Disponibilidades.edit', compact('disponibilidad', 'medicos', 'diasSemana'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $disponibilidad = Disponibilidad::findOrFail($id);
        $this->ensureCanUseMedico($disponibilidad->medico_id);

        $validated = $this->validateDisponibilidad($request);
        $this->ensureCanUseMedico((int) $validated['medico_id']);

        $this->ensureNoOverlap($validated, $disponibilidad->id);
        if ($disponibilidad->medico_id === (int) $validated['medico_id']) {
            $intervalos = $this->activeIntervalsFromRecords((int) $validated['medico_id'], function (Disponibilidad $record) use ($disponibilidad, $validated) {
                if ($record->id !== $disponibilidad->id) {
                    return $record->toArray();
                }

                return $validated;
            });

            $this->ensureAppointmentsRemainCovered((int) $validated['medico_id'], $intervalos);
        } else {
            $intervalosOrigen = $this->activeIntervalsFromRecords($disponibilidad->medico_id, function (Disponibilidad $record) use ($disponibilidad) {
                return $record->id === $disponibilidad->id ? null : $record->toArray();
            });
            $this->ensureAppointmentsRemainCovered($disponibilidad->medico_id, $intervalosOrigen);

            $intervalosDestino = $this->activeIntervalsFromRecords((int) $validated['medico_id'], fn (Disponibilidad $record) => $record->toArray());
            if ($validated['activo']) {
                $intervalosDestino[(int) $validated['dia_semana']][] = [
                    'hora_inicio' => $validated['hora_inicio'],
                    'hora_fin' => $validated['hora_fin'],
                ];
            }
            $this->ensureAppointmentsRemainCovered((int) $validated['medico_id'], $intervalosDestino);
        }
        $disponibilidad->update($validated);

        return redirect()->route('disponibilidades.index')->with('success', 'Disponibilidad actualizada correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $disponibilidad = Disponibilidad::findOrFail($id);
        $this->ensureCanUseMedico($disponibilidad->medico_id);

        $intervalos = $this->activeIntervalsFromRecords($disponibilidad->medico_id, function (Disponibilidad $record) use ($disponibilidad) {
            return $record->id === $disponibilidad->id ? null : $record->toArray();
        });

        $this->ensureAppointmentsRemainCovered($disponibilidad->medico_id, $intervalos);
        $disponibilidad->delete();

        return redirect()->route('disponibilidades.index')->with('success', 'Disponibilidad eliminada.');
    }

    private function storeSemana(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'medico_id' => 'required|exists:medicos,id',
            'dias' => 'required|array',
        ]);

        $medicoId = (int) $validated['medico_id'];
        $this->ensureCanUseMedico($medicoId);

        $dias = $this->validateSemana($request);
        $intervalos = $this->activeIntervalsFromSemana($dias);
        $this->ensureAppointmentsRemainCovered($medicoId, $intervalos);

        DB::transaction(function () use ($medicoId, $dias) {
            foreach ($dias as $dia => $config) {
                $registros = Disponibilidad::where('medico_id', $medicoId)
                    ->where('dia_semana', $dia)
                    ->orderByDesc('activo')
                    ->orderBy('hora_inicio')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $principal = $registros->first();

                if ($config['activo']) {
                    if ($principal) {
                        $principal->update([
                            'hora_inicio' => $config['hora_inicio'],
                            'hora_fin' => $config['hora_fin'],
                            'activo' => true,
                        ]);
                    } else {
                        $principal = Disponibilidad::create([
                            'medico_id' => $medicoId,
                            'dia_semana' => $dia,
                            'hora_inicio' => $config['hora_inicio'],
                            'hora_fin' => $config['hora_fin'],
                            'activo' => true,
                        ]);
                    }

                    $registros->reject(fn (Disponibilidad $registro) => $registro->id === $principal->id)->each->update(['activo' => false]);

                    continue;
                }

                if ($principal && $config['hora_inicio'] && $config['hora_fin']) {
                    $principal->update([
                        'hora_inicio' => $config['hora_inicio'],
                        'hora_fin' => $config['hora_fin'],
                        'activo' => false,
                    ]);

                    $registros->reject(fn (Disponibilidad $registro) => $registro->id === $principal->id)->each->update(['activo' => false]);

                    continue;
                }

                $registros->each->update(['activo' => false]);
            }
        });

        return redirect()
            ->route('disponibilidades.create', ['medico_id' => $medicoId])
            ->with('success', 'Disponibilidad semanal actualizada correctamente.');
    }

    private function validateDisponibilidad(Request $request): array
    {
        $validated = $request->validate([
            'medico_id' => 'required|exists:medicos,id',
            'dia_semana' => 'required|integer|between:1,7',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        $validated['activo'] = $request->boolean('activo');

        return $validated;
    }

    private function validateSemana(Request $request): array
    {
        $dias = [];
        $errors = [];

        foreach (Disponibilidad::diasSemana() as $dia => $nombre) {
            $data = (array) $request->input("dias.$dia", []);
            $activo = filter_var($data['activo'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $horaInicio = $data['hora_inicio'] ?? null;
            $horaFin = $data['hora_fin'] ?? null;
            $tieneHorario = $horaInicio || $horaFin;

            if ($activo || $tieneHorario) {
                $validator = validator([
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin,
                ], [
                    'hora_inicio' => 'required|date_format:H:i',
                    'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
                ], [
                    'hora_inicio.required' => "Indica la hora de inicio para $nombre.",
                    'hora_inicio.date_format' => "La hora de inicio de $nombre no es válida.",
                    'hora_fin.required' => "Indica la hora de fin para $nombre.",
                    'hora_fin.date_format' => "La hora de fin de $nombre no es válida.",
                    'hora_fin.after' => "La hora de fin de $nombre debe ser mayor que la hora de inicio.",
                ]);

                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $field => $messages) {
                        $errors["dias.$dia.$field"] = $messages;
                    }
                }
            }

            $dias[$dia] = [
                'activo' => $activo,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $dias;
    }

    private function ensureNoOverlap(array $validated, ?int $ignoreId = null): void
    {
        if (! $validated['activo']) {
            return;
        }

        $query = Disponibilidad::where('medico_id', $validated['medico_id'])
            ->where('dia_semana', $validated['dia_semana'])
            ->where('activo', true)
            ->where('hora_inicio', '<', $validated['hora_fin'])
            ->where('hora_fin', '>', $validated['hora_inicio']);

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'El médico ya tiene una disponibilidad activa que se traslapa con ese horario.',
            ]);
        }
    }

    private function buildSemana(?int $medicoId): array
    {
        $registros = $medicoId
            ? Disponibilidad::where('medico_id', $medicoId)
                ->orderByDesc('activo')
                ->orderBy('hora_inicio')
                ->orderBy('id')
                ->get()
                ->groupBy('dia_semana')
            : collect();

        $semana = [];

        foreach (Disponibilidad::diasSemana() as $dia => $nombre) {
            /** @var Collection<int, Disponibilidad> $delDia */
            $delDia = $registros->get($dia, collect());
            $principal = $delDia->firstWhere('activo', true) ?? $delDia->first();

            $semana[$dia] = [
                'nombre' => $nombre,
                'activo' => (bool) $delDia->where('activo', true)->count(),
                'hora_inicio' => $principal ? substr($principal->hora_inicio, 0, 5) : '08:00',
                'hora_fin' => $principal ? substr($principal->hora_fin, 0, 5) : '14:00',
                'registros' => $delDia->count(),
                'activos' => $delDia->where('activo', true)->count(),
            ];
        }

        return $semana;
    }

    private function activeIntervalsFromSemana(array $dias): array
    {
        $intervalos = [];

        foreach ($dias as $dia => $config) {
            if (! $config['activo']) {
                continue;
            }

            $intervalos[$dia][] = [
                'hora_inicio' => $config['hora_inicio'],
                'hora_fin' => $config['hora_fin'],
            ];
        }

        return $intervalos;
    }

    private function activeIntervalsFromRecords(int $medicoId, callable $map): array
    {
        $intervalos = [];

        Disponibilidad::where('medico_id', $medicoId)->get()->each(function (Disponibilidad $record) use (&$intervalos, $map) {
            $data = $map($record);

            if (! $data || empty($data['activo'])) {
                return;
            }

            $intervalos[(int) $data['dia_semana']][] = [
                'hora_inicio' => $data['hora_inicio'],
                'hora_fin' => $data['hora_fin'],
            ];
        });

        return $intervalos;
    }

    private function ensureAppointmentsRemainCovered(int $medicoId, array $intervalos): void
    {
        $citas = Cita::with('servicio')
            ->where('medico_id', $medicoId)
            ->whereIn('estado', Cita::estadosOcupantes())
            ->where('fecha_hora', '>=', now())
            ->orderBy('fecha_hora')
            ->get();

        foreach ($citas as $cita) {
            $inicio = $cita->fecha_hora;
            $fin = $inicio->copy()->addMinutes($cita->servicio?->duracion_minutos ?? 30);
            $dia = $inicio->dayOfWeekIso;

            $cubierta = collect($intervalos[$dia] ?? [])->contains(function (array $intervalo) use ($inicio, $fin) {
                $inicioDisponible = $inicio->copy()->setTimeFromTimeString($intervalo['hora_inicio']);
                $finDisponible = $inicio->copy()->setTimeFromTimeString($intervalo['hora_fin']);

                return $inicio->greaterThanOrEqualTo($inicioDisponible) && $fin->lessThanOrEqualTo($finDisponible);
            });

            if (! $cubierta) {
                throw ValidationException::withMessages([
                    'hora_inicio' => 'No se puede guardar porque una cita futura quedaría fuera de la disponibilidad del médico.',
                    "dias.$dia.hora_inicio" => 'Este cambio dejaría una cita futura fuera del horario disponible.',
                ]);
            }
        }
    }

    private function visibleMedicosQuery()
    {
        $user = Auth::user();
        $query = Medico::query();

        if ($user && $user->hasAnyRole(['admin', 'recepcionista'])) {
            return $query;
        }

        if ($user && $user->hasRole('medico')) {
            return $query->where('user_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    private function ensureCanUseMedico(int $medicoId): void
    {
        if (! $this->visibleMedicosQuery()->whereKey($medicoId)->exists()) {
            abort(403, 'No autorizado para gestionar la disponibilidad de este médico.');
        }
    }
}
