<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Disponibilidad;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_medico_dashboard_prioritizes_own_daily_agenda(): void
    {
        $medicoUser = $this->userWithRole('medico');
        $otroMedicoUser = $this->userWithRole('medico');
        $medico = $this->createMedico('Doctor Propio', $medicoUser);
        $otroMedico = $this->createMedico('Doctor Ajeno', $otroMedicoUser);
        $servicio = $this->createServicio();
        $this->createDisponibilidad($medico);

        $this->createCita($medico, $this->createPaciente('Paciente Visible'), $servicio, now()->setTime(10, 0));
        $this->createCita($otroMedico, $this->createPaciente('Paciente Oculto'), $servicio, now()->setTime(11, 0));

        $response = $this->actingAs($medicoUser)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('Agenda clinica de hoy')
            ->assertSee('Paciente Visible')
            ->assertSee('Disponibilidad configurada')
            ->assertDontSee('Paciente Oculto');
    }

    public function test_paciente_dashboard_shows_own_next_appointment_and_portal_action(): void
    {
        $pacienteUser = $this->userWithRole('paciente');
        $paciente = $this->createPaciente('Paciente Portal', $pacienteUser);
        $otroPaciente = $this->createPaciente('Paciente Otro');
        $medico = $this->createMedico('Doctora Agenda');
        $servicio = $this->createServicio('Consulta de seguimiento');

        $this->createCita($medico, $paciente, $servicio, now()->addDay()->setTime(9, 30), 'confirmada');
        $this->createCita($medico, $otroPaciente, $servicio, now()->addDay()->setTime(10, 30));

        $response = $this->actingAs($pacienteUser)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('Mi espacio de citas')
            ->assertSee('Consulta de seguimiento')
            ->assertSee('Agendar cita')
            ->assertDontSee('Paciente Otro');
    }

    public function test_admin_dashboard_keeps_operational_shortcuts(): void
    {
        $admin = $this->userWithRole('admin');
        $medico = $this->createMedico('Doctor Operacion');
        $paciente = $this->createPaciente('Paciente Operacion');
        $servicio = $this->createServicio();

        $this->createCita($medico, $paciente, $servicio, now()->setTime(8, 0));

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('Operacion de la clinica')
            ->assertSee('Nueva cita')
            ->assertSee('Gestionar pacientes')
            ->assertSee('Paciente Operacion');
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role, 'web');

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function createMedico(string $nombre, ?User $user = null): Medico
    {
        return Medico::create([
            'nombre' => $nombre,
            'apellido' => 'Prueba',
            'email' => fake()->unique()->safeEmail(),
            'especialidad' => 'Medicina general',
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

    private function createServicio(string $nombre = 'Consulta general'): Servicio
    {
        return Servicio::create([
            'nombre' => $nombre,
            'descripcion' => 'Servicio de prueba',
            'duracion_minutos' => 30,
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

    private function createCita(
        Medico $medico,
        Paciente $paciente,
        Servicio $servicio,
        mixed $fechaHora,
        string $estado = 'agendada'
    ): Cita {
        return Cita::create([
            'medico_id' => $medico->id,
            'paciente_id' => $paciente->id,
            'servicio_id' => $servicio->id,
            'fecha_hora' => $fechaHora,
            'motivo' => 'Consulta',
            'estado' => $estado,
        ]);
    }
}
