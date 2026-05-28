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

            <section class="mx-auto grid max-w-6xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[0.8fr_1.2fr] lg:px-8">
                <aside class="space-y-5">
                    <div class="rounded-3xl bg-violet-600 p-6 text-white shadow-2xl shadow-violet-900/20">
                        <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-100">Citas en linea</p>
                        <h1 class="mt-4 text-3xl font-extrabold tracking-tight">Solicita tu cita</h1>
                        <p class="mt-3 text-sm font-medium leading-6 text-violet-100">
                            Elige medico, servicio y fecha para ver los horarios disponibles antes de enviar tu solicitud.
                        </p>
                    </div>

                    <form method="GET" action="{{ route('portal.citas.index') }}" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60">
                        <h2 class="text-base font-extrabold text-slate-950">Buscar horarios</h2>
                        <div class="mt-5 space-y-4">
                            <div>
                                <x-input-label for="medico_id" value="Medico" />
                                <select id="medico_id" name="medico_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>
                                    <option value="">Selecciona un medico</option>
                                    @foreach($medicos as $medico)
                                        <option value="{{ $medico->id }}" {{ (int) request('medico_id') === $medico->id ? 'selected' : '' }}>
                                            {{ $medico->nombre }} {{ $medico->apellido }} · {{ $medico->especialidad }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('medico_id')" />
                            </div>

                            <div>
                                <x-input-label for="servicio_id" value="Servicio" />
                                <select id="servicio_id" name="servicio_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>
                                    <option value="">Selecciona un servicio</option>
                                    @foreach($servicios as $servicio)
                                        <option value="{{ $servicio->id }}" {{ (int) request('servicio_id') === $servicio->id ? 'selected' : '' }}>
                                            {{ $servicio->nombre }} · {{ $servicio->duracion_minutos }} min
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('servicio_id')" />
                            </div>

                            <div>
                                <x-input-label for="fecha" value="Fecha" />
                                <x-text-input id="fecha" type="date" name="fecha" value="{{ request('fecha', now()->toDateString()) }}" class="mt-2" required />
                                <x-input-error :messages="$errors->get('fecha')" />
                            </div>
                        </div>

                        <button type="submit" class="mt-5 w-full rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">
                            Ver horarios disponibles
                        </button>
                    </form>
                </aside>

                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60 sm:p-6">
                    @if(session('success'))
                        <div class="mb-5 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.18em] text-violet-600">Disponibilidad</p>
                        <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Elige un horario</h2>
                    </div>

                    @if(request()->filled(['medico_id', 'servicio_id', 'fecha']))
                        @if(count($slots) > 0)
                            <form method="POST" action="{{ route('portal.citas.store') }}" class="mt-6 space-y-6">
                                @csrf
                                <input type="hidden" name="medico_id" value="{{ request('medico_id') }}">
                                <input type="hidden" name="servicio_id" value="{{ request('servicio_id') }}">

                                <div>
                                    <x-input-label value="Horarios disponibles" />
                                    <div class="mt-3 grid gap-2 sm:grid-cols-3">
                                        @foreach($slots as $slot)
                                            <label class="flex cursor-pointer items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-violet-200 hover:bg-violet-50 has-[:checked]:border-violet-600 has-[:checked]:bg-violet-600 has-[:checked]:text-white">
                                                <input type="radio" name="fecha_hora" value="{{ $slot['value'] }}" class="sr-only" {{ old('fecha_hora') === $slot['value'] ? 'checked' : '' }} required>
                                                {{ $slot['label'] }} - {{ $slot['ends_at'] }}
                                            </label>
                                        @endforeach
                                    </div>
                                    <x-input-error :messages="$errors->get('fecha_hora')" />
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <x-input-label for="nombre" value="Nombre" />
                                        <x-text-input id="nombre" name="nombre" value="{{ old('nombre') }}" class="mt-2" required />
                                        <x-input-error :messages="$errors->get('nombre')" />
                                    </div>
                                    <div>
                                        <x-input-label for="apellido" value="Apellido" />
                                        <x-text-input id="apellido" name="apellido" value="{{ old('apellido') }}" class="mt-2" required />
                                        <x-input-error :messages="$errors->get('apellido')" />
                                    </div>
                                    <div>
                                        <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
                                        <x-text-input id="fecha_nacimiento" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" class="mt-2" required />
                                        <x-input-error :messages="$errors->get('fecha_nacimiento')" />
                                    </div>
                                    <div>
                                        <x-input-label for="genero" value="Genero" />
                                        <select id="genero" name="genero" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>
                                            <option value="">Selecciona una opcion</option>
                                            @foreach(['Femenino', 'Masculino', 'No especificado'] as $genero)
                                                <option value="{{ $genero }}" {{ old('genero') === $genero ? 'selected' : '' }}>{{ $genero }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('genero')" />
                                    </div>
                                    <div>
                                        <x-input-label for="email" value="Correo electronico" />
                                        <x-text-input id="email" type="email" name="email" value="{{ old('email') }}" class="mt-2" required />
                                        <x-input-error :messages="$errors->get('email')" />
                                    </div>
                                    <div>
                                        <x-input-label for="telefono" value="Telefono" />
                                        <x-text-input id="telefono" name="telefono" value="{{ old('telefono') }}" class="mt-2" required />
                                        <x-input-error :messages="$errors->get('telefono')" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="motivo" value="Motivo de la cita" />
                                    <textarea id="motivo" name="motivo" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>{{ old('motivo') }}</textarea>
                                    <x-input-error :messages="$errors->get('motivo')" />
                                </div>

                                <button type="submit" class="w-full rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-slate-950/15 transition hover:-translate-y-0.5 hover:bg-violet-700">
                                    Solicitar cita
                                </button>
                            </form>
                        @else
                            <div class="mt-6 rounded-3xl border border-amber-100 bg-amber-50 px-5 py-8 text-center">
                                <p class="text-sm font-bold text-amber-800">No hay horarios disponibles para esa combinacion.</p>
                            </div>
                        @endif
                    @else
                        <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 px-5 py-10 text-center">
                            <p class="text-sm font-semibold text-slate-500">Selecciona medico, servicio y fecha para consultar horarios.</p>
                        </div>
                    @endif
                </section>
            </section>
        </main>
    </body>
</html>
