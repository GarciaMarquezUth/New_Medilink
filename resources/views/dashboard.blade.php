@php
    $estadoStyles = [
        'agendada' => 'bg-sky-50 text-sky-700 ring-sky-100',
        'confirmada' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'cancelada' => 'bg-rose-50 text-rose-700 ring-rose-100',
        'atendida' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'no_show' => 'bg-amber-50 text-amber-700 ring-amber-100',
    ];

    $estadoLabel = [
        'agendada' => 'Agendada',
        'confirmada' => 'Confirmada',
        'cancelada' => 'Cancelada',
        'atendida' => 'Atendida',
        'no_show' => 'No asistio',
    ];

    $formatDate = fn ($date) => optional($date)->format('d/m/Y');
    $formatTime = fn ($date) => optional($date)->format('H:i');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-teal-700">{{ $roleLabel }}</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">
                    @if ($dashboardType === 'medico')
                        Agenda clinica de hoy
                    @elseif ($dashboardType === 'paciente')
                        Mi espacio de citas
                    @else
                        Operacion de la clinica
                    @endif
                </h1>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm">
                <svg class="h-4 w-4 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/>
                </svg>
                {{ now()->format('d/m/Y') }}
            </div>
        </div>
    </x-slot>

    @if ($dashboardType === 'medico')
        <div class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">MediLink</p>
                        <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">Hola, {{ $user->name }}</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Tu panel prioriza la siguiente atencion, la agenda del dia y las acciones clinicas que necesitas resolver sin navegar por informacion administrativa.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-500">Hoy</p>
                            <p class="mt-2 text-2xl font-extrabold text-slate-950">{{ $resumenMedico['hoy'] }}</p>
                        </div>
                        <div class="rounded-xl border border-sky-100 bg-sky-50 p-4">
                            <p class="text-xs font-bold uppercase text-sky-700">Pendientes</p>
                            <p class="mt-2 text-2xl font-extrabold text-sky-950">{{ $resumenMedico['pendientes'] }}</p>
                        </div>
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                            <p class="text-xs font-bold uppercase text-emerald-700">Atendidas</p>
                            <p class="mt-2 text-2xl font-extrabold text-emerald-950">{{ $resumenMedico['atendidas'] }}</p>
                        </div>
                        <div class="rounded-xl border border-amber-100 bg-amber-50 p-4">
                            <p class="text-xs font-bold uppercase text-amber-700">Ausencias</p>
                            <p class="mt-2 text-2xl font-extrabold text-amber-950">{{ $resumenMedico['no_show'] }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[0.9fr_1.4fr]">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold text-teal-700">Proximo paciente</p>
                            <h3 class="mt-1 text-xl font-extrabold text-slate-950">Siguiente atencion</h3>
                        </div>
                        <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-bold text-teal-700">En agenda</span>
                    </div>

                    @if ($proximaCita)
                        <div class="mt-6 space-y-5">
                            <div>
                                <p class="text-2xl font-extrabold text-slate-950">{{ $proximaCita->paciente?->nombre }} {{ $proximaCita->paciente?->apellido }}</p>
                                <p class="mt-1 text-sm font-medium text-slate-500">{{ $proximaCita->servicio?->nombre ?? 'Servicio sin asignar' }}</p>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <p class="text-xs font-bold uppercase text-slate-500">Fecha</p>
                                    <p class="mt-1 font-bold text-slate-900">{{ $formatDate($proximaCita->fecha_hora) }}</p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <p class="text-xs font-bold uppercase text-slate-500">Hora</p>
                                    <p class="mt-1 font-bold text-slate-900">{{ $formatTime($proximaCita->fecha_hora) }}</p>
                                </div>
                            </div>
                            <p class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">{{ $proximaCita->motivo }}</p>
                            <a href="{{ route('citas.index') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-teal-700">Ver agenda completa</a>
                        </div>
                    @else
                        <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm font-medium text-slate-500">No hay citas pendientes asignadas por ahora.</div>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-bold text-teal-700">Agenda de hoy</p>
                            <h3 class="mt-1 text-xl font-extrabold text-slate-950">Pacientes programados</h3>
                        </div>
                        <a href="{{ route('citas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-teal-200 hover:bg-teal-50 hover:text-teal-800">Abrir citas</a>
                    </div>

                    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200">
                        @forelse ($citasHoy as $cita)
                            <div class="grid gap-4 border-b border-slate-200 bg-white p-4 last:border-b-0 md:grid-cols-[88px_1fr_auto] md:items-center">
                                <div>
                                    <p class="text-xl font-extrabold text-slate-950">{{ $formatTime($cita->fecha_hora) }}</p>
                                    <p class="text-xs font-bold uppercase text-slate-400">{{ $formatDate($cita->fecha_hora) }}</p>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-950">{{ $cita->paciente?->nombre }} {{ $cita->paciente?->apellido }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $cita->servicio?->nombre ?? 'Servicio sin asignar' }} · {{ $cita->motivo }}</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $estadoStyles[$cita->estado] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">{{ $estadoLabel[$cita->estado] ?? ucfirst($cita->estado) }}</span>
                                    @if (in_array($cita->estado, ['agendada', 'confirmada'], true))
                                        <form method="POST" action="{{ route('citas.atendida', $cita->id) }}">
                                            @csrf
                                            <button class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-emerald-700">Atendida</button>
                                        </form>
                                        <form method="POST" action="{{ route('citas.no-presentada', $cita->id) }}">
                                            @csrf
                                            <button class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800 transition hover:bg-amber-100">No asistio</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="bg-slate-50 p-6 text-sm font-medium text-slate-500">Tu agenda esta libre para hoy.</div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-extrabold text-slate-950">Disponibilidad configurada</h3>
                    <div class="mt-5 space-y-3">
                        @forelse ($medico?->disponibilidades ?? [] as $disponibilidad)
                            <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                                <div>
                                    <p class="font-bold text-slate-900">{{ $disponibilidad->diaNombre() }}</p>
                                    <p class="text-sm text-slate-500">{{ substr($disponibilidad->hora_inicio, 0, 5) }} - {{ substr($disponibilidad->hora_fin, 0, 5) }}</p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $disponibilidad->activo ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ $disponibilidad->activo ? 'Activa' : 'Pausada' }}</span>
                            </div>
                        @empty
                            <p class="rounded-xl bg-slate-50 p-4 text-sm font-medium text-slate-500">Aun no tienes disponibilidad registrada.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-extrabold text-slate-950">Historial reciente</h3>
                    <div class="mt-5 space-y-3">
                        @forelse ($citasRecientes as $cita)
                            <div class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3">
                                <div>
                                    <p class="font-bold text-slate-900">{{ $cita->paciente?->nombre }} {{ $cita->paciente?->apellido }}</p>
                                    <p class="text-sm text-slate-500">{{ $formatDate($cita->fecha_hora) }} · {{ $cita->servicio?->nombre ?? 'Servicio sin asignar' }}</p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $estadoStyles[$cita->estado] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">{{ $estadoLabel[$cita->estado] ?? ucfirst($cita->estado) }}</span>
                            </div>
                        @empty
                            <p class="rounded-xl bg-slate-50 p-4 text-sm font-medium text-slate-500">Sin atenciones recientes.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    @elseif ($dashboardType === 'paciente')
        <div class="space-y-6">
            <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">MediLink</p>
                    <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">Hola, {{ $user->name }}</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Aqui tienes lo esencial: tu proxima cita, acceso para solicitar una nueva y el estado de tus consultas.</p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-500">Proximas</p>
                            <p class="mt-2 text-2xl font-extrabold text-slate-950">{{ $resumenPaciente['proximas'] }}</p>
                        </div>
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                            <p class="text-xs font-bold uppercase text-emerald-700">Confirmadas</p>
                            <p class="mt-2 text-2xl font-extrabold text-emerald-950">{{ $resumenPaciente['confirmadas'] }}</p>
                        </div>
                        <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                            <p class="text-xs font-bold uppercase text-indigo-700">Historial</p>
                            <p class="mt-2 text-2xl font-extrabold text-indigo-950">{{ $resumenPaciente['historial'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-teal-200 bg-teal-50 p-6 shadow-sm">
                    <p class="text-sm font-bold text-teal-800">Solicitar nueva cita</p>
                    <h3 class="mt-2 text-2xl font-extrabold text-slate-950">Encuentra un horario libre</h3>
                    <p class="mt-3 text-sm leading-6 text-teal-900/80">El portal muestra medicos, fechas y horarios realmente disponibles para evitar solicitudes duplicadas.</p>
                    <a href="{{ route('portal.citas.index') }}" class="mt-6 inline-flex items-center justify-center rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-teal-800">Agendar cita</a>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-bold text-teal-700">Mi proxima cita</p>
                    @if ($proximaCita)
                        <h3 class="mt-2 text-2xl font-extrabold text-slate-950">{{ $proximaCita->servicio?->nombre ?? 'Consulta medica' }}</h3>
                        <div class="mt-5 space-y-3">
                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-slate-500">Medico</p>
                                <p class="mt-1 font-bold text-slate-950">{{ $proximaCita->medico?->nombre }} {{ $proximaCita->medico?->apellido }}</p>
                                <p class="text-sm text-slate-500">{{ $proximaCita->medico?->especialidad }}</p>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <p class="text-xs font-bold uppercase text-slate-500">Fecha</p>
                                    <p class="mt-1 font-bold text-slate-950">{{ $formatDate($proximaCita->fecha_hora) }}</p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <p class="text-xs font-bold uppercase text-slate-500">Hora</p>
                                    <p class="mt-1 font-bold text-slate-950">{{ $formatTime($proximaCita->fecha_hora) }}</p>
                                </div>
                            </div>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $estadoStyles[$proximaCita->estado] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">{{ $estadoLabel[$proximaCita->estado] ?? ucfirst($proximaCita->estado) }}</span>
                        </div>
                    @else
                        <div class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6">
                            <h3 class="text-lg font-extrabold text-slate-950">No tienes citas proximas</h3>
                            <p class="mt-2 text-sm text-slate-500">Puedes solicitar una cita desde el portal publico cuando lo necesites.</p>
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-bold text-teal-700">Mis citas</p>
                            <h3 class="mt-1 text-xl font-extrabold text-slate-950">Proximas consultas</h3>
                        </div>
                        <a href="{{ route('citas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-teal-200 hover:bg-teal-50 hover:text-teal-800">Ver todas</a>
                    </div>

                    <div class="mt-6 space-y-3">
                        @forelse ($proximasCitas as $cita)
                            <div class="grid gap-3 rounded-xl bg-slate-50 p-4 sm:grid-cols-[96px_1fr_auto] sm:items-center">
                                <div>
                                    <p class="font-extrabold text-slate-950">{{ $formatTime($cita->fecha_hora) }}</p>
                                    <p class="text-xs font-bold uppercase text-slate-400">{{ $formatDate($cita->fecha_hora) }}</p>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900">{{ $cita->medico?->nombre }} {{ $cita->medico?->apellido }}</p>
                                    <p class="text-sm text-slate-500">{{ $cita->servicio?->nombre ?? 'Servicio sin asignar' }}</p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $estadoStyles[$cita->estado] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">{{ $estadoLabel[$cita->estado] ?? ucfirst($cita->estado) }}</span>
                            </div>
                        @empty
                            <p class="rounded-xl bg-slate-50 p-4 text-sm font-medium text-slate-500">No hay citas proximas registradas.</p>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-extrabold text-slate-950">Historial reciente</h3>
                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    @forelse ($historialCitas as $cita)
                        <div class="rounded-xl bg-slate-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-bold text-slate-950">{{ $cita->servicio?->nombre ?? 'Consulta medica' }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $formatDate($cita->fecha_hora) }} · {{ $cita->medico?->nombre }} {{ $cita->medico?->apellido }}</p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $estadoStyles[$cita->estado] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">{{ $estadoLabel[$cita->estado] ?? ucfirst($cita->estado) }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-xl bg-slate-50 p-4 text-sm font-medium text-slate-500">Aun no tienes historial de citas.</p>
                    @endforelse
                </div>
            </section>
        </div>
    @else
        <div class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">MediLink</p>
                        <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">Hola, {{ $user->name }}</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Vista operativa para coordinar pacientes, medicos y agenda sin mezclar informacion clinica individual.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('citas.create') }}" class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-teal-700">Nueva cita</a>
                        <a href="{{ route('pacientes.create') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-teal-200 hover:bg-teal-50">Nuevo paciente</a>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-slate-500">Medicos</p>
                    <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $totalMedicos }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-slate-500">Pacientes</p>
                    <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $totalPacientes }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-slate-500">Citas totales</p>
                    <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $totalCitas }}</p>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
                    <p class="text-sm font-bold text-amber-700">Por atender</p>
                    <p class="mt-2 text-3xl font-extrabold text-amber-950">{{ $citasPendientes }}</p>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-[1.4fr_0.8fr]">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-bold text-teal-700">Agenda operativa</p>
                            <h3 class="mt-1 text-xl font-extrabold text-slate-950">Citas de hoy</h3>
                        </div>
                        <a href="{{ route('citas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-teal-200 hover:bg-teal-50 hover:text-teal-800">Gestionar agenda</a>
                    </div>

                    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200">
                        @forelse ($citasHoy as $cita)
                            <div class="grid gap-4 border-b border-slate-200 p-4 last:border-b-0 md:grid-cols-[88px_1fr_auto] md:items-center">
                                <div>
                                    <p class="text-xl font-extrabold text-slate-950">{{ $formatTime($cita->fecha_hora) }}</p>
                                    <p class="text-xs font-bold uppercase text-slate-400">{{ $formatDate($cita->fecha_hora) }}</p>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-950">{{ $cita->paciente?->nombre }} {{ $cita->paciente?->apellido }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $cita->medico?->nombre }} {{ $cita->medico?->apellido }} · {{ $cita->servicio?->nombre ?? 'Servicio sin asignar' }}</p>
                                </div>
                                <span class="w-fit rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $estadoStyles[$cita->estado] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">{{ $estadoLabel[$cita->estado] ?? ucfirst($cita->estado) }}</span>
                            </div>
                        @empty
                            <div class="bg-slate-50 p-6 text-sm font-medium text-slate-500">No hay citas programadas para hoy.</div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-extrabold text-slate-950">Accesos frecuentes</h3>
                    <div class="mt-5 space-y-3">
                        <a href="{{ route('citas.create') }}" class="block rounded-xl border border-teal-100 bg-teal-50 px-4 py-3 text-sm font-bold text-teal-800 transition hover:bg-teal-100">Agendar cita</a>
                        <a href="{{ route('pacientes.index') }}" class="block rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100">Gestionar pacientes</a>
                        <a href="{{ route('medicos.index') }}" class="block rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100">Gestionar medicos</a>
                        <a href="{{ route('portal.citas.index') }}" class="block rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm font-bold text-indigo-800 transition hover:bg-indigo-100">Ver portal publico</a>
                    </div>
                    <div class="mt-6 rounded-xl border border-amber-100 bg-amber-50 p-4">
                        <p class="text-sm font-bold text-amber-800">Cancelaciones de hoy</p>
                        <p class="mt-2 text-3xl font-extrabold text-amber-950">{{ $citasCanceladasHoy }}</p>
                    </div>
                </div>
            </section>
        </div>
    @endif
</x-app-layout>
