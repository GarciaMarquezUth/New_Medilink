<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Agenda clínica</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Citas</h1>
            </div>
            @can('citas.crear')
                <a href="{{ route('citas.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                    Nueva cita
                </a>
            @endcan
            @role('paciente')
                <a href="{{ route('pacientes.citas.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                    Agendar cita
                </a>
            @endrole
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('success'))
            <div class="rounded-3xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-3xl border border-rose-100 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700 shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <form method="GET" action="{{ route('citas.index') }}" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_180px_180px_180px_auto] lg:items-end">
                <div>
                    <x-input-label for="q" value="Buscar" />
                    <x-text-input id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="mt-2" placeholder="Paciente, médico, servicio o motivo" />
                </div>
                @if($showEstadoFilter)
                    <div>
                        <x-input-label for="estado" value="Estado" />
                        <select id="estado" name="estado" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm transition focus:border-violet-500 focus:ring-violet-500">
                            <option value="">Todos</option>
                            @foreach($estadoLabels as $estado => $label)
                                <option value="{{ $estado }}" @selected(($filters['estado'] ?? '') === $estado)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <x-input-label for="fecha_desde" value="Desde" />
                    <x-text-input id="fecha_desde" type="date" name="fecha_desde" value="{{ $filters['fecha_desde'] ?? '' }}" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="fecha_hasta" value="Hasta" />
                    <x-text-input id="fecha_hasta" type="date" name="fecha_hasta" value="{{ $filters['fecha_hasta'] ?? '' }}" class="mt-2" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:bg-violet-700">Filtrar</button>
                    <a href="{{ route('citas.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">Limpiar</a>
                </div>
            </form>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60">
            <div class="flex flex-col gap-2 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-950">Agenda de citas</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Consulta estados, servicios, pacientes y médicos asignados.</p>
                </div>
                <span class="rounded-full bg-violet-50 px-4 py-2 text-sm font-bold text-violet-700">{{ $citas->total() }} registros</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Paciente</th>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Médico</th>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Servicio</th>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Fecha</th>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Estado</th>
                            <th class="px-6 py-4 text-right text-xs font-extrabold uppercase tracking-wider text-slate-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($citas as $cita)
                            @php
                                $estadoClasses = match ($cita->estado) {
                                    'atendida' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                                    'no_show' => 'bg-orange-50 text-orange-700 ring-orange-100',
                                    'cancelada' => 'bg-rose-50 text-rose-700 ring-rose-100',
                                    'confirmada' => 'bg-blue-50 text-blue-700 ring-blue-100',
                                    default => 'bg-amber-50 text-amber-700 ring-amber-100',
                                };
                            @endphp
                            <tr class="transition hover:bg-violet-50/40">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <p class="font-bold text-slate-950">{{ $cita->paciente->nombre }} {{ $cita->paciente->apellido }}</p>
                                    <p class="text-sm font-medium text-slate-500">{{ $cita->motivo }}</p>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <x-medico-avatar :medico="$cita->medico" class="h-10 w-10 rounded-2xl" text-class="text-xs" />
                                        <span class="text-sm font-semibold text-slate-700">{{ $cita->medico->nombre }} {{ $cita->medico->apellido }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-700">
                                    @if($cita->servicio)
                                        <p>{{ $cita->servicio->nombre }}</p>
                                        <p class="text-xs font-medium text-slate-500">{{ $cita->servicio->duracion_minutos }} min</p>
                                    @else
                                        <span class="text-sm font-medium text-slate-400">Sin servicio</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-600">
                                    {{ \Illuminate\Support\Carbon::parse($cita->fecha_hora)->format('d/m/Y H:i') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold capitalize ring-1 {{ $estadoClasses }}">
                                        {{ $estadoLabels[$cita->estado] ?? str_replace('_', ' ', $cita->estado) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="inline-flex flex-wrap justify-end gap-2">
                                        @hasanyrole(['admin', 'recepcionista'])
                                            @can('citas.editar')
                                                <a href="{{ route('citas.edit', $cita->id) }}" class="rounded-xl bg-violet-50 px-3 py-2 text-xs font-bold text-violet-700 transition hover:bg-violet-100">Editar</a>
                                            @endcan
                                        @endhasanyrole
                                        @can('citas.eliminar')
                                            <form action="{{ route('citas.destroy', $cita->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100" onclick="return confirm('¿Borrar?')">Eliminar</button>
                                            </form>
                                        @endcan

                                        @role('medico')
                                            @can('citas.editar')
                                                <a href="{{ route('historias-clinicas.edit', $cita->id) }}" class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 transition hover:bg-blue-100">
                                                    {{ $cita->historiaClinica ? 'Ver historia' : 'Historia' }}
                                                </a>
                                                @if(in_array($cita->estado, $estadosOcupantes, true))
                                                    <form action="{{ route('citas.atendida', $cita->id) }}" method="POST">
                                                        @csrf
                                                        <button class="rounded-xl bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100">Atendida</button>
                                                    </form>
                                                    <form action="{{ route('citas.no-presentada', $cita->id) }}" method="POST">
                                                        @csrf
                                                        <button class="rounded-xl bg-orange-50 px-3 py-2 text-xs font-bold text-orange-700 transition hover:bg-orange-100">No presentada</button>
                                                    </form>
                                                @else
                                                    <span class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-500">Finalizada</span>
                                                @endif
                                            @endcan
                                        @endrole

                                        @role('paciente')
                                            @if(in_array($cita->estado, $estadosOcupantes, true) && \Illuminate\Support\Carbon::parse($cita->fecha_hora)->isFuture())
                                                <form action="{{ route('citas.cancelar-paciente', $cita->id) }}" method="POST">
                                                    @csrf
                                                    <button class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100" onclick="return confirm('¿Cancelar esta cita?')">Cancelar</button>
                                                </form>
                                            @else
                                                <span class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-500">No cancelable</span>
                                            @endif
                                        @endrole
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <p class="text-sm font-semibold text-slate-500">No hay citas para mostrar.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $citas->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
