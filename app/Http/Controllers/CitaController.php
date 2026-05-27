<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CitaController extends Controller
{
    public function index(): View
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user && $user->hasAnyRole(['admin', 'recepcionista'])) {
            $citas = Cita::with(['medico', 'paciente'])->latest()->get();
        } elseif ($user && $user->hasRole('medico')) {
            $citas = Cita::with(['medico', 'paciente'])
                ->whereHas('medico', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->latest()->get();
        } else {
            $citas = Cita::with(['medico', 'paciente'])
                ->whereHas('paciente', function ($query) use ($user) {
                    $query->where('user_id', $user?->id);
                })
                ->latest()->get();
        }

        return view('Citas.index', compact('citas'));
    }

    public function create(): View
    {
        $this->authorizeCitaManagement();

        $medicos = Medico::all();
        $pacientes = Paciente::all();

        return view('Citas.create', compact('medicos', 'pacientes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeCitaManagement();

        $validated = $request->validate([
            'medico_id' => 'required|exists:medicos,id',
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha_hora' => 'required|date',
            'motivo' => 'required|string|max:255',
        ]);

        $validated['fecha_hora'] = str_replace('T', ' ', $validated['fecha_hora']);
        $validated['estado'] = 'pendiente';

        Cita::create($validated);

        return redirect()->route('citas.index')->with('success', 'Cita agendada correctamente.');
    }

    public function edit(int $id): View
    {
        $this->authorizeCitaManagement();

        $cita = Cita::findOrFail($id);
        $medicos = Medico::all();
        $pacientes = Paciente::all();

        return view('Citas.edit', compact('cita', 'medicos', 'pacientes'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeCitaManagement();

        $cita = Cita::findOrFail($id);

        $validated = $request->validate([
            'medico_id' => 'required|exists:medicos,id',
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha_hora' => 'required|date',
            'motivo' => 'required|string|max:255',
            'estado' => 'required|in:pendiente,confirmada,cancelada,atendida,no_presentada',
        ]);

        $validated['fecha_hora'] = str_replace('T', ' ', $validated['fecha_hora']);

        $cita->update($validated);

        return redirect()->route('citas.index')->with('success', 'Cita actualizada correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeCitaManagement();

        Cita::findOrFail($id)->delete();

        return redirect()->route('citas.index')->with('success', 'Cita eliminada.');
    }

    public function marcarAtendida(int $id): RedirectResponse
    {
        $cita = Cita::with('medico')->findOrFail($id);

        $this->authorizeMedicoCita($cita);

        $cita->update(['estado' => 'atendida']);

        return redirect()->route('citas.index')->with('success', 'Cita marcada como atendida.');
    }

    public function noPresentada(int $id): RedirectResponse
    {
        $cita = Cita::with('medico')->findOrFail($id);

        $this->authorizeMedicoCita($cita);

        $cita->update(['estado' => 'no_presentada']);

        return redirect()->route('citas.index')->with('success', 'Cita marcada como no presentada.');
    }

    private function authorizeCitaManagement(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->hasAnyRole(['admin', 'recepcionista'])) {
            abort(403, 'Acceso denegado');
        }
    }

    private function authorizeMedicoCita(Cita $cita): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->hasRole('medico') || $cita->medico?->user_id !== $user->id) {
            abort(403, 'No autorizado.');
        }
    }
}
