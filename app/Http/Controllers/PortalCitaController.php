<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Servicio;
use App\Services\AppointmentAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PortalCitaController extends Controller
{
    public function __construct(private readonly AppointmentAvailabilityService $availabilityService) {}

    public function index(Request $request): View
    {
        $medicos = Medico::orderBy('nombre')->orderBy('apellido')->get();
        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get();
        $slots = [];

        $filters = $request->validate([
            'medico_id' => 'nullable|exists:medicos,id',
            'servicio_id' => 'nullable|exists:servicios,id',
            'fecha' => 'nullable|date|after_or_equal:today',
        ]);

        if (! empty($filters['medico_id']) && ! empty($filters['servicio_id']) && ! empty($filters['fecha'])) {
            $slots = $this->availabilityService->availableSlots(
                (int) $filters['medico_id'],
                $filters['fecha'],
                (int) $filters['servicio_id'],
            );
        }

        return view('portal.citas', compact('medicos', 'servicios', 'slots'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'medico_id' => 'required|exists:medicos,id',
            'servicio_id' => 'required|exists:servicios,id',
            'fecha_hora' => 'required|date|after_or_equal:now',
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date|before:today',
            'genero' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'motivo' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            [, $inicio] = $this->availabilityService->validateCanSchedule(
                (int) $validated['medico_id'],
                (int) $validated['servicio_id'],
                $validated['fecha_hora'],
                lock: true,
            );

            $paciente = Paciente::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'nombre' => $validated['nombre'],
                    'apellido' => $validated['apellido'],
                    'fecha_nacimiento' => $validated['fecha_nacimiento'],
                    'genero' => $validated['genero'],
                    'telefono' => $validated['telefono'],
                ],
            );

            Cita::create([
                'medico_id' => $validated['medico_id'],
                'paciente_id' => $paciente->id,
                'servicio_id' => $validated['servicio_id'],
                'fecha_hora' => $inicio->format('Y-m-d H:i:s'),
                'motivo' => $validated['motivo'],
                'estado' => 'agendada',
            ]);
        });

        return redirect()
            ->route('portal.citas.index')
            ->with('success', 'Tu cita fue solicitada correctamente. La clínica confirmará los detalles contigo.');
    }
}
