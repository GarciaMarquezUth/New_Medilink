<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\User;
use App\Services\PatientProfileService;
use App\Services\PendingAppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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
            'contacto_emergencia' => 'nullable|string|max:255',
            'telefono_emergencia' => 'nullable|string|max:20',
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
            'contacto_emergencia' => 'nullable|string|max:255',
            'telefono_emergencia' => 'nullable|string|max:20',
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

    public function profile(PatientProfileService $profiles): View
    {
        /** @var User $user */
        $user = Auth::user();
        $paciente = $profiles->ensurePatientFor($user);

        return view('Pacientes.profile', compact('paciente', 'user'));
    }

    public function updateProfile(Request $request, PatientProfileService $profiles, PendingAppointmentService $pendingAppointments): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $paciente = $profiles->ensurePatientFor($user);

        $validated = $request->validate($this->patientProfileRules());
        $paciente->update($validated);

        if ($pendingAppointments->hasPending()) {
            $payload = $pendingAppointments->pending();

            try {
                $pendingAppointments->confirmPendingFor($user);
                session()->forget('url.intended');

                return redirect()->route('dashboard')->with('success', PendingAppointmentService::CONFIRMED_MESSAGE);
            } catch (ValidationException) {
                $pendingAppointments->forgetPending();
                session()->forget('url.intended');

                return redirect()->route('portal-citas.index')
                    ->with('error', PendingAppointmentService::UNAVAILABLE_MESSAGE)
                    ->withInput($payload ?? []);
            }
        }

        return redirect()->route('dashboard')->with('success', 'Perfil médico actualizado correctamente.');
    }

    public function showForMedico(Paciente $paciente): View
    {
        /** @var User|null $user */
        $user = Auth::user();
        $medico = Medico::where('user_id', $user?->id)->first();

        if (! $medico) {
            abort(403, 'Tu usuario médico no está vinculado a un registro de médico.');
        }

        $citas = Cita::with('servicio')
            ->where('medico_id', $medico->id)
            ->where('paciente_id', $paciente->id)
            ->orderByDesc('fecha_hora')
            ->get();

        if ($citas->isEmpty()) {
            abort(403, 'No autorizado.');
        }

        $estadoLabels = Cita::estados();

        return view('Pacientes.medico-show', compact('paciente', 'medico', 'citas', 'estadoLabels'));
    }

    private function patientProfileRules(): array
    {
        return [
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'genero' => ['required', 'string', 'max:20'],
            'telefono' => ['required', 'string', 'max:20'],
            'direccion' => ['required', 'string', 'max:500'],
            'tipo_sangre' => ['required', 'string', 'max:5', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'No sé'])],
            'alergias' => ['required', 'string', 'max:1000'],
            'contacto_emergencia' => ['nullable', 'string', 'max:255'],
            'telefono_emergencia' => ['nullable', 'string', 'max:20'],
        ];
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
