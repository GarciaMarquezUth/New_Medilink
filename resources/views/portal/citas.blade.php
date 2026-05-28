<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Solicitar cita | {{ config('app.name', 'MediLink') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <main class="min-h-screen bg-slate-50">
            <section class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-6 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <div>
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-3 text-slate-950">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-600 text-lg font-black text-white shadow-lg shadow-violet-600/20">M</span>
                            <span>
                                <span class="block text-lg font-extrabold leading-5">MediLink</span>
                                <span class="text-xs font-semibold text-slate-500">Portal del paciente</span>
                            </span>
                        </a>
                    </div>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Acceso interno
                    </a>
                </div>
            </section>

            <livewire:portal-appointment-scheduler />
        </main>

        @livewireScripts
    </body>
</html>
