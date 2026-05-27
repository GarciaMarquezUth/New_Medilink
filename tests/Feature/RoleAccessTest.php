<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\User;
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
            'estado' => 'atendida',
        ]);

        $this->actingAs($medicoUser)
            ->post(route('citas.no-presentada', $citaNoPresentada->id))
            ->assertRedirect(route('citas.index'));

        $this->assertDatabaseHas('citas', [
            'id' => $citaNoPresentada->id,
            'estado' => 'no_presentada',
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

    public function test_admin_and_recepcionista_can_create_update_and_cancel_citas(): void
    {
        $admin = $this->userWithRole('admin');
        $recepcionista = $this->userWithRole('recepcionista');
        $medico = $this->createMedico('Doctor Gestion');
        $paciente = $this->createPaciente('Paciente Gestion');

        $this->actingAs($admin)
            ->post(route('citas.store'), [
                'medico_id' => $medico->id,
                'paciente_id' => $paciente->id,
                'fecha_hora' => '2026-06-01T09:00',
                'motivo' => 'Consulta inicial',
            ])
            ->assertRedirect(route('citas.index'));

        $cita = Cita::firstOrFail();

        $this->actingAs($recepcionista)
            ->put(route('citas.update', $cita->id), [
                'medico_id' => $medico->id,
                'paciente_id' => $paciente->id,
                'fecha_hora' => '2026-06-02T10:30',
                'motivo' => 'Consulta reagendada',
                'estado' => 'cancelada',
            ])
            ->assertRedirect(route('citas.index'));

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'motivo' => 'Consulta reagendada',
            'estado' => 'cancelada',
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
            'fecha_hora' => '2026-06-01 09:00:00',
            'motivo' => 'Consulta',
            'estado' => 'pendiente',
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

    private function createPaciente(string $nombre): Paciente
    {
        return Paciente::create([
            'nombre' => $nombre,
            'apellido' => 'Prueba',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 'N/A',
            'email' => fake()->unique()->safeEmail(),
            'telefono' => '555-1111',
        ]);
    }
}
