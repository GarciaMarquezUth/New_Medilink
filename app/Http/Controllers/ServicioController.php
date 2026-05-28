<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServicioController extends Controller
{
    public function index(): View
    {
        $servicios = Servicio::orderBy('nombre')->get();

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
            'descripcion' => 'nullable|string',
            'duracion_minutos' => 'required|integer|min:5|max:480',
            'precio' => 'nullable|numeric|min:0|max:999999.99',
        ]);

        $validated['activo'] = $request->boolean('activo');

        Servicio::create($validated);

        return redirect()->route('servicios.index')->with('success', 'Servicio registrado correctamente.');
    }

    public function edit(int $id): View
    {
        $servicio = Servicio::findOrFail($id);

        return view('Servicios.edit', compact('servicio'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $servicio = Servicio::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'duracion_minutos' => 'required|integer|min:5|max:480',
            'precio' => 'nullable|numeric|min:0|max:999999.99',
        ]);

        $validated['activo'] = $request->boolean('activo');
        $servicio->update($validated);

        return redirect()->route('servicios.index')->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Servicio::findOrFail($id)->delete();

        return redirect()->route('servicios.index')->with('success', 'Servicio eliminado.');
    }
}
