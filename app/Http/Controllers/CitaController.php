<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CitaController extends Controller
{
    public function index(): View
    {
        // Usamos Eager Loading para optimizar la consulta
        $citas = Cita::with(['medico', 'paciente'])->latest()->get();
        return view('Citas.index', compact('citas'));
    }

    public function create(): View
    {
        $medicos = Medico::all();
        $pacientes = Paciente::all();
        return view('Citas.create', compact('medicos', 'pacientes'));
    }

    public function store(Request $request): RedirectResponse
    {
        // 1. Validamos los datos básicos
        $validated = $request->validate([
            'medico_id' => 'required|exists:medicos,id',
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha_hora' => 'required', // Quitamos 'date' para evitar conflicto con la 'T' de datetime-local
            'motivo' => 'required|string|max:255',
        ]);

        // 2. Limpiamos la fecha: cambiamos 'T' por un espacio para que MySQL lo entienda
        $validated['fecha_hora'] = str_replace('T', ' ', $request->fecha_hora);
        
        // 3. Asignamos estado por defecto
        $validated['estado'] = 'pendiente';

        // 4. Guardamos
        Cita::create($validated);

        return redirect()->route('citas.index')->with('success', 'Cita agendada correctamente.');
    }

    public function edit(int $id): View
    {
        $cita = Cita::findOrFail($id);
        $medicos = Medico::all();
        $pacientes = Paciente::all();
        return view('Citas.edit', compact('cita', 'medicos', 'pacientes'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $cita = Cita::findOrFail($id);
        
        $validated = $request->validate([
            'medico_id' => 'required|exists:medicos,id',
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha_hora' => 'required',
            'motivo' => 'required|string|max:255',
            'estado' => 'required|string',
        ]);

        // Aseguramos formato de fecha al actualizar
        $validated['fecha_hora'] = str_replace('T', ' ', $request->fecha_hora);

        $cita->update($validated);

        return redirect()->route('citas.index')->with('success', 'Cita actualizada correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Cita::findOrFail($id)->delete();
        return redirect()->route('citas.index')->with('success', 'Cita eliminada.');
    }
}