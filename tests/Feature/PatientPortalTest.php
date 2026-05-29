<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Disponibilidad;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_portal_is_public_and_lists_available_slots(): void
    {
        $medico = $this->createMedico('Doctor Portal');
        $servicio = $this->createServicio(30, $medico);
        $this->createDisponibilidad($medico, 1);

        $response = $this->get(route('portal-citas.index', [
            'medico_id' => $medico->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-06-01',
        ]));

        $response
            ->assertOk()
            ->assertSee('Portal del paciente')
            ->assertSee('09:00')
            ->assertSee('Termina 09:30');
    }

    public function test_patient_portal_filters_services_by_selected_medico(): void
    {
        $medico = $this->createMedico('Doctor Servicios');
        $otroMedico = $this->createMedico('Doctor Otro');
        $servicio = $this->createServicio(30, $medico);
        $otroServicio = $this->createServicio(45, $otroMedico);

        $this->get(route('portal-citas.index', [
            'medico_id' => $medico->id,
        ]))
            ->assertOk()
            ->assertSee($servicio->nombre)
            ->assertDontSee($otroServicio->nombre);
    }

    public function test_patient_portal_shows_message_when_medico_has_no_services(): void
    {
        $medico = $this->createMedico('Doctor Sin Servicios');

        $this->get(route('portal-citas.index', [
            'medico_id' => $medico->id,
        ]))
            ->assertOk()
            ->assertSee('Este médico no tiene servicios disponibles.');
    }

    public function test_patient_portal_rejects_service_not_assigned_to_medico(): void
    {
        $medico = $this->createMedico('Doctor Portal Valido');
        $otroMedico = $this->createMedico('Doctor Servicio Ajeno');
        $servicioAjeno = $this->createServicio(30, $otroMedico);
        $this->createDisponibilidad($medico, 1);

        $this->post(route('portal-citas.store'), [
            'medico_id' => $medico->id,
            'servicio_id' => $servicioAjeno->id,
            'fecha' => '2026-06-01',
            'horario' => '2026-06-01T09:00',
            'nombre' => 'Paciente',
            'apellido' => 'Servicio Ajeno',
            'email' => 'servicio-ajeno@example.com',
            'telefono' => '555-1212',
            'motivo' => 'Consulta invalida',
        ])->assertSessionHasErrors('servicio_id');

        $this->assertDatabaseCount('citas', 0);
    }

    public function test_guest_patient_portal_saves_pending_appointment_and_requires_login(): void
    {
        $medico = $this->createMedico('Doctor Nuevo');
        $servicio = $this->createServicio(30, $medico);
        $this->createDisponibilidad($medico, 1);

        $this->post(route('portal-citas.store'), [
            'medico_id' => $medico->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-06-01',
            'horario' => '2026-06-01T09:00',
            'nombre' => 'Paciente',
            'apellido' => 'Portal',
            'email' => 'portal@example.com',
            'telefono' => '555-2222',
            'motivo' => 'Consulta desde portal',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('portal_cita_pendiente')
            ->assertSessionHas('url.intended', route('portal-citas.confirm'));

        $this->assertDatabaseCount('pacientes', 0);
        $this->assertDatabaseCount('citas', 0);
    }

    public function test_authenticated_patient_portal_creates_new_patient_and_appointment(): void
    {
        $user = User::factory()->create([
            'name' => 'Paciente Portal',
            'email' => 'portal@example.com',
        ]);
        $medico = $this->createMedico('Doctor Nuevo');
        $servicio = $this->createServicio(30, $medico);
        $this->createDisponibilidad($medico, 1);

        $this->actingAs($user)->post(route('portal-citas.store'), [
            'medico_id' => $medico->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-06-01',
            'horario' => '2026-06-01T09:00',
            'motivo' => 'Consulta desde portal',
        ])->assertRedirect(route('portal-citas.index'));

        $this->assertDatabaseHas('pacientes', [
            'nombre' => 'Paciente',
            'apellido' => 'Portal',
            'email' => 'portal@example.com',
            'telefono' => '',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('citas', [
            'medico_id' => $medico->id,
            'servicio_id' => $servicio->id,
            'fecha_hora' => '2026-06-01 09:00:00',
            'motivo' => 'Consulta desde portal',
            'estado' => Cita::ESTADO_AGENDADA,
        ]);
    }

    public function test_patient_portal_reuses_existing_patient_by_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Paciente Existente',
            'email' => 'existente@example.com',
        ]);
        $medico = $this->createMedico('Doctor Reuso');
        $servicio = $this->createServicio(30, $medico);
        $paciente = $this->createPaciente('Paciente Existente', 'existente@example.com', '555-3333');
        $this->createDisponibilidad($medico, 1);

        $this->actingAs($user)->post(route('portal-citas.store'), [
            'medico_id' => $medico->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-06-01',
            'horario' => '2026-06-01T10:00',
            'motivo' => 'Consulta para paciente existente',
        ])->assertRedirect(route('portal-citas.index'));

        $this->assertDatabaseCount('pacientes', 1);
        $this->assertDatabaseHas('pacientes', [
            'id' => $paciente->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('citas', [
            'paciente_id' => $paciente->id,
            'fecha_hora' => '2026-06-01 10:00:00',
        ]);
    }

    public function test_authenticated_patient_portal_hides_manual_patient_fields(): void
    {
        $user = User::factory()->create([
            'name' => 'Paciente Visual',
            'email' => 'visual@example.com',
        ]);
        $medico = $this->createMedico('Doctor Visual');
        $servicio = $this->createServicio(30, $medico);
        $this->createDisponibilidad($medico, 1);

        $this->actingAs($user)
            ->get(route('portal-citas.index', [
                'medico_id' => $medico->id,
                'servicio_id' => $servicio->id,
                'fecha' => '2026-06-01',
            ]))
            ->assertOk()
            ->assertSee('Usaremos los datos de tu cuenta para registrar la cita.')
            ->assertSee('Motivo de consulta')
            ->assertDontSee('name="nombre"', false)
            ->assertDontSee('name="apellido"', false)
            ->assertDontSee('name="email"', false)
            ->assertDontSee('name="telefono"', false);
    }

    public function test_pending_guest_appointment_can_be_confirmed_after_login(): void
    {
        $user = User::factory()->create([
            'name' => 'Paciente Confirmado',
            'email' => 'confirmado@example.com',
        ]);
        $medico = $this->createMedico('Doctor Confirmacion');
        $servicio = $this->createServicio(30, $medico);
        $this->createDisponibilidad($medico, 1);

        $this->post(route('portal-citas.store'), [
            'medico_id' => $medico->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-06-01',
            'horario' => '2026-06-01T09:15',
            'nombre' => 'Paciente',
            'apellido' => 'Confirmado',
            'email' => 'confirmado@example.com',
            'telefono' => '555-7777',
            'motivo' => 'Consulta pendiente',
        ])->assertRedirect(route('login'));

        $this->actingAs($user)
            ->get(route('portal-citas.confirm'))
            ->assertOk()
            ->assertSee('Confirma tu cita médica')
            ->assertSee('Consulta pendiente');

        $this->actingAs($user)
            ->post(route('portal-citas.confirm.store'))
            ->assertRedirect(route('portal-citas.index'))
            ->assertSessionMissing('portal_cita_pendiente');

        $this->assertDatabaseHas('pacientes', [
            'email' => 'confirmado@example.com',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('citas', [
            'medico_id' => $medico->id,
            'servicio_id' => $servicio->id,
            'fecha_hora' => '2026-06-01 09:15:00',
            'motivo' => 'Consulta pendiente',
            'estado' => Cita::ESTADO_AGENDADA,
        ]);
    }

    public function test_pending_appointment_is_revalidated_after_login(): void
    {
        $user = User::factory()->create([
            'name' => 'Paciente Revalidado',
            'email' => 'revalidado@example.com',
        ]);
        $medico = $this->createMedico('Doctor Revalidacion');
        $servicio = $this->createServicio(60, $medico);
        $paciente = $this->createPaciente('Paciente Base', 'base-revalidacion@example.com', '555-8888');
        $this->createDisponibilidad($medico, 1);

        $this->post(route('portal-citas.store'), [
            'medico_id' => $medico->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-06-01',
            'horario' => '2026-06-01T09:00',
            'nombre' => 'Paciente',
            'apellido' => 'Revalidado',
            'email' => 'revalidado@example.com',
            'telefono' => '555-9999',
            'motivo' => 'Consulta por confirmar',
        ])->assertRedirect(route('login'));

        Cita::create([
            'medico_id' => $medico->id,
            'paciente_id' => $paciente->id,
            'servicio_id' => $servicio->id,
            'fecha_hora' => '2026-06-01 09:00:00',
            'motivo' => 'Consulta ocupante',
            'estado' => Cita::ESTADO_AGENDADA,
        ]);

        $this->actingAs($user)
            ->post(route('portal-citas.confirm.store'))
            ->assertSessionHasErrors('horario');

        $this->assertDatabaseCount('citas', 1);
    }

    public function test_patient_portal_rejects_overlapping_slot(): void
    {
        $medico = $this->createMedico('Doctor Ocupado');
        $servicio = $this->createServicio(60, $medico);
        $paciente = $this->createPaciente('Paciente Base', 'base@example.com', '555-4444');
        $this->createDisponibilidad($medico, 1);

        Cita::create([
            'medico_id' => $medico->id,
            'paciente_id' => $paciente->id,
            'servicio_id' => $servicio->id,
            'fecha_hora' => '2026-06-01 09:00:00',
            'motivo' => 'Consulta original',
            'estado' => Cita::ESTADO_AGENDADA,
        ]);

        $this->post(route('portal-citas.store'), [
            'medico_id' => $medico->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-06-01',
            'horario' => '2026-06-01T09:30',
            'nombre' => 'Paciente Traslape',
            'apellido' => 'Portal',
            'email' => 'traslape@example.com',
            'telefono' => '555-5555',
            'motivo' => 'Consulta traslapada',
        ])->assertSessionHasErrors('horario');

        $this->assertDatabaseCount('citas', 1);
    }

    private function createMedico(string $nombre): Medico
    {
        return Medico::create([
            'nombre' => $nombre,
            'apellido' => 'Prueba',
            'email' => fake()->unique()->safeEmail(),
            'especialidad' => 'General',
            'telefono' => '555-0000',
        ]);
    }

    private function createPaciente(string $nombre, string $email, string $telefono): Paciente
    {
        return Paciente::create([
            'nombre' => $nombre,
            'apellido' => 'Prueba',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 'N/A',
            'email' => $email,
            'telefono' => $telefono,
        ]);
    }

    private function createServicio(int $duracion, ?Medico $medico = null): Servicio
    {
        $servicio = Servicio::create([
            'nombre' => 'Consulta '.$duracion.' minutos',
            'descripcion' => 'Servicio de prueba',
            'duracion_minutos' => $duracion,
            'precio' => 100,
            'activo' => true,
        ]);

        if ($medico) {
            $medico->servicios()->attach($servicio->id);
        }

        return $servicio;
    }

    private function createDisponibilidad(Medico $medico, int $diaSemana): Disponibilidad
    {
        return Disponibilidad::create([
            'medico_id' => $medico->id,
            'dia_semana' => $diaSemana,
            'hora_inicio' => '08:00',
            'hora_fin' => '12:00',
            'activo' => true,
        ]);
    }
}
