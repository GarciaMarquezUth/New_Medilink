<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HistoriaClinicaController extends Controller
{
    public function edit(Cita $cita): View
    {
        $cita->load(['paciente', 'medico', 'servicio', 'historiaClinica']);
        $this->authorizeMedicoCita($cita);

        return view('HistoriasClinicas.edit', [
            'cita' => $cita,
            'historia' => $cita->historiaClinica,
            'estadosOcupantes' => Cita::estadosOcupantes(),
        ]);
    }

    public function update(Request $request, Cita $cita): RedirectResponse
    {
        $cita->load(['paciente', 'medico', 'servicio', 'historiaClinica']);
        $this->authorizeMedicoCita($cita);

        $validated = $request->validate([
            'diagnostico' => ['nullable', 'string', 'max:5000'],
            'tratamiento' => ['nullable', 'string', 'max:5000'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
            'receta' => ['nullable', 'string', 'max:5000'],
            'indicaciones' => ['nullable', 'string', 'max:5000'],
            'seguimiento_fecha' => ['nullable', 'date'],
        ]);

        $payload = array_merge($validated, [
            'updated_by' => Auth::id(),
        ]);

        if (! $cita->historiaClinica) {
            $payload['created_by'] = Auth::id();
        }

        $cita->historiaClinica()->updateOrCreate(['cita_id' => $cita->id], $payload);

        if ($request->boolean('marcar_atendida') && in_array($cita->estado, Cita::estadosOcupantes(), true)) {
            $cita->update(['estado' => Cita::ESTADO_ATENDIDA]);
        }

        return redirect()
            ->route('historias-clinicas.edit', $cita)
            ->with('success', 'Historia clínica guardada correctamente.');
    }

    private function authorizeMedicoCita(Cita $cita): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->hasRole('medico') || ! $user->can('citas.editar') || $cita->medico?->user_id !== $user->id) {
            abort(403, 'No autorizado.');
        }
    }
}
