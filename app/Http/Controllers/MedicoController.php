<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MedicoController extends Controller
{
    public function index(): View
    {
        $medicos = Medico::with(['user', 'servicios'])->get();

        return view('Medicos.index', compact('medicos'));
    }

    public function create(): View
    {
        $usuariosMedicos = $this->usuariosConRol('medico');
        $servicios = Servicio::orderBy('nombre')->get();

        return view('Medicos.create', compact('usuariosMedicos', 'servicios'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:medicos,email',
            'especialidad' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'user_id' => 'nullable|exists:users,id|unique:medicos,user_id',
            'servicio_ids' => ['required', 'array', 'min:1'],
            'servicio_ids.*' => ['integer', 'distinct', 'exists:servicios,id'],
        ]);

        $servicioIds = $validated['servicio_ids'];
        unset($validated['servicio_ids']);

        $validated['user_id'] = $validated['user_id'] ?? null;
        $this->ensureUserHasRole($validated['user_id'], 'medico');

        $medico = Medico::create($validated);
        $medico->servicios()->sync($servicioIds);

        return redirect()->route('medicos.index')->with('success', 'Médico registrado exitosamente.');
    }

    public function edit(int $id): View
    {
        $medico = Medico::with('servicios')->findOrFail($id);
        $usuariosMedicos = $this->usuariosConRol('medico');
        $servicios = Servicio::orderBy('nombre')->get();

        return view('Medicos.edit', compact('medico', 'usuariosMedicos', 'servicios'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $medico = Medico::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:medicos,email,'.$id,
            'especialidad' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'user_id' => 'nullable|exists:users,id|unique:medicos,user_id,'.$id,
            'servicio_ids' => ['required', 'array', 'min:1'],
            'servicio_ids.*' => ['integer', 'distinct', 'exists:servicios,id'],
        ]);

        $servicioIds = $validated['servicio_ids'];
        unset($validated['servicio_ids']);

        $validated['user_id'] = $validated['user_id'] ?? null;
        $this->ensureUserHasRole($validated['user_id'], 'medico');

        $medico->update($validated);
        $medico->servicios()->sync($servicioIds);

        return redirect()->route('medicos.index')->with('success', 'Datos del médico actualizados.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $medico = Medico::findOrFail($id);
        $medico->delete();

        return redirect()->route('medicos.index')->with('success', 'Médico eliminado del sistema.');
    }

    private function usuariosConRol(string $role)
    {
        return User::whereHas('roles', function ($query) use ($role) {
            $query->where('name', $role);
        })->orderBy('name')->get();
    }

    private function ensureUserHasRole(?int $userId, string $role): void
    {
        if (! $userId) {
            return;
        }

        $user = User::findOrFail($userId);

        if (! $user->hasRole($role)) {
            throw ValidationException::withMessages([
                'user_id' => "El usuario seleccionado debe tener rol {$role}.",
            ]);
        }
    }
}
