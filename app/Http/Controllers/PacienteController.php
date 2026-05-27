<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PacienteController extends Controller
{
    public function index(): View
    {
        $pacientes = Paciente::all();
        return view('Pacientes.index', compact('pacientes'));
    }

    public function create(): View
    {
        return view('Pacientes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'genero' => 'required|string|max:20',
            'email' => 'required|email|unique:pacientes,email',
            'telefono' => 'required|string|max:20',
            'direccion' => 'nullable|string',
            'tipo_sangre' => 'nullable|string|max:5',
            'alergias' => 'nullable|string',
        ]);

        Paciente::create($validated);
        return redirect()->route('pacientes.index')->with('success', 'Paciente registrado correctamente.');
    }

    public function edit(int $id): View
    {
        $paciente = Paciente::findOrFail($id);
        return view('Pacientes.edit', compact('paciente'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $paciente = Paciente::findOrFail($id);
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'genero' => 'required|string|max:20',
            'email' => 'required|email|unique:pacientes,email,' . $id,
            'telefono' => 'required|string|max:20',
            'direccion' => 'nullable|string',
            'tipo_sangre' => 'nullable|string|max:5',
            'alergias' => 'nullable|string',
        ]);

        $paciente->update($validated);
        return redirect()->route('pacientes.index')->with('success', 'Datos actualizados.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Paciente::findOrFail($id)->delete();
        return redirect()->route('pacientes.index')->with('success', 'Paciente eliminado.');
    }
}