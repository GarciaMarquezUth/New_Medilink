<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MedicoController extends Controller
{
    // Muestra la lista de médicos
    public function index(): View
    {
        $medicos = Medico::all();
        return view('Medicos.index', compact('medicos'));
    }

    // Muestra el formulario para crear un nuevo médico
    public function create(): View
    {
        return view('Medicos.create');
    }

    // Almacena un nuevo médico en la base de datos
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:medicos,email',
            'especialidad' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
        ]);

        Medico::create($validated);

        return redirect()->route('medicos.index')->with('success', 'Médico registrado exitosamente.');
    }

    // Muestra el formulario de edición
    public function edit(int $id): View
    {
        $medico = Medico::findOrFail($id);
        return view('Medicos.edit', compact('medico'));
    }

    // Actualiza los datos del médico
    public function update(Request $request, int $id): RedirectResponse
    {
        $medico = Medico::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:medicos,email,' . $id,
            'especialidad' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
        ]);

        $medico->update($validated);

        return redirect()->route('medicos.index')->with('success', 'Datos del médico actualizados.');
    }

    // Elimina un médico
    public function destroy(int $id): RedirectResponse
    {
        $medico = Medico::findOrFail($id);
        $medico->delete();

        return redirect()->route('medicos.index')->with('success', 'Médico eliminado del sistema.');
    }
}