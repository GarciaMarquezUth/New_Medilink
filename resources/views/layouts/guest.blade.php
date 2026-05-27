<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MediLink') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="relative min-h-screen overflow-hidden bg-slate-50">
            <div class="absolute inset-x-0 top-0 -z-10 h-72 bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-500 opacity-95"></div>
            <div class="absolute left-1/2 top-24 -z-10 h-72 w-72 -translate-x-1/2 rounded-full bg-white/20 blur-3xl"></div>

            <div class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
                <div class="w-full max-w-md">
                    <div class="mb-8 text-center text-white">
                        <a href="/" wire:navigate class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 shadow-lg ring-1 ring-white/30 backdrop-blur">
                            <span class="text-2xl font-black tracking-tight">M</span>
                        </a>
                        <h1 class="mt-5 text-3xl font-extrabold tracking-tight">MediLink</h1>
                        <p class="mt-2 text-sm font-medium text-violet-100">Gestión clínica moderna y segura</p>
                    </div>

                    <div class="rounded-3xl border border-white/70 bg-white/95 p-6 shadow-2xl shadow-violet-950/15 backdrop-blur sm:p-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
