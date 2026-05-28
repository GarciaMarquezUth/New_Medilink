<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Disponibilidad;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Servicio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalCitaTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_portal_shows_available_slots(): void
    {
        $medico = $this->createMedico();
        $servicio = $this->createServicio();
        $this->createDisponibilidad($medico);

        $response = $this->get(route('portal.citas.index', [
            'medico_id' => $medico->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-06-01',
        ]));

        $response
            ->assertOk()
            ->assertSee('09:00 - 09:30')
            ->assertSee('Solicitar cita');
    }

    public function test_patient_can_request_appointment_from_public_portal(): void
    {
        $medico = $this->createMedico();
        $servicio = $this->createServicio();
        $this->createDisponibilidad($medico);

        $this->post(route('portal.citas.store'), [
            'medico_id' => $medico->id,
            'servicio_id' => $servicio->id,
            'fecha_hora' => '2026-06-01T09:00',
            'nombre' => 'Paciente',
            'apellido' => 'Portal',
            'fecha_nacimiento' => '1992-05-10',
            'genero' => 'No especificado',
            'email' => 'paciente.portal@example.com',
            'telefono' => '555-2026',
            'motivo' => 'Consulta desde portal',
        ])->assertRedirect(route('portal.citas.index'));

        $paciente = Paciente::where('email', 'paciente.portal@example.com')->firstOrFail();

        $this->assertDatabaseHas('citas', [
            'medico_id' => $medico->id,
            'paciente_id' => $paciente->id,
            'servicio_id' => $servicio->id,
            'fecha_hora' => '2026-06-01 09:00:00',
            'estado' => 'agendada',
        ]);
    }

    public function test_patient_portal_hides_occupied_slots(): void
    {
        $medico = $this->createMedico();
        $servicio = $this->createServicio(60);
        $paciente = $this->createPaciente();
        $this->createDisponibilidad($medico);

        Cita::create([
            'medico_id' => $medico->id,
            'paciente_id' => $paciente->id,
            'servicio_id' => $servicio->id,
            'fecha_hora' => '2026-06-01 09:00:00',
            'motivo' => 'Cita existente',
            'estado' => 'agendada',
        ]);

        $response = $this->get(route('portal.citas.index', [
            'medico_id' => $medico->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-06-01',
        ]));

        $response
            ->assertOk()
            ->assertDontSee('09:00 - 10:00')
            ->assertSee('10:00 - 11:00');
    }

    private function createMedico(): Medico
    {
        return Medico::create([
            'nombre' => 'Doctor',
            'apellido' => 'Portal',
            'email' => fake()->unique()->safeEmail(),
            'especialidad' => 'General',
            'telefono' => '555-0000',
        ]);
    }

    private function createServicio(int $duracion = 30): Servicio
    {
        return Servicio::create([
            'nombre' => 'Consulta portal '.$duracion,
            'descripcion' => 'Servicio para portal',
            'duracion_minutos' => $duracion,
            'precio' => 500,
            'activo' => true,
        ]);
    }

    private function createDisponibilidad(Medico $medico): Disponibilidad
    {
        return Disponibilidad::create([
            'medico_id' => $medico->id,
            'dia_semana' => 1,
            'hora_inicio' => '09:00',
            'hora_fin' => '17:00',
            'activo' => true,
        ]);
    }

    private function createPaciente(): Paciente
    {
        return Paciente::create([
            'nombre' => 'Paciente',
            'apellido' => 'Existente',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 'No especificado',
            'email' => fake()->unique()->safeEmail(),
            'telefono' => '555-1111',
        ]);
    }
}
