<?php

namespace App\Livewire;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Servicio;
use App\Services\AppointmentAvailabilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class PortalAppointmentScheduler extends Component
{
    public ?int $medicoId = null;

    public ?int $servicioId = null;

    public string $fecha = '';

    public string $fechaHora = '';

    public string $nombre = '';

    public string $apellido = '';

    public string $fechaNacimiento = '';

    public string $genero = '';

    public string $email = '';

    public string $telefono = '';

    public string $motivo = '';

    public function mount(): void
    {
        $this->fecha = now()->toDateString();
    }

    public function updatedMedicoId(): void
    {
        $this->resetSlotSelection();
    }

    public function updatedServicioId(): void
    {
        $this->resetSlotSelection();
    }

    public function updatedFecha(): void
    {
        $this->resetSlotSelection();
    }

    public function submit(AppointmentAvailabilityService $availabilityService): void
    {
        $validated = $this->validate([
            'medicoId' => 'required|exists:medicos,id',
            'servicioId' => 'required|exists:servicios,id',
            'fechaHora' => 'required|date|after_or_equal:now',
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'fechaNacimiento' => 'required|date|before:today',
            'genero' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'motivo' => 'required|string|max:255',
        ], [], [
            'medicoId' => 'medico',
            'servicioId' => 'servicio',
            'fechaHora' => 'horario',
            'fechaNacimiento' => 'fecha de nacimiento',
        ]);

        DB::transaction(function () use ($availabilityService, $validated) {
            [, $inicio] = $availabilityService->validateCanSchedule(
                (int) $validated['medicoId'],
                (int) $validated['servicioId'],
                $validated['fechaHora'],
                lock: true,
            );

            $paciente = Paciente::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'nombre' => $validated['nombre'],
                    'apellido' => $validated['apellido'],
                    'fecha_nacimiento' => $validated['fechaNacimiento'],
                    'genero' => $validated['genero'],
                    'telefono' => $validated['telefono'],
                ],
            );

            Cita::create([
                'medico_id' => $validated['medicoId'],
                'paciente_id' => $paciente->id,
                'servicio_id' => $validated['servicioId'],
                'fecha_hora' => $inicio->format('Y-m-d H:i:s'),
                'motivo' => $validated['motivo'],
                'estado' => 'agendada',
            ]);
        });

        $this->reset([
            'fechaHora',
            'nombre',
            'apellido',
            'fechaNacimiento',
            'genero',
            'email',
            'telefono',
            'motivo',
        ]);

        $this->dispatch('appointment-requested');
        session()->flash('success', 'Tu cita fue solicitada correctamente. La clinica confirmara los detalles contigo.');
    }

    public function render(AppointmentAvailabilityService $availabilityService): View
    {
        return view('livewire.portal-appointment-scheduler', [
            'medicos' => Medico::orderBy('nombre')->orderBy('apellido')->get(),
            'servicios' => Servicio::where('activo', true)->orderBy('nombre')->get(),
            'slots' => $this->slots($availabilityService),
        ]);
    }

    private function slots(AppointmentAvailabilityService $availabilityService): array
    {
        if (! $this->medicoId || ! $this->servicioId || ! $this->fecha) {
            return [];
        }

        return $availabilityService->availableSlots($this->medicoId, $this->fecha, $this->servicioId);
    }

    private function resetSlotSelection(): void
    {
        $this->reset('fechaHora');
    }
}
