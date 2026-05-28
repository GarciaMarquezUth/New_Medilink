<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div x-data="{ open: false }">
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-72 border-r border-slate-200/80 bg-white/95 px-5 py-6 shadow-xl shadow-slate-200/50 backdrop-blur lg:flex lg:flex-col">
        <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3 rounded-3xl bg-gradient-to-br from-violet-600 to-purple-700 p-4 text-white shadow-lg shadow-violet-600/25">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-xl font-black ring-1 ring-white/25">M</span>
            <span>
                <span class="block text-lg font-extrabold leading-5">MediLink</span>
                <span class="text-xs font-semibold text-violet-100">Sistema clínico</span>
            </span>
        </a>

        <nav class="mt-8 flex flex-1 flex-col gap-2">
            <a href="{{ route('dashboard') }}" wire:navigate class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/20' : 'text-slate-600 hover:bg-violet-50 hover:text-violet-700' }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10"/></svg>
                Dashboard
            </a>

            @hasanyrole(['admin', 'recepcionista'])
                <a href="{{ route('medicos.index') }}" wire:navigate class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('medicos.*') ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/20' : 'text-slate-600 hover:bg-violet-50 hover:text-violet-700' }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 0116 0"/><path stroke-linecap="round" stroke-linejoin="round" d="M18 8h4m-2-2v4"/></svg>
                    Médicos
                </a>

                <a href="{{ route('pacientes.index') }}" wire:navigate class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('pacientes.*') ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/20' : 'text-slate-600 hover:bg-violet-50 hover:text-violet-700' }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20a5 5 0 00-10 0M12 12a4 4 0 100-8 4 4 0 000 8z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 11v6m3-3h-6"/></svg>
                    Pacientes
                </a>

                <a href="{{ route('servicios.index') }}" wire:navigate class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('servicios.*') ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/20' : 'text-slate-600 hover:bg-violet-50 hover:text-violet-700' }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"/></svg>
                    Servicios
                </a>

                <a href="{{ route('disponibilidades.index') }}" wire:navigate class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('disponibilidades.*') ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/20' : 'text-slate-600 hover:bg-violet-50 hover:text-violet-700' }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M7 15h3m4 0h3M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/></svg>
                    Disponibilidad
                </a>
            @endhasanyrole

            @role('paciente')
                <a href="{{ route('portal-citas.index') }}" class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition text-slate-600 hover:bg-violet-50 hover:text-violet-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/></svg>
                    Agendar cita
                </a>
            @endrole

            <a href="{{ route('citas.index') }}" wire:navigate class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('citas.*') ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/20' : 'text-slate-600 hover:bg-violet-50 hover:text-violet-700' }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/></svg>
                @role('paciente') Mis citas @else Citas @endrole
            </a>
        </nav>

        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-sm font-extrabold text-violet-700">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-slate-900">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs font-medium text-slate-500">{{ auth()->user()->email }}</p>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-2">
                <a href="{{ route('profile') }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-center text-xs font-bold text-slate-600 transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700">Perfil</a>
                <button wire:click="logout" class="rounded-2xl bg-slate-900 px-3 py-2 text-xs font-bold text-white transition hover:bg-violet-700">Salir</button>
            </div>
        </div>
    </aside>

    <div class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur lg:hidden">
        <div class="flex h-16 items-center justify-between px-4">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-600 text-lg font-black text-white shadow-lg shadow-violet-600/20">M</span>
                <span class="text-base font-extrabold text-slate-900">MediLink</span>
            </a>
            <button type="button" @click="open = ! open" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-violet-50 hover:text-violet-700" aria-label="Abrir navegación">
                <svg x-show="! open" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="open" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div x-show="open" x-transition class="border-t border-slate-200 bg-white px-4 py-4">
            <div class="space-y-2">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>Dashboard</x-responsive-nav-link>

                @hasanyrole(['admin', 'recepcionista'])
                    <x-responsive-nav-link :href="route('medicos.index')" :active="request()->routeIs('medicos.*')" wire:navigate>Médicos</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('pacientes.index')" :active="request()->routeIs('pacientes.*')" wire:navigate>Pacientes</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('servicios.index')" :active="request()->routeIs('servicios.*')" wire:navigate>Servicios</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('disponibilidades.index')" :active="request()->routeIs('disponibilidades.*')" wire:navigate>Disponibilidad</x-responsive-nav-link>
                @endhasanyrole

                @role('paciente')
                    <x-responsive-nav-link :href="route('portal-citas.index')" :active="request()->routeIs('portal-citas.*')">Agendar cita</x-responsive-nav-link>
                @endrole

                <x-responsive-nav-link :href="route('citas.index')" :active="request()->routeIs('citas.*')" wire:navigate>@role('paciente') Mis citas @else Citas @endrole</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('profile')" :active="request()->routeIs('profile')" wire:navigate>Perfil</x-responsive-nav-link>
            </div>

            <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                <p class="text-sm font-bold text-slate-900">{{ auth()->user()->name }}</p>
                <p class="text-xs font-medium text-slate-500">{{ auth()->user()->email }}</p>
                <button wire:click="logout" class="mt-3 w-full rounded-2xl bg-violet-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:bg-violet-700">Cerrar sesión</button>
            </div>
        </div>
    </div>
</div>
