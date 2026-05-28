<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 text-center">
        <span class="inline-flex rounded-full bg-violet-50 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-violet-700">Acceso seguro</span>
        <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">Bienvenido de nuevo</h2>
        <p class="mt-2 text-sm font-medium text-slate-500">Inicia sesión para gestionar la agenda clínica.</p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    @if (session('portal_cita_pendiente'))
        <div class="mb-5 rounded-2xl border border-violet-200 bg-violet-50 px-4 py-3 text-sm font-bold text-violet-800">
            Inicia sesión o regístrate para confirmar tu cita.
        </div>
    @endif

    <form wire:submit="login" class="space-y-5">
        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <div class="relative mt-2">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16v12H4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 7l8 6 8-6"/></svg>
                </span>
                <x-text-input wire:model="form.email" id="email" class="pl-12" type="email" name="email" required autofocus autocomplete="username" placeholder="nombre@clinica.com" />
            </div>
            <x-input-error :messages="$errors->get('form.email')" />
        </div>

        <div>
            <x-input-label for="password" value="Contraseña" />
            <div class="relative mt-2">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11V8a5 5 0 0110 0v3"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 11h12v9H6z"/></svg>
                </span>
                <x-text-input wire:model="form.password" id="password" class="pl-12" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            </div>
            <x-input-error :messages="$errors->get('form.password')" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-slate-300 text-violet-600 shadow-sm focus:ring-violet-500" name="remember">
                Recordarme
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-bold text-violet-700 transition hover:text-violet-900" href="{{ route('password.request') }}" wire:navigate>
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        <x-primary-button class="w-full">
            Entrar al panel
        </x-primary-button>

        <p class="text-center text-sm font-medium text-slate-500">
            ¿No tienes cuenta?
            <a href="{{ route('register') }}" class="font-bold text-violet-700 hover:text-violet-900" wire:navigate>Crear cuenta</a>
        </p>
    </form>
</div>
