<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PacienteController extends Controller
{
    public function index(): View
    {
        $pacientes = Paciente::with('user')->get();

        return view('Pacientes.index', compact('pacientes'));
    }

    public function create(): View
    {
        $usuariosPacientes = $this->usuariosConRol('paciente');

        return view('Pacientes.create', compact('usuariosPacientes'));
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
            'user_id' => 'nullable|exists:users,id|unique:pacientes,user_id',
        ]);

        $validated['user_id'] = $validated['user_id'] ?? null;
        $this->ensureUserHasRole($validated['user_id'], 'paciente');

        Paciente::create($validated);

        return redirect()->route('pacientes.index')->with('success', 'Paciente registrado correctamente.');
    }

    public function edit(int $id): View
    {
        $paciente = Paciente::findOrFail($id);
        $usuariosPacientes = $this->usuariosConRol('paciente');

        return view('Pacientes.edit', compact('paciente', 'usuariosPacientes'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $paciente = Paciente::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'genero' => 'required|string|max:20',
            'email' => 'required|email|unique:pacientes,email,'.$id,
            'telefono' => 'required|string|max:20',
            'direccion' => 'nullable|string',
            'tipo_sangre' => 'nullable|string|max:5',
            'alergias' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id|unique:pacientes,user_id,'.$id,
        ]);

        $validated['user_id'] = $validated['user_id'] ?? null;
        $this->ensureUserHasRole($validated['user_id'], 'paciente');

        $paciente->update($validated);

        return redirect()->route('pacientes.index')->with('success', 'Datos actualizados.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Paciente::findOrFail($id)->delete();

        return redirect()->route('pacientes.index')->with('success', 'Paciente eliminado.');
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
