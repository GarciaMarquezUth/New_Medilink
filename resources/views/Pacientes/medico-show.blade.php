@php
    $estadoClasses = fn ($estado) => match ($estado) {
        'atendida' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'no_show' => 'bg-orange-50 text-orange-700 ring-orange-100',
        'cancelada' => 'bg-rose-50 text-rose-700 ring-rose-100',
        'confirmada' => 'bg-blue-50 text-blue-700 ring-blue-100',
        default => 'bg-amber-50 text-amber-700 ring-amber-100',
    };
    $estadosOcupantes = \App\Models\Cita::estadosOcupantes();
    $ultimaCita = $citas
        ->filter(fn ($cita) => $cita->fecha_hora->isPast() || ! in_array($cita->estado, $estadosOcupantes, true))
        ->sortByDesc('fecha_hora')
        ->first();
    $proximaCita = $citas
        ->filter(fn ($cita) => $cita->fecha_hora->isFuture() && in_array($cita->estado, $estadosOcupantes, true))
        ->sortBy('fecha_hora')
        ->first();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Mis pacientes</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ $paciente->nombre }} {{ $paciente->apellido }}</h1>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Volver al dashboard
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 p-6 text-white shadow-2xl shadow-violet-900/20 sm:p-8">
            <div class="grid gap-6 lg:grid-cols-[1fr_0.75fr] lg:items-center">
                <div>
                    <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-violet-100 ring-1 ring-white/20">Paciente vinculado</span>
                    <h2 class="mt-5 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $paciente->nombre }} {{ $paciente->apellido }}</h2>
                    <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-violet-100 sm:text-base">
                        Historial visible solo para tus citas como {{ $medico->nombre }} {{ $medico->apellido }}.
                    </p>
                </div>
                <div class="rounded-3xl bg-white/12 p-5 ring-1 ring-white/20 backdrop-blur">
                    <p class="text-sm font-bold text-violet-100">Citas con este paciente</p>
                    <p class="mt-1 text-4xl font-extrabold">{{ $citas->count() }}</p>
                    <p class="mt-2 text-sm text-violet-100">Solo se incluyen atenciones asignadas a tu perfil médico.</p>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60">
                <p class="text-sm font-bold text-slate-500">Email</p>
                <p class="mt-2 break-words text-base font-extrabold text-slate-950">{{ $paciente->email ?: 'Sin email' }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60">
                <p class="text-sm font-bold text-slate-500">Teléfono</p>
                <p class="mt-2 text-base font-extrabold text-slate-950">{{ $paciente->telefono ?: 'Sin teléfono' }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60">
                <p class="text-sm font-bold text-slate-500">Última cita</p>
                <p class="mt-2 text-base font-extrabold text-slate-950">{{ $ultimaCita?->fecha_hora?->format('d/m/Y H:i') ?: 'Sin registro' }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60">
                <p class="text-sm font-bold text-slate-500">Próxima cita</p>
                <p class="mt-2 text-base font-extrabold text-slate-950">{{ $proximaCita?->fecha_hora?->format('d/m/Y H:i') ?: 'Sin próxima cita' }}</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60">
                <div class="mb-5 border-b border-slate-100 pb-5">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Datos personales</p>
                    <h2 class="mt-1 text-xl font-extrabold text-slate-950">Información del paciente</h2>
                </div>

                <dl class="space-y-4">
                    <div>
                        <dt class="text-xs font-extrabold uppercase tracking-wide text-slate-400">Nombre completo</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-800">{{ $paciente->nombre }} {{ $paciente->apellido }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-extrabold uppercase tracking-wide text-slate-400">Fecha de nacimiento</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-800">{{ $paciente->fecha_nacimiento ?: 'Sin registrar' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-extrabold uppercase tracking-wide text-slate-400">Género</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-800">{{ $paciente->genero ?: 'Sin registrar' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-extrabold uppercase tracking-wide text-slate-400">Tipo de sangre</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-800">{{ $paciente->tipo_sangre ?: 'Sin registrar' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-extrabold uppercase tracking-wide text-slate-400">Dirección</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-800">{{ $paciente->direccion ?: 'Sin registrar' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-extrabold uppercase tracking-wide text-slate-400">Alergias</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-800">{{ $paciente->alergias ?: 'Sin registrar' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-extrabold uppercase tracking-wide text-slate-400">Contacto de emergencia</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-800">{{ $paciente->contacto_emergencia ?: 'Sin registrar' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-extrabold uppercase tracking-wide text-slate-400">Teléfono de emergencia</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-800">{{ $paciente->telefono_emergencia ?: 'Sin registrar' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60">
                <div class="flex flex-col gap-2 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Historial clínico</p>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-950">Citas con este médico</h2>
                    </div>
                    <span class="rounded-full bg-violet-50 px-4 py-2 text-sm font-bold text-violet-700">{{ $citas->count() }} registros</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Fecha</th>
                                <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Servicio</th>
                                <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Motivo</th>
                                <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Historia</th>
                                <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($citas as $cita)
                                <tr class="transition hover:bg-violet-50/40">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-slate-800">{{ $cita->fecha_hora->format('d/m/Y H:i') }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-700">
                                        {{ $cita->servicio?->nombre ?? 'Servicio no especificado' }}
                                        @if($cita->servicio)
                                            <span class="block text-xs font-medium text-slate-500">{{ $cita->servicio->duracion_minutos }} min</span>
                                        @endif
                                    </td>
                                    <td class="min-w-64 px-6 py-4 text-sm font-medium text-slate-600">{{ $cita->motivo }}</td>
                                    <td class="min-w-72 px-6 py-4 text-sm font-medium text-slate-600">
                                        @if($cita->historiaClinica)
                                            <p class="font-semibold text-slate-800">
                                                {{ \Illuminate\Support\Str::limit($cita->historiaClinica->diagnostico ?: $cita->historiaClinica->observaciones ?: 'Historia clínica registrada', 90) }}
                                            </p>
                                            <a href="{{ route('historias-clinicas.edit', $cita->id) }}" class="mt-2 inline-flex rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 transition hover:bg-blue-100">Editar historia</a>
                                        @else
                                            <a href="{{ route('historias-clinicas.edit', $cita->id) }}" class="inline-flex rounded-xl bg-violet-50 px-3 py-2 text-xs font-bold text-violet-700 transition hover:bg-violet-100">Agregar historia</a>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold ring-1 {{ $estadoClasses($cita->estado) }}">
                                            {{ $estadoLabels[$cita->estado] ?? str_replace('_', ' ', $cita->estado) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
