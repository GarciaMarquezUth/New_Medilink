<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Disponibilidad;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Servicio;
use App\Models\User;
use App\Services\PendingAppointmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PatientPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

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

    public function test_patient_portal_returns_services_dates_and_slots_as_json(): void
    {
        Carbon::setTestNow('2026-05-29 07:00:00');

        $medico = $this->createMedico('Doctor Json');
        $otroMedico = $this->createMedico('Doctor Json Otro');
        $servicio = $this->createServicio(30, $medico);
        $otroServicio = $this->createServicio(45, $otroMedico);
        $this->createDisponibilidad($medico, 1);

        $this->getJson(route('portal-citas.servicios', $medico))
            ->assertOk()
            ->assertJsonPath('servicios.0.id', $servicio->id)
            ->assertJsonMissing(['id' => $otroServicio->id]);

        $this->getJson(route('portal-citas.fechas', [$medico, $servicio]))
            ->assertOk()
            ->assertJsonPath('fechas.0.value', '2026-06-01')
            ->assertJsonPath('fechas.0.label', 'Lunes 1 junio');

        $this->getJson(route('portal-citas.horarios', [$medico, $servicio, '2026-06-01']))
            ->assertOk()
            ->assertJsonPath('horarios.0.value', '2026-06-01T08:00')
            ->assertJsonPath('horarios.0.ends_at', '08:30');
    }

    public function test_patient_portal_dates_only_include_days_where_service_fits(): void
    {
        Carbon::setTestNow('2026-05-29 07:00:00');

        $medico = $this->createMedico('Doctor Duracion');
        $servicio = $this->createServicio(90, $medico);

        Disponibilidad::create([
            'medico_id' => $medico->id,
            'dia_semana' => 1,
            'hora_inicio' => '08:00',
            'hora_fin' => '09:00',
            'activo' => true,
        ]);

        Disponibilidad::create([
            'medico_id' => $medico->id,
            'dia_semana' => 2,
            'hora_inicio' => '08:00',
            'hora_fin' => '10:00',
            'activo' => true,
        ]);

        $this->getJson(route('portal-citas.fechas', [$medico, $servicio]))
            ->assertOk()
            ->assertJsonMissing(['value' => '2026-06-01'])
            ->assertJsonPath('fechas.0.value', '2026-06-02')
            ->assertJsonPath('fechas.0.label', 'Martes 2 junio');
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

        $response = $this->post(route('portal-citas.store'), [
            'medico_id' => $medico->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-06-01',
            'horario' => '2026-06-01T09:00',
            'nombre' => 'Paciente',
            'apellido' => 'Portal',
            'email' => 'portal@example.com',
            'telefono' => '555-2222',
            'motivo' => 'Consulta desde portal',
        ]);

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas(PendingAppointmentService::SESSION_KEY)
            ->assertSessionMissing(PendingAppointmentService::LEGACY_SESSION_KEY)
            ->assertSessionHas('url.intended', route('dashboard'))
            ->assertSessionHas('status', PendingAppointmentService::LOGIN_NOTICE);

        $this->assertSame('portal@example.com', session(PendingAppointmentService::SESSION_KEY)['email']);

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
        ])->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', PendingAppointmentService::CONFIRMED_MESSAGE);

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
        ])->assertRedirect(route('dashboard'));

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

    public function test_pending_guest_appointment_is_confirmed_automatically_after_login(): void
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
            'horario' => '2026-06-01T09:30',
            'nombre' => 'Paciente',
            'apellido' => 'Confirmado',
            'email' => 'confirmado@example.com',
            'telefono' => '555-7777',
            'motivo' => 'Consulta pendiente',
        ])->assertRedirect(route('login'));

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertFalse(session()->has(PendingAppointmentService::SESSION_KEY));
        $this->assertSame(PendingAppointmentService::CONFIRMED_MESSAGE, session('success'));

        $this->assertDatabaseHas('pacientes', [
            'email' => 'confirmado@example.com',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('citas', [
            'medico_id' => $medico->id,
            'servicio_id' => $servicio->id,
            'fecha_hora' => '2026-06-01 09:30:00',
            'motivo' => 'Consulta pendiente',
            'estado' => Cita::ESTADO_AGENDADA,
        ]);
    }

    public function test_pending_guest_appointment_is_confirmed_automatically_after_registration(): void
    {
        $medico = $this->createMedico('Doctor Registro');
        $servicio = $this->createServicio(30, $medico);
        $this->createDisponibilidad($medico, 1);

        $this->post(route('portal-citas.store'), [
            'medico_id' => $medico->id,
            'servicio_id' => $servicio->id,
            'fecha' => '2026-06-01',
            'horario' => '2026-06-01T09:30',
            'nombre' => 'Paciente',
            'apellido' => 'Registrado',
            'email' => 'registrado@example.com',
            'telefono' => '555-1010',
            'motivo' => 'Consulta tras registro',
        ])->assertRedirect(route('login'));

        $component = Volt::test('pages.auth.register');

        $component
            ->assertSet('name', 'Paciente Registrado')
            ->assertSet('email', 'registrado@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertRedirect(route('pacientes.profile', absolute: false));

        $user = User::where('email', 'registrado@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasRole('paciente'));
        $this->assertTrue(session()->has(PendingAppointmentService::SESSION_KEY));

        $this->assertDatabaseHas('pacientes', [
            'nombre' => 'Paciente',
            'apellido' => 'Registrado',
            'email' => 'registrado@example.com',
            'telefono' => '555-1010',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->put(route('pacientes.profile.update'), $this->completeProfilePayload([
                'telefono' => '555-1010',
            ]))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', PendingAppointmentService::CONFIRMED_MESSAGE);

        $this->assertFalse(session()->has(PendingAppointmentService::SESSION_KEY));

        $this->assertDatabaseHas('citas', [
            'medico_id' => $medico->id,
            'servicio_id' => $servicio->id,
            'fecha_hora' => '2026-06-01 09:30:00',
            'motivo' => 'Consulta tras registro',
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

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('portal-citas.index', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertFalse(session()->has(PendingAppointmentService::SESSION_KEY));
        $this->assertSame(PendingAppointmentService::UNAVAILABLE_MESSAGE, session('error'));

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

    public function test_patient_internal_create_requires_authentication_and_patient_role(): void
    {
        $this->get(route('pacientes.citas.create'))
            ->assertRedirect(route('login'));

        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->get(route('pacientes.citas.create'))
            ->assertForbidden();
    }

    public function test_incomplete_patient_profile_shows_dashboard_alert_and_blocks_internal_booking(): void
    {
        $user = $this->userWithRole('paciente', [
            'name' => 'Paciente Incompleto',
            'email' => 'incompleto@example.com',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Completa tu perfil médico para poder agendar citas.')
            ->assertSee(route('pacientes.profile'), false);

        $this->actingAs($user)
            ->get(route('pacientes.citas.create'))
            ->assertRedirect(route('pacientes.profile'))
            ->assertSessionHas('status', 'Completa tu perfil médico para poder agendar citas.');
    }

    public function test_patient_internal_create_uses_dashboard_layout_and_hides_patient_fields(): void
    {
        Carbon::setTestNow('2026-05-29 07:00:00');

        $user = $this->userWithRole('paciente', [
            'name' => 'Paciente Interno',
            'email' => 'interno@example.com',
        ]);
        $paciente = $this->createPaciente('Paciente Interno', 'interno@example.com', '555-3030');
        $paciente->update(['user_id' => $user->id]);
        $medico = $this->createMedico('Doctor Interno');
        $servicio = $this->createServicio(30, $medico);
        $this->createDisponibilidad($medico, 1);

        $this->actingAs($user)
            ->get(route('pacientes.citas.create', [
                'medico_id' => $medico->id,
                'servicio_id' => $servicio->id,
                'fecha' => '2026-06-01',
            ]))
            ->assertOk()
            ->assertSee('Agendar nueva cita')
            ->assertSee('Paciente autenticado')
            ->assertSee('No necesitas escribir tus datos personales')
            ->assertSee('09:00')
            ->assertDontSee('name="nombre"', false)
            ->assertDontSee('name="apellido"', false)
            ->assertDontSee('name="email"', false)
            ->assertDontSee('name="telefono"', false);
    }

    public function test_patient_internal_endpoints_return_services_dates_and_slots(): void
    {
        Carbon::setTestNow('2026-05-29 07:00:00');

        $user = $this->userWithRole('paciente');
        $medico = $this->createMedico('Doctor Json Interno');
        $otroMedico = $this->createMedico('Doctor Json Externo');
        $servicio = $this->createServicio(30, $medico);
        $otroServicio = $this->createServicio(45, $otroMedico);
        $this->createDisponibilidad($medico, 1);

        $this->actingAs($user)
            ->getJson(route('pacientes.citas.servicios', $medico))
            ->assertOk()
            ->assertJsonPath('servicios.0.id', $servicio->id)
            ->assertJsonMissing(['id' => $otroServicio->id]);

        $this->actingAs($user)
            ->getJson(route('pacientes.citas.fechas', [$medico, $servicio]))
            ->assertOk()
            ->assertJsonPath('fechas.0.value', '2026-06-01')
            ->assertJsonPath('fechas.0.label', 'Lunes 1 junio');

        $this->actingAs($user)
            ->getJson(route('pacientes.citas.horarios', [$medico, $servicio, '2026-06-01']))
            ->assertOk()
            ->assertJsonPath('horarios.0.value', '2026-06-01T08:00')
            ->assertJsonPath('horarios.0.ends_at', '08:30');
    }

    public function test_patient_dashboard_links_to_internal_appointment_create(): void
    {
        $user = $this->userWithRole('paciente', [
            'name' => 'Paciente Link',
            'email' => 'paciente-link@example.com',
        ]);
        $paciente = $this->createPaciente('Paciente Link', 'paciente-link@example.com', '555-3030');
        $paciente->update(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('pacientes.citas.create'), false)
            ->assertDontSee(route('portal-citas.index'), false);
    }

    public function test_patient_internal_store_creates_appointment_for_authenticated_patient(): void
    {
        Carbon::setTestNow('2026-05-29 07:00:00');

        $user = $this->userWithRole('paciente', [
            'name' => 'Paciente Dashboard',
            'email' => 'dashboard@example.com',
        ]);
        $paciente = $this->createPaciente('Paciente Dashboard', 'dashboard@example.com', '555-1234');
        $paciente->update(['user_id' => $user->id]);
        $medico = $this->createMedico('Doctor Dashboard');
        $servicio = $this->createServicio(30, $medico);
        $this->createDisponibilidad($medico, 1);

        $this->actingAs($user)
            ->post(route('pacientes.citas.store'), [
                'medico_id' => $medico->id,
                'servicio_id' => $servicio->id,
                'fecha' => '2026-06-01',
                'horario' => '2026-06-01T09:00',
                'motivo' => 'Consulta interna',
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', PendingAppointmentService::CONFIRMED_MESSAGE);

        $this->assertDatabaseHas('citas', [
            'medico_id' => $medico->id,
            'paciente_id' => $paciente->id,
            'servicio_id' => $servicio->id,
            'fecha_hora' => '2026-06-01 09:00:00',
            'motivo' => 'Consulta interna',
            'estado' => Cita::ESTADO_AGENDADA,
        ]);
    }

    public function test_patient_internal_store_ignores_submitted_patient_identity_fields(): void
    {
        Carbon::setTestNow('2026-05-29 07:00:00');

        $user = $this->userWithRole('paciente', [
            'name' => 'Paciente Seguro',
            'email' => 'seguro@example.com',
        ]);
        $paciente = $this->createPaciente('Paciente Seguro', 'seguro@example.com', '555-9090');
        $paciente->update(['user_id' => $user->id]);
        $medico = $this->createMedico('Doctor Seguro');
        $servicio = $this->createServicio(30, $medico);
        $this->createDisponibilidad($medico, 1);

        $this->actingAs($user)
            ->post(route('pacientes.citas.store'), [
                'medico_id' => $medico->id,
                'servicio_id' => $servicio->id,
                'fecha' => '2026-06-01',
                'horario' => '2026-06-01T09:30',
                'nombre' => 'Nombre Inyectado',
                'apellido' => 'Apellido Inyectado',
                'email' => 'otro@example.com',
                'telefono' => '999-9999',
                'motivo' => 'Consulta segura',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('pacientes', [
            'id' => $paciente->id,
            'nombre' => 'Paciente Seguro',
            'apellido' => 'Prueba',
            'email' => 'seguro@example.com',
            'telefono' => '555-9090',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('pacientes', [
            'email' => 'otro@example.com',
        ]);
    }

    public function test_patient_internal_store_rejects_unavailable_slot(): void
    {
        Carbon::setTestNow('2026-05-29 07:00:00');

        $user = $this->userWithRole('paciente');
        $paciente = $this->createPaciente('Paciente Interno', $user->email, '555-4321');
        $paciente->update(['user_id' => $user->id]);
        $medico = $this->createMedico('Doctor Sin Horario');
        $servicio = $this->createServicio(60, $medico);
        $ocupante = $this->createPaciente('Paciente Ocupante', 'ocupante-interno@example.com', '555-0001');
        $this->createDisponibilidad($medico, 1);

        Cita::create([
            'medico_id' => $medico->id,
            'paciente_id' => $ocupante->id,
            'servicio_id' => $servicio->id,
            'fecha_hora' => '2026-06-01 09:00:00',
            'motivo' => 'Consulta ocupante',
            'estado' => Cita::ESTADO_AGENDADA,
        ]);

        $this->actingAs($user)
            ->post(route('pacientes.citas.store'), [
                'medico_id' => $medico->id,
                'servicio_id' => $servicio->id,
                'fecha' => '2026-06-01',
                'horario' => '2026-06-01T09:30',
                'motivo' => 'Consulta no disponible',
            ])
            ->assertSessionHasErrors('horario');

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
            'genero' => 'Otro',
            'email' => $email,
            'telefono' => $telefono,
            'direccion' => 'Calle de prueba 123',
            'tipo_sangre' => 'O+',
            'alergias' => 'Ninguna conocida',
        ]);
    }

    private function completeProfilePayload(array $overrides = []): array
    {
        return array_merge([
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 'Otro',
            'telefono' => '555-0000',
            'direccion' => 'Calle de prueba 123',
            'tipo_sangre' => 'O+',
            'alergias' => 'Ninguna conocida',
            'contacto_emergencia' => 'Contacto Prueba',
            'telefono_emergencia' => '555-9999',
        ], $overrides);
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

    private function userWithRole(string $role, array $attributes = []): User
    {
        Role::findOrCreate($role, 'web');

        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }
}
