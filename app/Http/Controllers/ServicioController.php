<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ServicioController extends Controller
{
    public function index(): View
    {
        $servicios = Servicio::all();
        return view('Servicios.index', compact('servicios'));
    }

    public function create(): View
    {
        return view('Servicios.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'duracion_minutos' => 'required|integer|min:5',
        ]);

        Servicio::create($validated);

        return redirect()->route('servicios.index')->with('success', 'Servicio creado exitosamente.');
    }

    public function edit(Servicio $servicio): View
    {
        return view('Servicios.edit', compact('servicio'));
    }

    public function update(Request $request, Servicio $servicio): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'duracion_minutos' => 'required|integer|min:5',
        ]);

        $servicio->update($validated);

        return redirect()->route('servicios.index')->with('success', 'Servicio actualizado.');
    }

    public function destroy(Servicio $servicio): RedirectResponse
    {
        $servicio->delete();
        return redirect()->route('servicios.index')->with('success', 'Servicio eliminado.');
    }
}