<?php

namespace App\Http\Controllers;

use App\Models\Disponibilidad;
use App\Models\Medico;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DisponibilidadController extends Controller
{
    // Muestra las disponibilidades de un médico específico
    public function index(Medico $medico): View
    {
        $disponibilidades = $medico->disponibilidades;
        return view('Disponibilidades.index', compact('medico', 'disponibilidades'));
    }

    public function store(Request $request, Medico $medico): RedirectResponse
    {
        $validated = $request->validate([
            'dia_semana' => 'required|integer|between:1,7',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        $medico->disponibilidades()->create($validated);

        return back()->with('success', 'Horario agregado correctamente.');
    }

    public function destroy(Disponibilidad $disponibilidad): RedirectResponse
    {
        $disponibilidad->delete();
        return back()->with('success', 'Horario eliminado.');
    }
}