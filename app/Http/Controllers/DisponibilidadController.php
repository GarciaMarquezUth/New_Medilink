<?php

namespace App\Http\Controllers;

use App\Models\Disponibilidad;
use App\Models\Medico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DisponibilidadController extends Controller
{
    public function index(): View
    {
        $disponibilidades = Disponibilidad::with('medico')
            ->orderBy('medico_id')
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();

        return view('Disponibilidades.index', compact('disponibilidades'));
    }

    public function create(): View
    {
        $medicos = Medico::orderBy('nombre')->get();
        $diasSemana = Disponibilidad::diasSemana();

        return view('Disponibilidades.create', compact('medicos', 'diasSemana'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDisponibilidad($request);

        $this->ensureNoOverlap($validated);

        Disponibilidad::create($validated);

        return redirect()->route('disponibilidades.index')->with('success', 'Disponibilidad registrada correctamente.');
    }

    public function edit(int $id): View
    {
        $disponibilidad = Disponibilidad::findOrFail($id);
        $medicos = Medico::orderBy('nombre')->get();
        $diasSemana = Disponibilidad::diasSemana();

        return view('Disponibilidades.edit', compact('disponibilidad', 'medicos', 'diasSemana'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $disponibilidad = Disponibilidad::findOrFail($id);
        $validated = $this->validateDisponibilidad($request);

        $this->ensureNoOverlap($validated, $disponibilidad->id);
        $disponibilidad->update($validated);

        return redirect()->route('disponibilidades.index')->with('success', 'Disponibilidad actualizada correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Disponibilidad::findOrFail($id)->delete();

        return redirect()->route('disponibilidades.index')->with('success', 'Disponibilidad eliminada.');
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
}
