<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Título de la aplicación --}}
        <title>{{ config('app.name', 'MediLink') }}</title>

        {{-- Favicon / Icono de la pestaña --}}
        <link rel="icon" type="image/png" href="{{ asset('images/medilink_logo.png') }}">

        {{-- Fuentes --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        {{-- Archivos CSS y JS --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-sans antialiased text-slate-900">
        <div class="min-h-screen bg-slate-50">

            {{-- Navegación lateral --}}
            <livewire:layout.navigation />

            <div class="lg:pl-72">

                {{-- Header dinámico --}}
                @if (isset($header))
                    <header class="border-b border-slate-200/70 bg-white/80 backdrop-blur-xl">
                        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                {{-- Contenido principal --}}
                <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    {{ $slot }}
                </main>

            </div>
        </div>
    </body>
</html>