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
}; 
?>

<nav x-data="{ open: false }" class="bg-purple-800 border-b border-purple-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-white" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-white hover:text-purple-200" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('medicos.index')" :active="request()->routeIs('medicos.*')" class="text-white hover:text-purple-200" wire:navigate>
                        {{ __('Médicos') }}
                    </x-nav-link>
                    <x-nav-link :href="route('servicios.index')" :active="request()->routeIs('servicios.*')" class="text-white hover:text-purple-200" wire:navigate>
                        {{ __('Servicios') }}
                    </x-nav-link>
                    <x-nav-link :href="route('pacientes.index')" :active="request()->routeIs('pacientes.*')" class="text-white hover:text-purple-200" wire:navigate>
                        {{ __('Pacientes') }}
                    </x-nav-link>
                    <x-nav-link :href="route('citas.index')" :active="request()->routeIs('citas.*')" class="text-white hover:text-purple-200" wire:navigate>
                        {{ __('Citas') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-purple-900 hover:text-purple-200 transition">
                            <div x-text="'{{ auth()->user()->name }}'"></div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')">{{ __('Profile') }}</x-dropdown-link>
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>{{ __('Log Out') }}</x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>