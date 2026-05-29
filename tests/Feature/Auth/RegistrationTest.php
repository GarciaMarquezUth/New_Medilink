<?php

namespace Tests\Feature\Auth;

use App\Models\Paciente;
use App\Services\PatientProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_can_register(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(route('pacientes.profile', absolute: false));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('pacientes', [
            'nombre' => 'Test',
            'apellido' => 'User',
            'email' => 'test@example.com',
            'telefono' => '',
        ]);
        $this->assertSame(PatientProfileService::INCOMPLETE_MESSAGE, session('status'));
    }

    public function test_patient_can_complete_medical_profile(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Profile User')
            ->set('email', 'profile@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $user = auth()->user();

        $this->put(route('pacientes.profile.update'), [
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 'Otro',
            'telefono' => '555-4444',
            'direccion' => 'Calle perfil 123',
            'tipo_sangre' => 'O+',
            'alergias' => 'Ninguna conocida',
            'contacto_emergencia' => 'Contacto Perfil',
            'telefono_emergencia' => '555-9999',
        ])->assertRedirect(route('dashboard'));

        $paciente = Paciente::where('user_id', $user->id)->firstOrFail();

        $this->assertTrue($paciente->isProfileComplete());
        $this->assertDatabaseHas('pacientes', [
            'id' => $paciente->id,
            'telefono' => '555-4444',
            'direccion' => 'Calle perfil 123',
            'tipo_sangre' => 'O+',
            'alergias' => 'Ninguna conocida',
            'contacto_emergencia' => 'Contacto Perfil',
            'telefono_emergencia' => '555-9999',
        ]);
    }
}
