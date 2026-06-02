<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
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

    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        @php
            $selectedMedico = $medicos->firstWhere('id', (int) $selectedMedicoId);
            $selectedServicio = $servicios->firstWhere('id', (int) $selectedServicioId);
            $selectionComplete = $selectedMedicoId && $selectedServicioId && $selectedFecha;
            $canChooseDate = $selectedMedicoId && $selectedServicioId;
            $selectedFechaLabel = collect($fechasDisponibles)->firstWhere('value', $selectedFecha)['label'] ?? $selectedFecha;
            $medicosDestacados = $selectedMedicoId ? $medicos->where('id', (int) $selectedMedicoId)->take(3) : $medicos->take(3);
            $weekdayShort = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom'];
            $serviceIcons = ['M12 6v6l4 2', 'M9 12h6m-3-3v6', 'M4 7h16M7 4v6', 'M12 21s7-4.35 7-10a7 7 0 1 0-14 0c0 5.65 7 10 7 10Z'];
            $reviews = [
                ['name' => 'María González', 'text' => 'Agendé en menos de dos minutos y recibí atención puntual. La experiencia se siente segura y profesional.'],
                ['name' => 'Carlos Ramírez', 'text' => 'Me gustó poder ver fechas reales disponibles sin llamar a recepción. Muy claro y rápido.'],
                ['name' => 'Ana Torres', 'text' => 'El recordatorio y la confirmación me ayudaron a organizar mi consulta sin filas ni esperas.'],
            ];
        @endphp

        <main id="inicio" class="min-h-screen overflow-x-hidden bg-[radial-gradient(circle_at_top_left,#f3e8ff_0,#f8fafc_34%,#f1f5f9_100%)]">
            <header class="sticky top-0 z-40 border-b border-white/70 bg-white/85 backdrop-blur-xl">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-4 py-4 sm:px-6 lg:px-8">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-3" aria-label="MediLink">
                        <span class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-2xl bg-violet-700 shadow-lg shadow-violet-700/20">
                            <img src="{{ asset('images/medilink_logo.png') }}" alt="MediLink" class="h-full w-full object-cover">
                        </span>
                        <span class="text-xl font-black tracking-tight text-slate-950">MediLink</span>
                    </a>

                    <nav class="hidden items-center gap-8 text-sm font-bold text-slate-600 lg:flex" aria-label="Navegación principal">
                        <a href="#inicio" class="transition hover:text-violet-700">Inicio</a>
                        <a href="#servicios" class="transition hover:text-violet-700">Servicios</a>
                        <a href="#especialistas" class="transition hover:text-violet-700">Especialistas</a>
                        <a href="#contacto" class="transition hover:text-violet-700">Contacto</a>
                    </nav>

                    <div class="flex items-center gap-3">
                        <a href="#agenda" class="hidden rounded-full bg-violet-50 px-4 py-2 text-sm font-black text-violet-700 transition hover:bg-violet-100 sm:inline-flex">Agendar</a>
                        <a href="{{ route('login') }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 shadow-sm transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700 focus:outline-none focus:ring-4 focus:ring-violet-100">
                            Iniciar sesión
                        </a>
                    </div>
                </div>
            </header>

            <section class="relative">
                <div class="absolute left-1/2 top-10 h-72 w-72 -translate-x-1/2 rounded-full bg-violet-300/20 blur-3xl"></div>
                <div class="absolute right-0 top-40 h-80 w-80 rounded-full bg-fuchsia-200/30 blur-3xl"></div>

                <div class="relative mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 sm:py-10 lg:grid-cols-[minmax(0,1fr)_520px] lg:items-start lg:gap-10 lg:px-8 lg:py-12 xl:gap-12">
                    <div class="flex flex-col justify-start lg:sticky lg:top-24 lg:self-start lg:pr-6">
                        <span class="inline-flex w-fit items-center gap-2 rounded-full border border-violet-200 bg-white/80 px-4 py-2 text-xs font-black uppercase tracking-[0.22em] text-violet-700 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            Portal del paciente
                        </span>

                        <h1 class="mt-6 max-w-3xl text-5xl font-black leading-[0.95] tracking-tight text-slate-950 sm:text-6xl lg:text-7xl">
                            Agenda tu cita médica
                        </h1>

                        <p class="mt-6 max-w-2xl text-lg font-medium leading-8 text-slate-600">
                            Elige especialista, servicio, fecha y horario en una experiencia digital moderna. MediLink valida disponibilidad real para que agendes con confianza y sin llamadas innecesarias.
                        </p>

                        <div class="mt-8 grid max-w-2xl gap-3 sm:grid-cols-2">
                            @foreach(['Fácil y rápido', 'Seguro', 'Sin filas', 'Recordatorios automáticos'] as $beneficio)
                                <div class="flex items-center gap-3 rounded-2xl border border-white bg-white/80 px-4 py-3 shadow-sm shadow-slate-200/70">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-violet-100 text-violet-700">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                                    </span>
                                    <span class="text-sm font-black text-slate-800">{{ $beneficio }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                            <a href="#agenda" class="inline-flex items-center justify-center rounded-2xl bg-violet-700 px-6 py-4 text-sm font-black text-white shadow-xl shadow-violet-700/25 transition hover:-translate-y-0.5 hover:bg-violet-800 focus:outline-none focus:ring-4 focus:ring-violet-200">
                                Agendar ahora
                            </a>
                            <a href="#especialistas" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-6 py-4 text-sm font-black text-slate-700 shadow-sm transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700 focus:outline-none focus:ring-4 focus:ring-violet-100">
                                Ver especialistas
                            </a>
                        </div>

                        <div class="mt-6 max-w-2xl rounded-3xl border border-violet-100 bg-white/75 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-violet-600">Ayuda rápida</p>
                            <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">Selecciona médico, servicio y una fecha disponible. El formulario de la derecha se actualizará sin mover esta sección.</p>
                        </div>
                    </div>

                    <aside id="agenda" class="rounded-[2rem] border border-white bg-white/90 p-4 shadow-2xl shadow-violet-950/10 backdrop-blur sm:p-5 lg:self-start">
                        <div class="rounded-[1.65rem] bg-slate-950 p-5 text-white shadow-xl shadow-slate-950/15">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.22em] text-violet-200">Agendador inteligente</p>
                                    <h2 class="mt-2 text-2xl font-black tracking-tight">Reserva en minutos</h2>
                                </div>
                                <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-black text-violet-100 ring-1 ring-white/10">Seguro</span>
                            </div>

                            <div class="mt-5 grid grid-cols-4 gap-2 text-center text-[11px] font-black uppercase tracking-wide text-slate-300">
                                @foreach(['1 Médico', '2 Servicio', '3 Fecha', '4 Horario'] as $paso)
                                    <div class="rounded-2xl bg-white/10 px-2 py-2 ring-1 ring-white/10">{{ $paso }}</div>
                                @endforeach
                            </div>
                        </div>

                        @if (session('success'))
                            <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800 shadow-sm">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('status'))
                            <div class="mt-4 rounded-2xl border border-violet-200 bg-violet-50 px-5 py-4 text-sm font-bold text-violet-800 shadow-sm">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-800 shadow-sm">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-800 shadow-sm">
                                <p class="font-black">Revisa la información ingresada.</p>
                                <p class="mt-1">Algunos datos no son válidos o el horario seleccionado ya no está disponible.</p>
                            </div>
                        @endif

                        <section class="mt-4 rounded-[1.65rem] border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="mb-5 flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.2em] text-violet-600">Disponibilidad</p>
                                    <h3 class="mt-1 text-xl font-black tracking-tight text-slate-950">Busca un horario disponible</h3>
                                </div>
                                <span class="rounded-full bg-violet-50 px-3 py-1.5 text-[11px] font-black text-violet-700">Duración real</span>
                            </div>

                            <form
                                id="portal-cita-picker"
                                action="{{ route('portal-citas.index') }}"
                                method="GET"
                                class="space-y-4"
                                data-servicios-url-template="{{ route('portal-citas.servicios', ['medico' => '__MEDICO__']) }}"
                                data-fechas-url-template="{{ route('portal-citas.fechas', ['medico' => '__MEDICO__', 'servicio' => '__SERVICIO__']) }}"
                                data-horarios-url-template="{{ route('portal-citas.horarios', ['medico' => '__MEDICO__', 'servicio' => '__SERVICIO__', 'fecha' => '__FECHA__']) }}"
                            >
                                <div>
                                    <label for="medico_id" class="block text-sm font-black text-slate-800">Paso 1: Médico</label>
                                    <select id="medico_id" name="medico_id" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-sm font-bold text-slate-800 shadow-sm transition focus:border-violet-500 focus:ring-violet-500" required>
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
                                    <label for="servicio_id" class="block text-sm font-black text-slate-800">Paso 2: Servicio</label>
                                    <select id="servicio_id" name="servicio_id" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-sm font-bold text-slate-800 shadow-sm transition focus:border-violet-500 focus:ring-violet-500" required {{ ! $selectedMedicoId || $servicios->isEmpty() ? 'disabled' : '' }}>
                                        <option value="">{{ $selectedMedicoId ? 'Selecciona servicio' : 'Selecciona primero un médico' }}</option>
                                        @foreach ($servicios as $servicio)
                                            <option value="{{ $servicio->id }}" {{ (int) $selectedServicioId === $servicio->id ? 'selected' : '' }}>
                                                {{ $servicio->nombre }} - {{ $servicio->duracion_minutos }} min @if ($servicio->precio !== null) - ${{ number_format((float) $servicio->precio, 2) }} @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <p id="sin-servicios-medico" class="{{ $selectedMedicoId && $servicios->isEmpty() ? '' : 'hidden' }} mt-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-800">Este médico no tiene servicios disponibles.</p>
                                    @error('servicio_id')
                                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </form>

                            <p id="portal-loading-error" class="mt-4 hidden rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700"></p>
                        </section>

                        <section id="fechas-section" class="{{ $canChooseDate ? '' : 'hidden' }} mt-4 rounded-[1.65rem] border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-end justify-between gap-3">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.2em] text-violet-600">Paso 3: Fecha</p>
                                    <h3 class="mt-1 text-xl font-black text-slate-950">Próximas fechas disponibles</h3>
                                </div>
                            </div>

                            <div id="fechas-grid" class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                @foreach ($fechasDisponibles as $fechaDisponible)
                                    @php
                                        $date = \Carbon\Carbon::parse($fechaDisponible['value']);
                                        $selectedDateCard = $selectedFecha === $fechaDisponible['value'];
                                    @endphp
                                    <button type="button" data-date-value="{{ $fechaDisponible['value'] }}" class="group flex min-h-24 flex-col justify-between rounded-3xl border px-4 py-3 text-left shadow-sm transition focus:outline-none focus:ring-4 focus:ring-violet-200 {{ $selectedDateCard ? 'border-violet-700 bg-violet-700 text-white shadow-lg shadow-violet-700/20' : 'border-slate-200 bg-white text-slate-800 hover:-translate-y-0.5 hover:border-violet-300 hover:shadow-md' }}">
                                        <span class="text-xs font-black uppercase tracking-[0.18em] {{ $selectedDateCard ? 'text-violet-100' : 'text-violet-600' }}">{{ $weekdayShort[$date->dayOfWeekIso] }}</span>
                                        <span class="text-3xl font-black leading-none">{{ $date->format('d') }}</span>
                                        <span class="text-[11px] font-extrabold {{ $selectedDateCard ? 'text-violet-100' : 'text-slate-500' }}">{{ $fechaDisponible['slots_count'] }} horarios disponibles</span>
                                    </button>
                                @endforeach
                            </div>

                            <div id="no-fechas-message" class="{{ $canChooseDate && count($fechasDisponibles) === 0 ? '' : 'hidden' }} mt-4 rounded-2xl border border-violet-200 bg-violet-50 p-5 text-center">
                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-white text-lg font-black text-violet-700 shadow-sm">i</div>
                                <h2 class="mt-3 text-lg font-black text-violet-950">No hay fechas disponibles para este médico y servicio.</h2>
                            </div>

                            @error('fecha')
                                <p class="mt-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ $message }}</p>
                            @enderror
                        </section>

                        <section id="booking-section" class="{{ $selectionComplete ? '' : 'hidden' }} mt-4 rounded-[1.65rem] border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-violet-600">Resumen</p>
                                <div class="mt-3 grid gap-2 text-sm font-bold text-slate-700">
                                    <div class="rounded-2xl bg-white px-4 py-3 shadow-sm">
                                        <span class="block text-xs uppercase tracking-wide text-slate-400">Médico</span>
                                        <span id="summary-medico">{{ $selectedMedico?->nombre }} {{ $selectedMedico?->apellido }}</span>
                                    </div>
                                    <div class="rounded-2xl bg-white px-4 py-3 shadow-sm">
                                        <span class="block text-xs uppercase tracking-wide text-slate-400">Servicio</span>
                                        <span id="summary-servicio">{{ $selectedServicio?->nombre }} @if ($selectedServicio) - {{ $selectedServicio->duracion_minutos }} min @endif</span>
                                    </div>
                                    <div class="rounded-2xl bg-white px-4 py-3 shadow-sm">
                                        <span class="block text-xs uppercase tracking-wide text-slate-400">Fecha</span>
                                        <span id="summary-fecha">{{ $selectedFechaLabel }}</span>
                                    </div>
                                </div>
                            </div>

                            <form id="portal-cita-form" action="{{ route('portal-citas.store') }}" method="POST" class="mt-5 space-y-6">
                                @csrf
                                <input id="appointment-medico-id" type="hidden" name="medico_id" value="{{ $selectedMedicoId }}">
                                <input id="appointment-servicio-id" type="hidden" name="servicio_id" value="{{ $selectedServicioId }}">
                                <input id="appointment-fecha" type="hidden" name="fecha" value="{{ $selectedFecha }}">

                                <div>
                                    <div class="flex items-end justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-black uppercase tracking-[0.2em] text-violet-600">Paso 4: Horario</p>
                                            <h3 class="mt-1 text-xl font-black text-slate-950">Elige tu horario</h3>
                                        </div>
                                    </div>

                                    <div id="horarios-grid" class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                        @foreach ($horarios as $slot)
                                            <label class="block select-none">
                                                <input type="radio" name="horario" value="{{ $slot['value'] }}" class="peer sr-only" {{ old('horario') === $slot['value'] ? 'checked' : '' }} required>
                                                <span class="flex min-h-16 cursor-pointer flex-col items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-center shadow-sm transition hover:border-violet-300 hover:bg-white peer-checked:border-violet-700 peer-checked:bg-violet-700 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-violet-700/20 peer-focus:ring-4 peer-focus:ring-violet-200">
                                                    <span class="text-base font-black leading-none">{{ $slot['label'] }}</span>
                                                    <span class="mt-1 text-[11px] font-bold opacity-75">Termina {{ $slot['ends_at'] }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div id="no-horarios-message" class="{{ $selectionComplete && count($horarios) === 0 ? '' : 'hidden' }} mt-4 rounded-2xl border border-violet-200 bg-violet-50 p-5 text-center">
                                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-white text-lg font-black text-violet-700 shadow-sm">i</div>
                                        <h2 class="mt-3 text-lg font-black text-violet-950">No hay horarios disponibles</h2>
                                        <p class="mx-auto mt-1 max-w-xl text-sm font-semibold leading-6 text-violet-800">El horario pudo haberse ocupado recientemente. Elige otra fecha disponible.</p>
                                    </div>

                                    @error('horario')
                                        <p class="mt-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div id="appointment-fields" class="{{ count($horarios) > 0 ? '' : 'hidden' }} rounded-3xl border border-slate-200 bg-slate-50 p-5">
                                    <div class="mb-5">
                                        @guest
                                            <p class="text-xs font-black uppercase tracking-[0.2em] text-violet-600">Datos del paciente</p>
                                            <h2 class="mt-1 text-2xl font-black text-slate-950">Completa tu solicitud</h2>
                                            <p class="mt-1 text-sm font-medium text-slate-500">Usaremos estos datos para identificarte y contactarte.</p>
                                        @else
                                            <p class="text-xs font-black uppercase tracking-[0.2em] text-violet-600">Confirmación</p>
                                            <h2 class="mt-1 text-2xl font-black text-slate-950">Confirma tu solicitud</h2>
                                            <p class="mt-1 text-sm font-medium text-slate-500">Usaremos los datos de tu cuenta para registrar la cita.</p>
                                        @endguest
                                    </div>

                                    @guest
                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div>
                                                <label for="nombre" class="block text-sm font-black text-slate-800">Nombre</label>
                                                <input id="nombre" type="text" name="nombre" value="{{ old('nombre') }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800 shadow-sm transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-violet-500" autocomplete="given-name" required>
                                                @error('nombre')
                                                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="apellido" class="block text-sm font-black text-slate-800">Apellido</label>
                                                <input id="apellido" type="text" name="apellido" value="{{ old('apellido') }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800 shadow-sm transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-violet-500" autocomplete="family-name" required>
                                                @error('apellido')
                                                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="email" class="block text-sm font-black text-slate-800">Email</label>
                                                <input id="email" type="email" name="email" value="{{ old('email') }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800 shadow-sm transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-violet-500" autocomplete="email" required>
                                                @error('email')
                                                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="telefono" class="block text-sm font-black text-slate-800">Teléfono</label>
                                                <input id="telefono" type="tel" name="telefono" value="{{ old('telefono') }}" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800 shadow-sm transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-violet-500" autocomplete="tel" required>
                                                @error('telefono')
                                                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    @endguest

                                    <div class="@guest mt-4 @endguest">
                                        <label for="motivo" class="block text-sm font-black text-slate-800">Motivo de consulta</label>
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

                                    <button type="submit" class="mt-5 inline-flex w-full select-none items-center justify-center rounded-2xl bg-violet-700 px-6 py-4 text-base font-black text-white shadow-xl shadow-violet-700/25 transition hover:-translate-y-0.5 hover:bg-violet-800 focus:outline-none focus:ring-4 focus:ring-violet-200">
                                        @auth Confirmar cita @else Solicitar cita @endauth
                                    </button>
                                </div>
                            </form>
                        </section>

                        <section id="empty-state" class="{{ $canChooseDate ? 'hidden' : '' }} mt-4 rounded-[1.65rem] border border-violet-200 bg-violet-50 p-5 text-center shadow-sm">
                            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-white text-lg font-black text-violet-700 shadow-sm">i</div>
                            <h2 class="mt-3 text-lg font-black text-violet-950">Completa la búsqueda</h2>
                            <p class="mx-auto mt-1 max-w-2xl text-sm font-semibold leading-6 text-violet-800">
                                Selecciona médico y servicio para ver automáticamente las próximas fechas disponibles.
                            </p>
                        </section>
                    </aside>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-4 pb-8 sm:px-6 lg:px-8">
                <div class="grid overflow-hidden rounded-[2rem] border border-white bg-white shadow-xl shadow-slate-200/70 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach([
                        ['value' => '+500', 'label' => 'pacientes satisfechos'],
                        ['value' => '+10', 'label' => 'especialistas'],
                        ['value' => '5', 'label' => 'estrellas'],
                        ['value' => 'Lun-Sáb', 'label' => 'atención disponible'],
                    ] as $stat)
                        <div class="border-b border-slate-100 px-6 py-6 last:border-b-0 sm:border-r sm:last:border-r-0 lg:border-b-0">
                            <p class="text-3xl font-black tracking-tight text-slate-950">{{ $stat['value'] }}</p>
                            <p class="mt-1 text-sm font-bold text-slate-500">{{ $stat['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section id="especialistas" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-violet-600">Especialistas destacados</p>
                        <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Equipo médico de confianza</h2>
                    </div>
                    <p class="max-w-2xl text-sm font-semibold leading-6 text-slate-500">Profesionales con agenda digital, servicios vinculados y disponibilidad validada en tiempo real.</p>
                </div>

                <div class="mt-8 grid gap-5 md:grid-cols-3">
                    @forelse($medicosDestacados as $medico)
                        @php
                            $serviciosMedico = $medico->servicios->take(3);
                        @endphp
                        <article class="group overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-2xl hover:shadow-violet-950/10">
                            <div class="relative flex h-44 items-center justify-center bg-gradient-to-br from-violet-100 via-white to-slate-100 p-6">
                                <div class="absolute inset-4 rounded-[1.6rem] bg-white/55 shadow-inner"></div>
                                <div class="relative z-10 h-[120px] w-[120px] overflow-hidden rounded-full bg-gradient-to-br from-violet-700 to-purple-600 shadow-xl shadow-violet-700/20 ring-4 ring-white">
                                    @if($medico->photo_url)
                                        <img src="{{ $medico->photo_url }}" alt="Foto de {{ $medico->nombre }} {{ $medico->apellido }}" class="h-full w-full object-cover object-top" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                                        <div class="hidden flex h-full w-full items-center justify-center text-3xl font-black text-white">
                                            {{ $medico->initials }}
                                        </div>
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-3xl font-black text-white">
                                            {{ $medico->initials }}
                                        </div>
                                    @endif
                                </div>
                                <span class="absolute right-5 top-5 rounded-full bg-white/90 px-3 py-1 text-xs font-black text-violet-700 shadow-sm">{{ $medico->photo_path ? 'Foto verificada' : 'Avatar' }}</span>
                            </div>
                            <div class="p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-xl font-black text-slate-950">{{ $medico->nombre }} {{ $medico->apellido }}</h3>
                                        <p class="mt-1 text-sm font-bold text-slate-500">{{ $medico->especialidad ?: 'Medicina general' }}</p>
                                        <p class="mt-1 text-xs font-extrabold uppercase tracking-wide text-violet-600">Experiencia clínica verificada</p>
                                    </div>
                                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-700 ring-1 ring-amber-100">5.0</span>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    @forelse($serviciosMedico as $servicio)
                                        <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700">{{ $servicio->nombre }}</span>
                                    @empty
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">Servicios por confirmar</span>
                                    @endforelse
                                </div>

                                <a href="#agenda" class="mt-5 inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700">
                                    Ver perfil
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[2rem] border border-violet-200 bg-violet-50 p-6 text-sm font-bold text-violet-800 md:col-span-3">Aún no hay especialistas registrados.</div>
                    @endforelse
                </div>
            </section>

            <section id="servicios" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="rounded-[2rem] bg-slate-950 p-6 text-white shadow-2xl shadow-slate-950/15 sm:p-8 lg:p-10">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-violet-200">Servicios más solicitados</p>
                            <h2 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Atención médica clara y transparente</h2>
                        </div>
                        <p class="max-w-2xl text-sm font-semibold leading-6 text-slate-300">Consulta duración y precio antes de seleccionar el horario que mejor se ajuste a tu agenda.</p>
                    </div>

                    <div class="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        @forelse($serviciosDestacados as $index => $servicio)
                            <article class="rounded-[1.6rem] border border-white/10 bg-white/10 p-5 shadow-sm backdrop-blur transition hover:-translate-y-1 hover:bg-white/15">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-400/20 text-violet-100 ring-1 ring-white/10">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $serviceIcons[$index % count($serviceIcons)] }}"/>
                                    </svg>
                                </div>
                                <h3 class="mt-5 text-lg font-black">{{ $servicio->nombre }}</h3>
                                <p class="mt-2 text-sm font-semibold text-slate-300">{{ $servicio->duracion_minutos }} minutos</p>
                                <p class="mt-4 text-2xl font-black text-white">{{ $servicio->precio !== null ? '$'.number_format((float) $servicio->precio, 2) : 'Consultar' }}</p>
                            </article>
                        @empty
                            @foreach(['Consulta general', 'Control médico', 'Teleorientación', 'Seguimiento'] as $index => $nombre)
                                <article class="rounded-[1.6rem] border border-white/10 bg-white/10 p-5 shadow-sm backdrop-blur">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-400/20 text-violet-100 ring-1 ring-white/10">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-3-3v6"/></svg>
                                    </div>
                                    <h3 class="mt-5 text-lg font-black">{{ $nombre }}</h3>
                                    <p class="mt-2 text-sm font-semibold text-slate-300">30 minutos</p>
                                    <p class="mt-4 text-2xl font-black text-white">Consultar</p>
                                </article>
                            @endforeach
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-violet-600">Opiniones de pacientes</p>
                        <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Experiencias que generan confianza</h2>
                    </div>
                </div>

                <div class="mt-8 grid gap-5 md:grid-cols-3">
                    @foreach($reviews as $review)
                        <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/70">
                            <div class="flex gap-1 text-amber-500" aria-label="5 estrellas">
                                @for($i = 0; $i < 5; $i++)
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81H7.03a1 1 0 0 0 .95-.69l1.07-3.292Z"/></svg>
                                @endfor
                            </div>
                            <p class="mt-5 text-sm font-semibold leading-7 text-slate-600">“{{ $review['text'] }}”</p>
                            <p class="mt-5 text-sm font-black text-slate-950">{{ $review['name'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <footer id="contacto" class="mt-12 border-t border-slate-200 bg-white">
                <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
                    <div>
                        <div class="inline-flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-2xl bg-violet-700 shadow-lg shadow-violet-700/20">
                                <img src="{{ asset('images/medilink_logo.png') }}" alt="MediLink" class="h-full w-full object-cover">
                            </span>
                            <span class="text-xl font-black tracking-tight text-slate-950">MediLink</span>
                        </div>
                        <p class="mt-4 text-sm font-semibold leading-6 text-slate-500">Clínica digital para agendar citas con especialistas, disponibilidad real y confirmación segura.</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-black uppercase tracking-[0.18em] text-slate-950">Contacto</h3>
                        <div class="mt-4 space-y-2 text-sm font-semibold text-slate-500">
                            <p>Dirección: Av. Salud 123, Centro Médico</p>
                            <p>Teléfono: (555) 123-4567</p>
                            <p>WhatsApp: +52 555 123 4567</p>
                            <p>Correo: contacto@medilink.test</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-black uppercase tracking-[0.18em] text-slate-950">Horario</h3>
                        <div class="mt-4 space-y-2 text-sm font-semibold text-slate-500">
                            <p>Lunes a viernes: 08:00 - 18:00</p>
                            <p>Sábado: 08:00 - 14:00</p>
                            <p>Domingo: urgencias programadas</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-black uppercase tracking-[0.18em] text-slate-950">Redes sociales</h3>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach(['Facebook', 'Instagram', 'LinkedIn'] as $social)
                                <a href="#inicio" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-600 transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700">{{ $social }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </footer>
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const picker = document.getElementById('portal-cita-picker');
                const medicoSelect = document.getElementById('medico_id');
                const servicioSelect = document.getElementById('servicio_id');
                const sinServicios = document.getElementById('sin-servicios-medico');
                const errorBox = document.getElementById('portal-loading-error');
                const fechasSection = document.getElementById('fechas-section');
                const fechasGrid = document.getElementById('fechas-grid');
                const noFechasMessage = document.getElementById('no-fechas-message');
                const bookingSection = document.getElementById('booking-section');
                const horariosGrid = document.getElementById('horarios-grid');
                const noHorariosMessage = document.getElementById('no-horarios-message');
                const appointmentFields = document.getElementById('appointment-fields');
                const emptyState = document.getElementById('empty-state');
                const summaryMedico = document.getElementById('summary-medico');
                const summaryServicio = document.getElementById('summary-servicio');
                const summaryFecha = document.getElementById('summary-fecha');
                const appointmentMedico = document.getElementById('appointment-medico-id');
                const appointmentServicio = document.getElementById('appointment-servicio-id');
                const appointmentFecha = document.getElementById('appointment-fecha');

                const state = {
                    medicoId: @json((string) ($selectedMedicoId ?? '')),
                    servicioId: @json((string) ($selectedServicioId ?? '')),
                    fecha: @json((string) ($selectedFecha ?? '')),
                    horario: @json(old('horario', '')),
                    servicios: @json($serviciosIniciales),
                    fechas: @json($fechasDisponibles),
                    horarios: @json($horarios),
                    loadingServices: false,
                    loadingDates: false,
                    loadingSlots: false,
                };

                const urlFromTemplate = (template, replacements) => {
                    let url = template;

                    Object.entries(replacements).forEach(([placeholder, value]) => {
                        url = url.replace(placeholder, encodeURIComponent(value));
                    });

                    return url;
                };

                const fetchJson = async (url) => {
                    const response = await fetch(url, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (! response.ok) {
                        throw new Error('No se pudo cargar la disponibilidad. Intenta nuevamente.');
                    }

                    return response.json();
                };

                const setError = (message = '') => {
                    errorBox.textContent = message;
                    errorBox.classList.toggle('hidden', ! message);
                };

                const serviceLabel = (servicio) => servicio?.label || '';
                const selectedService = () => state.servicios.find((servicio) => String(servicio.id) === String(state.servicioId));
                const selectedDate = () => state.fechas.find((fecha) => String(fecha.value) === String(state.fecha));

                const calendarDate = (value) => {
                    const [year, month, day] = value.split('-').map(Number);
                    const date = new Date(year, month - 1, day);
                    const weekdays = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

                    return {
                        weekday: weekdays[date.getDay()],
                        day: String(day).padStart(2, '0'),
                    };
                };

                const updateUrl = () => {
                    const url = new URL(window.location.href);

                    ['medico_id', 'servicio_id', 'fecha'].forEach((key) => url.searchParams.delete(key));

                    if (state.medicoId) {
                        url.searchParams.set('medico_id', state.medicoId);
                    }

                    if (state.servicioId) {
                        url.searchParams.set('servicio_id', state.servicioId);
                    }

                    if (state.fecha) {
                        url.searchParams.set('fecha', state.fecha);
                    }

                    window.history.replaceState({}, '', url);
                };

                const dateButtonClasses = (selected) => [
                    'group flex min-h-24 flex-col justify-between rounded-3xl border px-4 py-3 text-left shadow-sm transition focus:outline-none focus:ring-4 focus:ring-violet-200',
                    selected
                        ? 'border-violet-700 bg-violet-700 text-white shadow-lg shadow-violet-700/20'
                        : 'border-slate-200 bg-white text-slate-800 hover:-translate-y-0.5 hover:border-violet-300 hover:shadow-md',
                ].join(' ');

                const slotButtonClasses = () => 'flex min-h-16 cursor-pointer flex-col items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-center shadow-sm transition hover:border-violet-300 hover:bg-white peer-checked:border-violet-700 peer-checked:bg-violet-700 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-violet-700/20 peer-focus:ring-4 peer-focus:ring-violet-200';

                const renderServiceOptions = () => {
                    servicioSelect.replaceChildren();

                    const placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = ! state.medicoId
                        ? 'Selecciona primero un médico'
                        : state.loadingServices
                            ? 'Cargando servicios...'
                            : 'Selecciona servicio';
                    servicioSelect.appendChild(placeholder);

                    state.servicios.forEach((servicio) => {
                        const option = document.createElement('option');
                        option.value = servicio.id;
                        option.textContent = serviceLabel(servicio);
                        option.selected = String(servicio.id) === String(state.servicioId);
                        servicioSelect.appendChild(option);
                    });

                    servicioSelect.disabled = ! state.medicoId || state.loadingServices || state.servicios.length === 0;
                    sinServicios.classList.toggle('hidden', ! state.medicoId || state.loadingServices || state.servicios.length > 0);
                };

                const renderDates = () => {
                    const canChooseDate = Boolean(state.medicoId && state.servicioId);

                    fechasSection.classList.toggle('hidden', ! canChooseDate);
                    emptyState.classList.toggle('hidden', canChooseDate);

                    if (! canChooseDate) {
                        fechasGrid.replaceChildren();
                        noFechasMessage.classList.add('hidden');
                        return;
                    }

                    if (state.loadingDates) {
                        const loadingCard = document.createElement('div');
                        loadingCard.className = 'col-span-full rounded-3xl border border-violet-100 bg-violet-50 px-4 py-5 text-sm font-black text-violet-700';
                        loadingCard.textContent = 'Buscando próximas fechas disponibles...';
                        fechasGrid.replaceChildren(loadingCard);
                        noFechasMessage.classList.add('hidden');
                        return;
                    }

                    const fragment = document.createDocumentFragment();

                    state.fechas.forEach((fecha) => {
                        const selected = String(fecha.value) === String(state.fecha);
                        const parts = calendarDate(fecha.value);
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.dataset.dateValue = fecha.value;
                        button.className = dateButtonClasses(selected);

                        const weekday = document.createElement('span');
                        weekday.className = ['text-xs font-black uppercase tracking-[0.18em]', selected ? 'text-violet-100' : 'text-violet-600'].join(' ');
                        weekday.textContent = parts.weekday;

                        const day = document.createElement('span');
                        day.className = 'text-3xl font-black leading-none';
                        day.textContent = parts.day;

                        const count = document.createElement('span');
                        count.className = ['text-[11px] font-extrabold', selected ? 'text-violet-100' : 'text-slate-500'].join(' ');
                        count.textContent = `${fecha.slots_count} horarios disponibles`;

                        button.append(weekday, day, count);
                        fragment.appendChild(button);
                    });

                    fechasGrid.replaceChildren(fragment);
                    noFechasMessage.classList.toggle('hidden', state.fechas.length > 0);
                };

                const renderSlots = () => {
                    const hasDate = Boolean(state.medicoId && state.servicioId && state.fecha);

                    bookingSection.classList.toggle('hidden', ! hasDate);

                    if (! hasDate) {
                        horariosGrid.replaceChildren();
                        noHorariosMessage.classList.add('hidden');
                        appointmentFields.classList.add('hidden');
                        return;
                    }

                    appointmentMedico.value = state.medicoId;
                    appointmentServicio.value = state.servicioId;
                    appointmentFecha.value = state.fecha;
                    summaryMedico.textContent = medicoSelect.options[medicoSelect.selectedIndex]?.textContent.trim() || '';
                    summaryServicio.textContent = serviceLabel(selectedService());
                    summaryFecha.textContent = selectedDate()?.label || state.fecha;

                    if (state.loadingSlots) {
                        const loadingSlot = document.createElement('div');
                        loadingSlot.className = 'col-span-full rounded-2xl border border-violet-100 bg-violet-50 px-4 py-4 text-sm font-black text-violet-700';
                        loadingSlot.textContent = 'Cargando horarios disponibles...';
                        horariosGrid.replaceChildren(loadingSlot);
                        noHorariosMessage.classList.add('hidden');
                        appointmentFields.classList.add('hidden');
                        return;
                    }

                    const fragment = document.createDocumentFragment();

                    state.horarios.forEach((slot) => {
                        const label = document.createElement('label');
                        label.className = 'block select-none';

                        const input = document.createElement('input');
                        input.type = 'radio';
                        input.name = 'horario';
                        input.value = slot.value;
                        input.className = 'peer sr-only';
                        input.required = true;
                        input.checked = String(state.horario) === String(slot.value);

                        const button = document.createElement('span');
                        button.className = slotButtonClasses();

                        const start = document.createElement('span');
                        start.className = 'text-base font-black leading-none';
                        start.textContent = slot.label;

                        const end = document.createElement('span');
                        end.className = 'mt-1 text-[11px] font-bold opacity-75';
                        end.textContent = `Termina ${slot.ends_at}`;

                        button.append(start, end);
                        label.append(input, button);
                        fragment.appendChild(label);
                    });

                    horariosGrid.replaceChildren(fragment);
                    noHorariosMessage.classList.toggle('hidden', state.horarios.length > 0);
                    appointmentFields.classList.toggle('hidden', state.horarios.length === 0);
                };

                const render = () => {
                    renderServiceOptions();
                    renderDates();
                    renderSlots();
                };

                const loadServices = async () => {
                    state.medicoId = medicoSelect.value;
                    state.servicioId = '';
                    state.fecha = '';
                    state.horario = '';
                    state.servicios = [];
                    state.fechas = [];
                    state.horarios = [];
                    setError();
                    updateUrl();

                    if (! state.medicoId) {
                        render();
                        return;
                    }

                    state.loadingServices = true;
                    render();

                    try {
                        const data = await fetchJson(urlFromTemplate(picker.dataset.serviciosUrlTemplate, {
                            __MEDICO__: state.medicoId,
                        }));

                        state.servicios = data.servicios || [];
                    } catch (error) {
                        setError(error.message);
                    } finally {
                        state.loadingServices = false;
                        render();
                    }
                };

                const loadDates = async () => {
                    state.servicioId = servicioSelect.value;
                    state.fecha = '';
                    state.horario = '';
                    state.fechas = [];
                    state.horarios = [];
                    setError();
                    updateUrl();

                    if (! state.medicoId || ! state.servicioId) {
                        render();
                        return;
                    }

                    state.loadingDates = true;
                    render();

                    try {
                        const data = await fetchJson(urlFromTemplate(picker.dataset.fechasUrlTemplate, {
                            __MEDICO__: state.medicoId,
                            __SERVICIO__: state.servicioId,
                        }));

                        state.fechas = data.fechas || [];
                    } catch (error) {
                        setError(error.message);
                    } finally {
                        state.loadingDates = false;
                        render();
                    }
                };

                const loadSlots = async (fecha) => {
                    state.fecha = fecha;
                    state.horario = '';
                    state.horarios = [];
                    setError();
                    updateUrl();

                    if (! state.medicoId || ! state.servicioId || ! state.fecha) {
                        render();
                        return;
                    }

                    state.loadingSlots = true;
                    render();

                    try {
                        const data = await fetchJson(urlFromTemplate(picker.dataset.horariosUrlTemplate, {
                            __MEDICO__: state.medicoId,
                            __SERVICIO__: state.servicioId,
                            __FECHA__: state.fecha,
                        }));

                        state.horarios = data.horarios || [];
                    } catch (error) {
                        setError(error.message);
                    } finally {
                        state.loadingSlots = false;
                        render();
                    }
                };

                medicoSelect?.addEventListener('change', loadServices);
                servicioSelect?.addEventListener('change', loadDates);
                fechasGrid?.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-date-value]');

                    if (button) {
                        loadSlots(button.dataset.dateValue);
                    }
                });
                horariosGrid?.addEventListener('change', (event) => {
                    if (event.target.matches('input[name="horario"]')) {
                        state.horario = event.target.value;
                    }
                });

                render();
            });
        </script>
    </body>
</html>
