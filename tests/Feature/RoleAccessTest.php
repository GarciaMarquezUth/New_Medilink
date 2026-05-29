<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Disponibilidad;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Servicio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_all_citas(): void
    {
        $admin = $this->userWithRole('admin');
        $this->createCitaForMedico('Paciente Admin Uno', 'Doctor Uno');
        $this->createCitaForMedico('Paciente Admin Dos', 'Doctor Dos');

        $response = $this->actingAs($admin)->get(route('citas.index'));

        $response
            ->assertOk()
            ->assertSee('Paciente Admin Uno')
            ->assertSee('Paciente Admin Dos');
    }

    public function test_recepcionista_can_see_all_citas(): void
    {
        $recepcionista = $this->userWithRole('recepcionista');
        $this->createCitaForMedico('Paciente Recepcion Uno', 'Doctor Uno');
        $this->createCitaForMedico('Paciente Recepcion Dos', 'Doctor Dos');

        $response = $this->actingAs($recepcionista)->get(route('citas.index'));

        $response
            ->assertOk()
            ->assertSee('Paciente Recepcion Uno')
            ->assertSee('Paciente Recepcion Dos');
    }

    public function test_medico_only_sees_own_citas(): void
    {
        $medicoUser = $this->userWithRole('medico');
        $otroMedicoUser = $this->userWithRole('medico');

        $this->createCitaForMedico('Paciente Propio', 'Doctor Propio', $medicoUser);
        $this->createCitaForMedico('Paciente Ajeno', 'Doctor Ajeno', $otroMedicoUser);

        $response = $this->actingAs($medicoUser)->get(route('citas.index'));

        $response
            ->assertOk()
            ->assertSee('Paciente Propio')
            ->assertDontSee('Paciente Ajeno');
    }

    public function test_medico_dashboard_only_shows_own_clinical_data(): void
    {
        $medicoUser = $this->userWithRole('medico');
        $otroMedicoUser = $this->userWithRole('medico');

        $this->createCitaForMedico('Paciente Propio', 'Doctor Propio', $medicoUser);
        $this->createCitaForMedico('Paciente Ajeno', 'Doctor Ajeno', $otroMedicoUser);

        $response = $this->actingAs($medicoUser)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('Panel médico')
            ->assertSee('Citas de hoy')
            ->assertSee('Próximas citas')
            ->assertSee('Pendientes por atender')
            ->assertSee('Citas atendidas')
            ->assertSee('Mis pacientes')
            ->assertSee('Paciente Propio')
            ->assertDontSee('Paciente Ajeno')
            ->assertDontSee('Estado del sistema')
            ->assertDontSee('Nuevo médico')
            ->assertDontSee('Nuevo paciente')
            ->assertDontSee('Nuevo servicio')
            ->assertDontSee('Médicos</p>', false)
            ->assertDontSee('Pacientes</p>', false);
    }

    public function test_medico_can_mark_own_citas_as_atendida_or_no_presentada(): void
    {
        $medicoUser = $this->userWithRole('medico');
        $citaAtendida = $this->createCitaForMedico('Paciente Atendida', 'Doctor Agenda', $medicoUser);
        $citaNoPresentada = $this->createCitaForMedico('Paciente Ausente', 'Doctor Agenda', $medicoUser);

        $this->actingAs($medicoUser)
            ->post(route('citas.atendida', $citaAtendida->id))
            ->assertRedirect(route('citas.index'));

        $this->assertDatabaseHas('citas', [
            'id' => $citaAtendida->id,
            'estado' => Cita::ESTADO_ATENDIDA,
        ]);

        $this->actingAs($medicoUser)
            ->post(route('citas.no-presentada', $citaNoPresentada->id))
            ->assertRedirect(route('citas.index'));

        $this->assertDatabaseHas('citas', [
            'id' => $citaNoPresentada->id,
            'estado' => Cita::ESTADO_NO_SHOW,
        ]);
    }

    public function test_medico_cannot_manage_citas(): void
    {
        $medicoUser = $this->userWithRole('medico');
        $cita = $this->createCitaForMedico('Paciente Bloqueado', 'Doctor Bloqueado', $medicoUser);

        $this->actingAs($medicoUser)
            ->get(route('citas.create'))
            ->assertForbidden();

        $this->actingAs($medicoUser)
            ->get(route('citas.edit', $cita->id))
            ->assertForbidden();

        $this->actingAs($medicoUser)
            ->delete(route('citas.destroy', $cita->id))
            ->assertForbidden();
    }

    public function test_paciente_dashboard_only_shows_patient_content_and_own_citas(): void
    {
        $pacienteUser = $this->userWithRole('paciente');
        $otroPacienteUser = $this->userWithRole('paciente');
        $paciente = $this->createPaciente('Paciente Propio', $pacienteUser);
        $otroPaciente = $this->createPaciente('Paciente Ajeno', $otroPacienteUser);

        $this->createCitaForPaciente($paciente, 'Consulta Propia');
        $this->createCitaForPaciente($otroPaciente, 'Consulta Ajena');

        $response = $this->actingAs($pacienteUser)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('Portal del paciente')
            ->assertSee('Consulta Propia')
            ->assertSee('Agendar cita')
            ->assertDontSee('Consulta Ajena')
            ->assertDontSee('Nuevo médico')
            ->assertDontSee('Nuevo paciente')
            ->assertDontSee('Disponibilidad médica');
    }

    public function test_paciente_only_sees_own_citas_and_can_cancel_future_own_cita(): void
    {
        $pacienteUser = $this->userWithRole('paciente');
        $otroPacienteUser = $this->userWithRole('paciente');
        $paciente = $this->createPaciente('Paciente Propio', $pacienteUser);
        $otroPaciente = $this->createPaciente('Paciente Ajeno', $otroPacienteUser);
        $cita = $this->createCitaForPaciente($paciente, 'Consulta Cancelable');
        $this->createCitaForPaciente($otroPaciente, 'Consulta No Visible');

        $this->actingAs($pacienteUser)
            ->get(route('citas.index'))
            ->assertOk()
            ->assertSee('Consulta Cancelable')
            ->assertSee('Cancelar')
            ->assertSee('Agendar cita')
            ->assertDontSee('Consulta No Visible')
            ->assertDontSee('Atendida')
            ->assertDontSee('No presentada')
            ->assertDontSee('Editar')
            ->assertDontSee('Eliminar');

        $this->actingAs($pacienteUser)
            ->post(route('citas.cancelar-paciente', $cita->id))
            ->assertRedirect();

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => Cita::ESTADO_CANCELADA,
        ]);
    }

    public function test_paciente_cannot_cancel_other_patient_cita_or_use_medico_actions(): void
    {
        $pacienteUser = $this->userWithRole('paciente');
        $otroPacienteUser = $this->userWithRole('paciente');
        $otroPaciente = $this->createPaciente('Paciente Ajeno', $otroPacienteUser);
        $citaAjena = $this->createCitaForPaciente($otroPaciente, 'Consulta Ajena');

        $this->actingAs($pacienteUser)
            ->post(route('citas.cancelar-paciente', $citaAjena->id))
            ->assertForbidden();

        $this->actingAs($pacienteUser)
            ->post(route('citas.atendida', $citaAjena->id))
            ->assertForbidden();

        $this->actingAs($pacienteUser)
            ->post(route('citas.no-presentada', $citaAjena->id))
            ->assertForbidden();

        $this->assertDatabaseHas('citas', [
            'id' => $citaAjena->id,
            'estado' => Cita::ESTADO_AGENDADA,
        ]);
    }

    public function test_admin_and_recepcionista_can_create_update_and_cancel_citas(): void
    {
        $admin = $this->userWithRole('admin');
        $recepcionista = $this->userWithRole('recepcionista');
        $medico = $this->createMedico('Doctor Gestion');
        $paciente = $this->createPaciente('Paciente Gestion');
        $servicio = $this->createServicio(30, $medico);
        $this->createDisponibilidad($medico, 1);

        $this->actingAs($admin)
            ->post(route('citas.store'), [
                'medico_id' => $medico->id,
                'paciente_id' => $paciente->id,
                'servicio_id' => $servicio->id,
                'fecha_hora' => '2026-06-01T09:00',
                'motivo' => 'Consulta inicial',
            ])
            ->assertRedirect(route('citas.index'));

        $cita = Cita::firstOrFail();

        $this->actingAs($recepcionista)
            ->put(route('citas.update', $cita->id), [
                'medico_id' => $medico->id,
                'paciente_id' => $paciente->id,
                'servicio_id' => $servicio->id,
                'fecha_hora' => '2026-06-02T10:30',
                'motivo' => 'Consulta reagendada',
                'estado' => Cita::ESTADO_CANCELADA,
            ])
            ->assertRedirect(route('citas.index'));

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'motivo' => 'Consulta reagendada',
            'estado' => Cita::ESTADO_CANCELADA,
        ]);
    }

    public function test_citas_reject_service_not_assigned_to_selected_medico(): void
    {
        $admin = $this->userWithRole('admin');
        $medico = $this->createMedico('Doctor Valido');
        $otroMedico = $this->createMedico('Doctor Servicio Ajeno');
        $paciente = $this->createPaciente('Paciente Servicio Ajeno');
        $servicioAjeno = $this->createServicio(30, $otroMedico);
        $this->createDisponibilidad($medico, 1);

        $this->actingAs($admin)
            ->post(route('citas.store'), [
                'medico_id' => $medico->id,
                'paciente_id' => $paciente->id,
                'servicio_id' => $servicioAjeno->id,
                'fecha_hora' => '2026-06-01T09:00',
                'motivo' => 'Consulta invalida',
            ])
            ->assertSessionHasErrors('servicio_id');

        $this->assertDatabaseCount('citas', 0);
    }

    public function test_citas_must_be_inside_medico_availability(): void
    {
        $admin = $this->userWithRole('admin');
        $medico = $this->createMedico('Doctor Sin Horario');
        $paciente = $this->createPaciente('Paciente Sin Horario');
        $servicio = $this->createServicio(30, $medico);

        $this->actingAs($admin)
            ->post(route('citas.store'), [
                'medico_id' => $medico->id,
                'paciente_id' => $paciente->id,
                'servicio_id' => $servicio->id,
                'fecha_hora' => '2026-06-01T09:00',
                'motivo' => 'Consulta fuera de horario',
            ])
            ->assertSessionHasErrors('fecha_hora');

        $this->assertDatabaseCount('citas', 0);
    }

    public function test_citas_cannot_overlap_existing_schedule(): void
    {
        $admin = $this->userWithRole('admin');
        $medico = $this->createMedico('Doctor Ocupado');
        $paciente = $this->createPaciente('Paciente Original');
        $otroPaciente = $this->createPaciente('Paciente Traslape');
        $servicio = $this->createServicio(60, $medico);
        $this->createDisponibilidad($medico, 1);

        Cita::create([
            'medico_id' => $medico->id,
            'paciente_id' => $paciente->id,
            'servicio_id' => $servicio->id,
            'fecha_hora' => '2026-06-01 09:00:00',
            'motivo' => 'Consulta original',
            'estado' => Cita::ESTADO_AGENDADA,
        ]);

        $this->actingAs($admin)
            ->post(route('citas.store'), [
                'medico_id' => $medico->id,
                'paciente_id' => $otroPaciente->id,
                'servicio_id' => $servicio->id,
                'fecha_hora' => '2026-06-01T09:30',
                'motivo' => 'Consulta con traslape',
            ])
            ->assertSessionHasErrors('fecha_hora');

        $this->assertDatabaseCount('citas', 1);
    }

    public function test_admin_can_manage_servicios(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->get(route('servicios.create'))
            ->assertOk()
            ->assertSee('Registrar servicio');

        $this->actingAs($admin)
            ->post(route('servicios.store'), [
                'nombre' => 'Consulta pediátrica',
                'descripcion' => 'Atención general para niños',
                'duracion_minutos' => 45,
                'precio' => 250,
                'activo' => '1',
            ])
            ->assertRedirect(route('servicios.index'));

        $this->assertDatabaseHas('servicios', [
            'nombre' => 'Consulta pediátrica',
            'duracion_minutos' => 45,
            'activo' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('servicios.index'))
            ->assertOk()
            ->assertSee('Consulta pediátrica');
    }

    public function test_admin_can_manage_disponibilidades_and_reject_overlaps(): void
    {
        $admin = $this->userWithRole('admin');
        $medico = $this->createMedico('Doctor Horario');

        $this->actingAs($admin)
            ->get(route('disponibilidades.create'))
            ->assertOk()
            ->assertSee('Registrar disponibilidad');

        $this->actingAs($admin)
            ->post(route('disponibilidades.store'), [
                'medico_id' => $medico->id,
                'dia_semana' => 1,
                'hora_inicio' => '08:00',
                'hora_fin' => '12:00',
                'activo' => '1',
            ])
            ->assertRedirect(route('disponibilidades.index'));

        $this->actingAs($admin)
            ->post(route('disponibilidades.store'), [
                'medico_id' => $medico->id,
                'dia_semana' => 1,
                'hora_inicio' => '11:00',
                'hora_fin' => '13:00',
                'activo' => '1',
            ])
            ->assertSessionHasErrors('hora_inicio');

        $this->assertDatabaseCount('disponibilidades', 1);

        $this->actingAs($admin)
            ->get(route('disponibilidades.index'))
            ->assertOk()
            ->assertSee('Doctor Horario');
    }

    public function test_admin_can_save_weekly_disponibilidades(): void
    {
        $admin = $this->userWithRole('admin');
        $medico = $this->createMedico('Doctor Semana');

        $this->actingAs($admin)
            ->post(route('disponibilidades.store'), [
                'medico_id' => $medico->id,
                'dias' => $this->weeklyDias([
                    1 => ['activo' => '1', 'hora_inicio' => '08:00', 'hora_fin' => '14:00'],
                    2 => ['activo' => '1', 'hora_inicio' => '08:00', 'hora_fin' => '14:00'],
                    3 => ['activo' => '1', 'hora_inicio' => '08:00', 'hora_fin' => '14:00'],
                    4 => ['activo' => '1', 'hora_inicio' => '10:00', 'hora_fin' => '18:00'],
                    5 => ['activo' => '1', 'hora_inicio' => '08:00', 'hora_fin' => '13:00'],
                ]),
            ])
            ->assertRedirect(route('disponibilidades.create', ['medico_id' => $medico->id]));

        $this->assertDatabaseHas('disponibilidades', [
            'medico_id' => $medico->id,
            'dia_semana' => 4,
            'hora_inicio' => '10:00',
            'hora_fin' => '18:00',
            'activo' => true,
        ]);
        $this->assertDatabaseMissing('disponibilidades', [
            'medico_id' => $medico->id,
            'dia_semana' => 6,
            'activo' => true,
        ]);
        $this->assertDatabaseCount('disponibilidades', 5);
    }

    public function test_weekly_disponibilidad_rejects_invalid_hours(): void
    {
        $admin = $this->userWithRole('admin');
        $medico = $this->createMedico('Doctor Invalido');

        $this->actingAs($admin)
            ->post(route('disponibilidades.store'), [
                'medico_id' => $medico->id,
                'dias' => $this->weeklyDias([
                    1 => ['activo' => '1', 'hora_inicio' => '14:00', 'hora_fin' => '08:00'],
                ]),
            ])
            ->assertSessionHasErrors('dias.1.hora_fin');

        $this->assertDatabaseCount('disponibilidades', 0);
    }

    public function test_weekly_disponibilidad_does_not_leave_future_citas_outside_schedule(): void
    {
        $admin = $this->userWithRole('admin');
        $medico = $this->createMedico('Doctor Con Cita');
        $paciente = $this->createPaciente('Paciente Futuro');
        $servicio = $this->createServicio(60, $medico);
        $fecha = Carbon::now()->addWeek()->next(Carbon::MONDAY)->setTime(9, 0);
        $diaSemana = $fecha->dayOfWeekIso;

        Disponibilidad::create([
            'medico_id' => $medico->id,
            'dia_semana' => $diaSemana,
            'hora_inicio' => '08:00',
            'hora_fin' => '12:00',
            'activo' => true,
        ]);

        Cita::create([
            'medico_id' => $medico->id,
            'paciente_id' => $paciente->id,
            'servicio_id' => $servicio->id,
            'fecha_hora' => $fecha->format('Y-m-d H:i:s'),
            'motivo' => 'Consulta futura',
            'estado' => Cita::ESTADO_AGENDADA,
        ]);

        $this->actingAs($admin)
            ->post(route('disponibilidades.store'), [
                'medico_id' => $medico->id,
                'dias' => $this->weeklyDias([
                    $diaSemana => ['activo' => '1', 'hora_inicio' => '10:00', 'hora_fin' => '12:00'],
                ]),
            ])
            ->assertSessionHasErrors("dias.$diaSemana.hora_inicio");

        $this->assertDatabaseHas('disponibilidades', [
            'medico_id' => $medico->id,
            'dia_semana' => $diaSemana,
            'hora_inicio' => '08:00',
            'hora_fin' => '12:00',
            'activo' => true,
        ]);
    }

    public function test_medico_can_only_manage_own_weekly_disponibilidad(): void
    {
        $medicoUser = $this->userWithRole('medico');
        $medico = $this->createMedico('Doctor Propio', $medicoUser);
        $otroMedico = $this->createMedico('Doctor Ajeno');

        $this->actingAs($medicoUser)
            ->get(route('disponibilidades.create'))
            ->assertOk()
            ->assertSee('Doctor Propio')
            ->assertDontSee('Doctor Ajeno');

        $this->actingAs($medicoUser)
            ->post(route('disponibilidades.store'), [
                'medico_id' => $otroMedico->id,
                'dias' => $this->weeklyDias([
                    1 => ['activo' => '1', 'hora_inicio' => '08:00', 'hora_fin' => '12:00'],
                ]),
            ])
            ->assertForbidden();

        $this->actingAs($medicoUser)
            ->post(route('disponibilidades.store'), [
                'medico_id' => $medico->id,
                'dias' => $this->weeklyDias([
                    1 => ['activo' => '1', 'hora_inicio' => '08:00', 'hora_fin' => '12:00'],
                ]),
            ])
            ->assertRedirect(route('disponibilidades.create', ['medico_id' => $medico->id]));

        $this->assertDatabaseHas('disponibilidades', [
            'medico_id' => $medico->id,
            'dia_semana' => 1,
            'activo' => true,
        ]);
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role, 'web');

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function createCitaForMedico(string $pacienteNombre, string $medicoNombre, ?User $medicoUser = null): Cita
    {
        $medico = $this->createMedico($medicoNombre, $medicoUser);
        $paciente = $this->createPaciente($pacienteNombre);

        return Cita::create([
            'medico_id' => $medico->id,
            'paciente_id' => $paciente->id,
            'servicio_id' => $this->createServicio(30, $medico)->id,
            'fecha_hora' => '2026-06-01 09:00:00',
            'motivo' => 'Consulta',
            'estado' => Cita::ESTADO_AGENDADA,
        ]);
    }

    private function createMedico(string $nombre, ?User $user = null): Medico
    {
        return Medico::create([
            'nombre' => $nombre,
            'apellido' => 'Prueba',
            'email' => fake()->unique()->safeEmail(),
            'especialidad' => 'General',
            'telefono' => '555-0000',
            'user_id' => $user?->id,
        ]);
    }

    private function createPaciente(string $nombre, ?User $user = null): Paciente
    {
        return Paciente::create([
            'nombre' => $nombre,
            'apellido' => 'Prueba',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 'N/A',
            'email' => fake()->unique()->safeEmail(),
            'telefono' => '555-1111',
            'user_id' => $user?->id,
        ]);
    }

    private function createCitaForPaciente(Paciente $paciente, string $motivo): Cita
    {
        $medico = $this->createMedico('Doctor Paciente');

        return Cita::create([
            'medico_id' => $medico->id,
            'paciente_id' => $paciente->id,
            'servicio_id' => $this->createServicio(30, $medico)->id,
            'fecha_hora' => '2026-06-01 09:00:00',
            'motivo' => $motivo,
            'estado' => Cita::ESTADO_AGENDADA,
        ]);
    }

    private function createServicio(int $duracion = 30, ?Medico $medico = null): Servicio
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

    private function weeklyDias(array $overrides = []): array
    {
        $dias = [];

        foreach (range(1, 7) as $dia) {
            $dias[$dia] = [
                'activo' => '0',
                'hora_inicio' => '08:00',
                'hora_fin' => '14:00',
            ];
        }

        foreach ($overrides as $dia => $config) {
            $dias[$dia] = array_merge($dias[$dia], $config);
        }

        return $dias;
    }
}
