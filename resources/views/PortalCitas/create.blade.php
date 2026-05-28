<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Agenda tu cita médica | {{ config('app.name', 'MediLink') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/medilink_logo.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            ::selection {
                background: #ede9fe;
                color: #4c1d95;
            }
        </style>
    </head>

    <body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
        @php
            $selectedMedico = $medicos->firstWhere('id', (int) $selectedMedicoId);
            $selectedServicio = $servicios->firstWhere('id', (int) $selectedServicioId);
            $selectionComplete = $selectedMedicoId && $selectedServicioId && $selectedFecha;
        @endphp

        <main class="min-h-screen bg-slate-100">
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex max-w-[1100px] items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-700 text-sm font-black text-white shadow-sm shadow-violet-700/20">M</span>
                        <span class="text-lg font-black tracking-tight text-slate-950">MediLink</span>
                    </a>

                    <a href="{{ route('login') }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700 focus:outline-none focus:ring-4 focus:ring-violet-100">
                        Acceso interno
                    </a>
                </div>
            </header>

            <div class="mx-auto max-w-[1100px] px-4 py-5 sm:px-6 lg:px-8">
                <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-violet-800 via-violet-700 to-purple-700 px-5 py-7 text-white shadow-xl shadow-violet-950/15 sm:px-8 sm:py-8">
                    <div class="max-w-3xl">
                        <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-violet-100">Portal del paciente</p>
                        <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl">Agenda tu cita médica</h1>
                        <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-violet-50 sm:text-base">
                            Selecciona médico, servicio y horario disponible. Te mostraremos únicamente espacios válidos según la agenda de la clínica.
                        </p>
                    </div>
                </section>

                @if (session('success'))
                    <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800 shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="mt-5 rounded-2xl border border-violet-200 bg-violet-50 px-5 py-4 text-sm font-bold text-violet-800 shadow-sm">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-800 shadow-sm">
                        <p class="font-black">Revisa la información ingresada.</p>
                        <p class="mt-1">Algunos datos no son válidos o el horario seleccionado ya no está disponible.</p>
                    </div>
                @endif

                <section class="mt-5 grid gap-4 md:grid-cols-3">
                    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-base font-black text-violet-700">1</span>
                            <div>
                                <h2 class="text-base font-black text-slate-950">Selecciona médico y servicio</h2>
                                <p class="mt-1 text-sm font-medium leading-5 text-slate-500">Elige el especialista y la atención que necesitas.</p>
                            </div>
                        </div>
                    </article>

                    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-base font-black text-violet-700">2</span>
                            <div>
                                <h2 class="text-base font-black text-slate-950">Elige fecha y horario</h2>
                                <p class="mt-1 text-sm font-medium leading-5 text-slate-500">Consulta espacios disponibles sin traslapes.</p>
                            </div>
                        </div>
                    </article>

                    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-base font-black text-violet-700">3</span>
                            <div>
                                <h2 class="text-base font-black text-slate-950">@auth Confirma tu cita @else Ingresa tus datos @endauth</h2>
                                <p class="mt-1 text-sm font-medium leading-5 text-slate-500">@auth Escribe el motivo y confirma la solicitud. @else Completa tu información y confirma la solicitud. @endauth</p>
                            </div>
                        </div>
                    </article>
                </section>

                <section class="mt-5 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5 flex flex-col gap-2 border-b border-slate-100 pb-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Disponibilidad</p>
                            <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Busca un horario disponible</h2>
                        </div>
                        <p class="inline-flex w-fit rounded-full bg-violet-50 px-3 py-1.5 text-xs font-bold text-violet-700">Horarios cada 15 minutos</p>
                    </div>

                    <form action="{{ route('portal-citas.index') }}" method="GET" class="grid gap-4 lg:grid-cols-[1fr_1fr_1fr_auto] lg:items-end">
                        <div>
                            <label for="medico_id" class="block text-sm font-extrabold text-slate-800">Médico</label>
                            <select id="medico_id" name="medico_id" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800 shadow-sm transition focus:border-violet-500 focus:ring-violet-500" required>
                                <option value="">Selecciona médico</option>
                                @foreach ($medicos as $medico)
                                    <option value="{{ $medico->id }}" {{ (int) $selectedMedicoId === $medico->id ? 'selected' : '' }}>
                                        {{ $medico->nombre }} {{ $medico->apellido }} @if ($medico->especialidad) - {{ $medico->especialidad }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('medico_id')
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="servicio_id" class="block text-sm font-extrabold text-slate-800">Servicio</label>
                            <select id="servicio_id" name="servicio_id" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800 shadow-sm transition focus:border-violet-500 focus:ring-violet-500" required>
                                <option value="">Selecciona servicio</option>
                                @foreach ($servicios as $servicio)
                                    <option value="{{ $servicio->id }}" {{ (int) $selectedServicioId === $servicio->id ? 'selected' : '' }}>
                                        {{ $servicio->nombre }} - {{ $servicio->duracion_minutos }} min @if ($servicio->precio !== null) - ${{ number_format((float) $servicio->precio, 2) }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('servicio_id')
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="fecha" class="block text-sm font-extrabold text-slate-800">Fecha</label>
                            <input id="fecha" type="date" name="fecha" min="{{ now()->toDateString() }}" value="{{ $selectedFecha }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800 shadow-sm transition focus:border-violet-500 focus:ring-violet-500" required>
                            @error('fecha')
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="inline-flex w-full select-none items-center justify-center rounded-2xl bg-violet-700 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-violet-700/20 transition hover:-translate-y-0.5 hover:bg-violet-800 focus:outline-none focus:ring-4 focus:ring-violet-200 lg:w-auto lg:whitespace-nowrap">
                            Ver horarios disponibles
                        </button>
                    </form>
                </section>

                @if ($selectionComplete)
                    <section class="mt-5 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                        <div class="mb-5 rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Resumen seleccionado</p>
                            <div class="mt-3 grid gap-3 text-sm font-bold text-slate-700 md:grid-cols-3">
                                <div class="rounded-2xl bg-white px-4 py-3 shadow-sm">
                                    <span class="block text-xs uppercase tracking-wide text-slate-400">Médico</span>
                                    {{ $selectedMedico?->nombre }} {{ $selectedMedico?->apellido }}
                                </div>
                                <div class="rounded-2xl bg-white px-4 py-3 shadow-sm">
                                    <span class="block text-xs uppercase tracking-wide text-slate-400">Servicio</span>
                                    {{ $selectedServicio?->nombre }} @if ($selectedServicio) - {{ $selectedServicio->duracion_minutos }} min @endif
                                </div>
                                <div class="rounded-2xl bg-white px-4 py-3 shadow-sm">
                                    <span class="block text-xs uppercase tracking-wide text-slate-400">Fecha</span>
                                    {{ $selectedFecha }}
                                </div>
                            </div>
                        </div>

                        @if (count($horarios) > 0)
                            <form action="{{ route('portal-citas.store') }}" method="POST" class="space-y-6">
                                @csrf
                                <input type="hidden" name="medico_id" value="{{ $selectedMedicoId }}">
                                <input type="hidden" name="servicio_id" value="{{ $selectedServicioId }}">
                                <input type="hidden" name="fecha" value="{{ $selectedFecha }}">

                                <div>
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                                        <div>
                                            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Horarios disponibles</p>
                                            <h2 class="mt-1 text-2xl font-black text-slate-950">Elige el horario de tu cita</h2>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-500">El horario seleccionado se resaltará en morado.</p>
                                    </div>

                                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                                        @foreach ($horarios as $slot)
                                            <label class="block select-none">
                                                <input type="radio" name="horario" value="{{ $slot['value'] }}" class="peer sr-only" {{ old('horario') === $slot['value'] ? 'checked' : '' }} required>
                                                <span class="flex min-h-16 cursor-pointer flex-col items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 text-center shadow-sm transition hover:border-violet-300 hover:bg-white peer-checked:border-violet-700 peer-checked:bg-violet-700 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-violet-700/20 peer-focus:ring-4 peer-focus:ring-violet-200">
                                                    <span class="text-base font-black leading-none">{{ $slot['label'] }}</span>
                                                    <span class="mt-1 text-[11px] font-bold opacity-75">Termina {{ $slot['ends_at'] }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>

                                    @error('horario')
                                        <p class="mt-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                                    <div class="mb-5">
                                        @guest
                                            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Datos del paciente</p>
                                            <h2 class="mt-1 text-2xl font-black text-slate-950">Completa tu solicitud</h2>
                                            <p class="mt-1 text-sm font-medium text-slate-500">Usaremos estos datos para identificarte y contactarte.</p>
                                        @else
                                            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Confirmación</p>
                                            <h2 class="mt-1 text-2xl font-black text-slate-950">Confirma tu solicitud</h2>
                                            <p class="mt-1 text-sm font-medium text-slate-500">Usaremos los datos de tu cuenta para registrar la cita.</p>
                                        @endguest
                                    </div>

                                    @guest
                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div>
                                                <label for="nombre" class="block text-sm font-extrabold text-slate-800">Nombre</label>
                                                <input id="nombre" type="text" name="nombre" value="{{ old('nombre') }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800 shadow-sm transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-violet-500" autocomplete="given-name" required>
                                                @error('nombre')
                                                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="apellido" class="block text-sm font-extrabold text-slate-800">Apellido</label>
                                                <input id="apellido" type="text" name="apellido" value="{{ old('apellido') }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800 shadow-sm transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-violet-500" autocomplete="family-name" required>
                                                @error('apellido')
                                                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="email" class="block text-sm font-extrabold text-slate-800">Email</label>
                                                <input id="email" type="email" name="email" value="{{ old('email') }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800 shadow-sm transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-violet-500" autocomplete="email" required>
                                                @error('email')
                                                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="telefono" class="block text-sm font-extrabold text-slate-800">Teléfono</label>
                                                <input id="telefono" type="tel" name="telefono" value="{{ old('telefono') }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800 shadow-sm transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-violet-500" autocomplete="tel" required>
                                                @error('telefono')
                                                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    @endguest

                                    <div class="@guest mt-4 @endguest">
                                        <label for="motivo" class="block text-sm font-extrabold text-slate-800">Motivo de consulta</label>
                                        <textarea id="motivo" name="motivo" rows="4" class="mt-2 block w-full resize-none rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800 shadow-sm transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-violet-500" required>{{ old('motivo') }}</textarea>
                                        @error('motivo')
                                            <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mt-5 rounded-2xl border border-violet-100 bg-violet-50 px-4 py-3 text-sm font-semibold text-violet-800">
                                        @guest
                                            Inicia sesión o regístrate para confirmar tu cita. Guardaremos temporalmente esta selección mientras accedes.
                                        @else
                                            Usaremos los datos de tu cuenta para registrar la cita. Solo confirma el motivo de consulta y envía la solicitud.
                                        @endguest
                                    </div>

                                    <button type="submit" class="mt-5 inline-flex w-full select-none items-center justify-center rounded-2xl bg-violet-700 px-6 py-3.5 text-base font-black text-white shadow-lg shadow-violet-700/20 transition hover:-translate-y-0.5 hover:bg-violet-800 focus:outline-none focus:ring-4 focus:ring-violet-200">
                                        @auth Confirmar cita @else Solicitar cita @endauth
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5 text-center">
                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-white text-lg font-black text-violet-700 shadow-sm">i</div>
                                <h2 class="mt-3 text-lg font-black text-violet-950">No hay horarios disponibles</h2>
                                <p class="mx-auto mt-1 max-w-xl text-sm font-semibold leading-6 text-violet-800">
                                    No encontramos espacios para esa combinación. Prueba con otra fecha, médico o servicio.
                                </p>
                            </div>
                        @endif
                    </section>
                @else
                    <section class="mt-5 rounded-2xl border border-violet-200 bg-violet-50 p-5 text-center shadow-sm">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-white text-lg font-black text-violet-700 shadow-sm">i</div>
                        <h2 class="mt-3 text-lg font-black text-violet-950">Completa la búsqueda</h2>
                        <p class="mx-auto mt-1 max-w-2xl text-sm font-semibold leading-6 text-violet-800">
                            Selecciona médico, servicio y fecha para ver los horarios disponibles y continuar con tu solicitud.
                        </p>
                    </section>
                @endif
            </div>
        </main>
    </body>
</html>
