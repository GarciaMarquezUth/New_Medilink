<?php

use App\Models\User;
use App\Services\PatientProfileService;
use App\Services\PendingAppointmentService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $pendingAppointment = session(PendingAppointmentService::SESSION_KEY) ?: session(PendingAppointmentService::LEGACY_SESSION_KEY);

        if (! $pendingAppointment) {
            return;
        }

        $pendingName = trim(($pendingAppointment['nombre'] ?? '').' '.($pendingAppointment['apellido'] ?? ''));

        if ($pendingName !== '' && $this->name === '') {
            $this->name = $pendingName;
        }

        if (! empty($pendingAppointment['email']) && $this->email === '') {
            $this->email = $pendingAppointment['email'];
        }
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Role::findOrCreate('paciente', 'web');
        $user->assignRole('paciente');

        Auth::login($user);

        Session::regenerate();

        $pendingAppointments = app(PendingAppointmentService::class);
        $patientProfiles = app(PatientProfileService::class);

        if ($patientProfiles->requiresCompletion($user)) {
            $patientProfiles->ensurePatientFor($user, $pendingAppointments->pending() ?? []);
            session()->flash('status', PatientProfileService::INCOMPLETE_MESSAGE);

            $this->redirect(route('pacientes.profile', absolute: false), navigate: true);

            return;
        }

        if ($pendingAppointments->hasPending()) {
            $payload = $pendingAppointments->pending();

            try {
                $pendingAppointments->confirmPendingFor($user);
                session()->forget('url.intended');
                session()->flash('success', PendingAppointmentService::CONFIRMED_MESSAGE);

                $this->redirect(route('dashboard', absolute: false), navigate: true);

                return;
            } catch (ValidationException) {
                $pendingAppointments->forgetPending();
                session()->forget('url.intended');
                session()->flash('error', PendingAppointmentService::UNAVAILABLE_MESSAGE);
                session()->flash('_old_input', $payload ?? []);

                $this->redirect(route('portal-citas.index', absolute: false), navigate: true);

                return;
            }
        }

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 text-center">
        <span class="inline-flex rounded-full bg-violet-50 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-violet-700">Nueva cuenta</span>
        <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">Crea tu acceso</h2>
        <p class="mt-2 text-sm font-medium text-slate-500">Regístrate para consultar y gestionar información clínica.</p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    @if (session(PendingAppointmentService::SESSION_KEY) || session(PendingAppointmentService::LEGACY_SESSION_KEY))
        <div class="mb-5 rounded-2xl border border-violet-200 bg-violet-50 px-4 py-3 text-sm font-bold text-violet-800">
            {{ PendingAppointmentService::LOGIN_NOTICE }}
        </div>
    @endif

    <form wire:submit="register" class="space-y-5">
        <div>
            <x-input-label for="name" value="Nombre completo" />
            <x-text-input wire:model="name" id="name" class="mt-2" type="text" name="name" required autofocus autocomplete="name" placeholder="Tu nombre" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input wire:model="email" id="email" class="mt-2" type="email" name="email" required autocomplete="username" placeholder="nombre@correo.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" value="Contraseña" />
            <x-text-input wire:model="password" id="password" class="mt-2" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirmar contraseña" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="mt-2" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="w-full">
            Crear cuenta
        </x-primary-button>

        <p class="text-center text-sm font-medium text-slate-500">
            ¿Ya tienes cuenta?
            <a class="font-bold text-violet-700 hover:text-violet-900" href="{{ route('login') }}" wire:navigate>Inicia sesión</a>
        </p>
    </form>
</div>
