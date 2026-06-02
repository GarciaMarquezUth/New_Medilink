<x-app-layout>
    <x-slot name="header">
        @if($isPacienteDashboard)
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Portal del paciente</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Mis citas</h1>
                </div>
                <a href="{{ route('pacientes.citas.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                    Agendar cita
                </a>
            </div>
        @elseif($isMedicoDashboard)
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Panel médico</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Mi agenda clínica</h1>
                </div>
                <a href="{{ route('citas.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">Ver mis citas</a>
            </div>
        @else
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Panel principal</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Dashboard clínico</h1>
                </div>
                <div class="inline-flex items-center rounded-2xl border border-violet-100 bg-violet-50 px-4 py-2 text-sm font-bold text-violet-700">
                    {{ now()->format('d/m/Y') }}
                </div>
            </div>
        @endif
    </x-slot>

    @if($isPacienteDashboard)
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

            @if(session('status'))
                <div class="rounded-3xl border border-violet-100 bg-violet-50 px-5 py-4 text-sm font-semibold text-violet-700 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if($perfilPacienteIncompleto)
                <div class="flex flex-col gap-3 rounded-3xl border border-amber-100 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-800 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                    <span>Completa tu perfil médico para poder agendar citas.</span>
                    <a href="{{ route('pacientes.profile') }}" class="inline-flex items-center justify-center rounded-2xl bg-amber-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-amber-500/20 transition hover:bg-amber-600">
                        Completar perfil
                    </a>
                </div>
            @endif

            <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 p-6 text-white shadow-2xl shadow-violet-900/20 sm:p-8">
                <div class="max-w-3xl">
                    <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-violet-100 ring-1 ring-white/20">MediLink</span>
                    <h2 class="mt-5 text-3xl font-extrabold tracking-tight sm:text-4xl">Hola, {{ $user->name }}</h2>
                    <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-violet-100 sm:text-base">Consulta tus próximas citas, revisa el historial y agenda una nueva atención médica desde el portal del paciente.</p>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60">
                    <p class="text-sm font-bold text-slate-500">Próximas citas</p>
                    <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $proximasCitas->count() }}</p>
                    <p class="mt-1 text-xs font-semibold text-violet-600">Agendadas o confirmadas</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60">
                    <p class="text-sm font-bold text-slate-500">Historial</p>
                    <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $historialCitas->count() }}</p>
                    <p class="mt-1 text-xs font-semibold text-violet-600">Atendidas, vencidas o cerradas</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60">
                    <p class="text-sm font-bold text-slate-500">Canceladas</p>
                    <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $citasCanceladas }}</p>
                    <p class="mt-1 text-xs font-semibold text-violet-600">Cancelaciones registradas</p>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60">
                    <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-950">Próximas citas</h3>
                            <p class="mt-1 text-sm font-medium text-slate-500">Solo se muestran citas vinculadas a tu usuario.</p>
                        </div>
                        <a href="{{ route('pacientes.citas.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:bg-violet-700">Agendar cita</a>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse($proximasCitas as $cita)
                            <article class="p-6 transition hover:bg-violet-50/40">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="text-lg font-extrabold text-slate-950">{{ $cita->fecha_hora->format('d/m/Y H:i') }}</h4>
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold ring-1 {{ $estadoClasses($cita->estado) }}">
                                                {{ $estadoLabels[$cita->estado] ?? str_replace('_', ' ', $cita->estado) }}
                                            </span>
                                        </div>
                                        <div class="mt-2 flex items-center gap-3">
                                            <x-medico-avatar :medico="$cita->medico" class="h-9 w-9 rounded-xl" text-class="text-xs" />
                                            <p class="text-sm font-semibold text-slate-700">{{ $cita->medico?->nombre }} {{ $cita->medico?->apellido }}</p>
                                        </div>
                                        <p class="mt-1 text-sm font-medium text-slate-500">{{ $cita->servicio?->nombre ?? 'Servicio no especificado' }} @if($cita->servicio) · {{ $cita->servicio->duracion_minutos }} min @endif</p>
                                        <p class="mt-2 text-sm font-medium text-slate-600">{{ $cita->motivo }}</p>
                                    </div>

                                    @if($canCancelCita($cita))
                                        <form action="{{ route('citas.cancelar-paciente', $cita->id) }}" method="POST" class="shrink-0">
                                            @csrf
                                            <button type="submit" onclick="return confirm('¿Cancelar esta cita?')" class="inline-flex items-center justify-center rounded-2xl bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-700 transition hover:bg-rose-100">Cancelar cita</button>
                                        </form>
                                    @else
                                        <span class="inline-flex shrink-0 items-center justify-center rounded-2xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-500">No cancelable</span>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="px-6 py-12 text-center">
                                <p class="text-sm font-semibold text-slate-500">No tienes próximas citas.</p>
                                <a href="{{ route('pacientes.citas.create') }}" class="mt-4 inline-flex items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:bg-violet-700">Agendar una cita</a>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h3 class="text-lg font-extrabold text-slate-950">Historial de citas</h3>
                        <p class="mt-1 text-sm font-medium text-slate-500">Consulta el estado de tus citas anteriores o cerradas.</p>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse($historialCitas->take(8) as $cita)
                            <article class="px-6 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-extrabold text-slate-950">{{ $cita->fecha_hora->format('d/m/Y H:i') }}</p>
                                        <p class="mt-1 text-sm font-medium text-slate-500">{{ $cita->servicio?->nombre ?? 'Servicio no especificado' }}</p>
                                        <div class="mt-2 flex items-center gap-2">
                                            <x-medico-avatar :medico="$cita->medico" class="h-7 w-7 rounded-lg" text-class="text-[10px]" />
                                            <p class="text-xs font-semibold text-slate-400">{{ $cita->medico?->nombre }} {{ $cita->medico?->apellido }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold ring-1 {{ $estadoClasses($cita->estado) }}">
                                        {{ $estadoLabels[$cita->estado] ?? str_replace('_', ' ', $cita->estado) }}
                                    </span>
                                </div>
                            </article>
                        @empty
                            <div class="px-6 py-12 text-center">
                                <p class="text-sm font-semibold text-slate-500">Aún no tienes historial de citas.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    @elseif($isMedicoDashboard)
        <div class="space-y-6">
            <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 p-6 text-white shadow-2xl shadow-violet-900/20 sm:p-8">
                <div class="max-w-3xl">
                    <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-violet-100 ring-1 ring-white/20">MediLink</span>
                    <h2 class="mt-5 text-3xl font-extrabold tracking-tight sm:text-4xl">Hola, Dr. {{ $user->name }}</h2>
                    <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-violet-100 sm:text-base">Consulta tu agenda clínica, da seguimiento a tus pacientes y revisa únicamente las citas asociadas a tu perfil médico.</p>
                </div>
            </section>

            @if(! $medico)
                <div class="rounded-3xl border border-amber-100 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-800 shadow-sm">
                    Tu usuario médico aún no está vinculado a un registro de médico. Solicita a administración que vincule tu cuenta para ver tu agenda.
                </div>
            @else
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            <x-medico-avatar :medico="$medico" class="h-20 w-20 rounded-full" text-class="text-2xl" />
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Mi perfil</p>
                                <h3 class="mt-1 text-xl font-black text-slate-950">Dr. {{ $medico->nombre }} {{ $medico->apellido }}</h3>
                                <p class="mt-1 text-sm font-semibold text-slate-500">{{ $medico->especialidad }}</p>
                                <p class="mt-2 text-xs font-bold text-violet-600">{{ $medico->servicios->count() }} servicios asignados</p>
                            </div>
                        </div>
                        <a href="{{ route('medicos.profile') }}" class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">Cambiar foto y datos</a>
                    </div>
                </section>
            @endif

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60">
                    <p class="text-sm font-bold text-slate-500">Citas de hoy</p>
                    <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $citasHoy->count() }}</p>
                    <p class="mt-1 text-xs font-semibold text-violet-600">Agenda del día</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60">
                    <p class="text-sm font-bold text-slate-500">Próximas citas</p>
                    <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $proximasCitasMedico->count() }}</p>
                    <p class="mt-1 text-xs font-semibold text-violet-600">Agendadas o confirmadas</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60">
                    <p class="text-sm font-bold text-slate-500">Pendientes por atender</p>
                    <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $citasPendientesMedico->count() }}</p>
                    <p class="mt-1 text-xs font-semibold text-violet-600">Requieren seguimiento</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60">
                    <p class="text-sm font-bold text-slate-500">Citas atendidas</p>
                    <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $citasAtendidasMedico }}</p>
                    <p class="mt-1 text-xs font-semibold text-violet-600">Historial clínico propio</p>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60">
                    <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-950">Citas de hoy</h3>
                            <p class="mt-1 text-sm font-medium text-slate-500">Pacientes programados para tu atención de hoy.</p>
                        </div>
                        <a href="{{ route('citas.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:bg-violet-700">Ver agenda</a>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse($citasHoy as $cita)
                            <article class="p-6 transition hover:bg-violet-50/40">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="text-lg font-extrabold text-slate-950">{{ $cita->fecha_hora->format('H:i') }}</h4>
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold ring-1 {{ $estadoClasses($cita->estado) }}">
                                                {{ $estadoLabels[$cita->estado] ?? str_replace('_', ' ', $cita->estado) }}
                                            </span>
                                        </div>
                                        <p class="mt-2 text-sm font-semibold text-slate-700">{{ $cita->paciente?->nombre }} {{ $cita->paciente?->apellido }}</p>
                                        <p class="mt-1 text-sm font-medium text-slate-500">{{ $cita->servicio?->nombre ?? 'Servicio no especificado' }} @if($cita->servicio) · {{ $cita->servicio->duracion_minutos }} min @endif</p>
                                        <p class="mt-2 text-sm font-medium text-slate-600">{{ $cita->motivo }}</p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="px-6 py-12 text-center">
                                <p class="text-sm font-semibold text-slate-500">No tienes citas programadas para hoy.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h3 class="text-lg font-extrabold text-slate-950">Próximas citas</h3>
                        <p class="mt-1 text-sm font-medium text-slate-500">Siguientes atenciones asignadas a tu usuario médico.</p>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse($proximasCitasMedico->take(8) as $cita)
                            <article class="px-6 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-extrabold text-slate-950">{{ $cita->fecha_hora->format('d/m/Y H:i') }}</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ $cita->paciente?->nombre }} {{ $cita->paciente?->apellido }}</p>
                                        <p class="mt-1 text-xs font-semibold text-slate-400">{{ $cita->servicio?->nombre ?? 'Servicio no especificado' }}</p>
                                    </div>
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold ring-1 {{ $estadoClasses($cita->estado) }}">
                                        {{ $estadoLabels[$cita->estado] ?? str_replace('_', ' ', $cita->estado) }}
                                    </span>
                                </div>
                            </article>
                        @empty
                            <div class="px-6 py-12 text-center">
                                <p class="text-sm font-semibold text-slate-500">No tienes próximas citas pendientes.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60">
                <div class="flex flex-col gap-2 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-950">Mis pacientes</h3>
                        <p class="mt-1 text-sm font-medium text-slate-500">Pacientes vinculados únicamente a tus citas.</p>
                    </div>
                    <span class="rounded-full bg-violet-50 px-4 py-2 text-sm font-bold text-violet-700">{{ $pacientesMedicoData->count() }} pacientes</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Paciente</th>
                                <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Teléfono</th>
                                <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Email</th>
                                <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Última cita</th>
                                <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Próxima cita</th>
                                <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Estado</th>
                                <th class="px-6 py-4 text-right text-xs font-extrabold uppercase tracking-wider text-slate-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($pacientesMedicoData as $item)
                                @php($paciente = $item['paciente'])
                                <tr class="transition hover:bg-violet-50/40">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <p class="font-bold text-slate-950">{{ $paciente->nombre }} {{ $paciente->apellido }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-700">{{ $paciente->telefono ?: 'Sin teléfono' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-700">{{ $paciente->email ?: 'Sin email' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-600">
                                        @if($item['ultima'])
                                            {{ $item['ultima']->fecha_hora->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-slate-400">Sin registro</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-600">
                                        @if($item['proxima'])
                                            {{ $item['proxima']->fecha_hora->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-slate-400">Sin próxima cita</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        @if($item['estado_cita'])
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold ring-1 {{ $estadoClasses($item['estado_cita']->estado) }}">
                                                {{ $estadoLabels[$item['estado_cita']->estado] ?? str_replace('_', ' ', $item['estado_cita']->estado) }}
                                            </span>
                                        @else
                                            <span class="text-sm font-semibold text-slate-400">Sin estado</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                        <a href="{{ route('medicos.pacientes.show', $paciente->id) }}" class="inline-flex items-center justify-center rounded-xl bg-violet-50 px-3 py-2 text-xs font-bold text-violet-700 transition hover:bg-violet-100">
                                            Ver detalles
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <p class="text-sm font-semibold text-slate-500">Aún no tienes pacientes vinculados a tus citas.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    @else
        <div class="space-y-8">
            <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 p-6 text-white shadow-2xl shadow-violet-900/20 sm:p-8">
                <div class="grid gap-6 lg:grid-cols-[1.4fr_0.8fr] lg:items-center">
                    <div>
                        <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-violet-100 ring-1 ring-white/20">MediLink</span>
                        <h2 class="mt-5 text-3xl font-extrabold tracking-tight sm:text-4xl">Hola, {{ $user->name }}</h2>
                        <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-violet-100 sm:text-base">Gestiona la operación diaria de la clínica con una vista clara de citas, pacientes y médicos.</p>
                    </div>

                    <div class="rounded-3xl bg-white/12 p-5 ring-1 ring-white/20 backdrop-blur">
                        @role('admin')
                            <p class="text-sm font-bold text-violet-100">Rol activo</p>
                            <h3 class="mt-1 text-2xl font-extrabold">Administrador</h3>
                            <p class="mt-2 text-sm text-violet-100">Acceso completo a médicos, pacientes y agenda clínica.</p>
                        @endrole

                        @role('recepcionista')
                            <p class="text-sm font-bold text-violet-100">Rol activo</p>
                            <h3 class="mt-1 text-2xl font-extrabold">Recepción</h3>
                            <p class="mt-2 text-sm text-violet-100">Gestión de pacientes y agenda completa de citas.</p>
                        @endrole
                    </div>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60">
                    <div class="flex items-center justify-between">
                        <span class="rounded-2xl bg-violet-50 p-3 text-violet-700">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 0116 0"/></svg>
                        </span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total</span>
                    </div>
                    <p class="mt-5 text-sm font-bold text-slate-500">Médicos</p>
                    <p class="mt-1 text-3xl font-extrabold text-slate-950">{{ $totalMedicos }}</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60">
                    <div class="flex items-center justify-between">
                        <span class="rounded-2xl bg-fuchsia-50 p-3 text-fuchsia-700">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20a5 5 0 00-10 0M12 12a4 4 0 100-8 4 4 0 000 8z"/></svg>
                        </span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total</span>
                    </div>
                    <p class="mt-5 text-sm font-bold text-slate-500">Pacientes</p>
                    <p class="mt-1 text-3xl font-extrabold text-slate-950">{{ $totalPacientes }}</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60">
                    <div class="flex items-center justify-between">
                        <span class="rounded-2xl bg-indigo-50 p-3 text-indigo-700">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/></svg>
                        </span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Agenda</span>
                    </div>
                    <p class="mt-5 text-sm font-bold text-slate-500">Citas</p>
                    <p class="mt-1 text-3xl font-extrabold text-slate-950">{{ $totalCitas }}</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60">
                    <div class="flex items-center justify-between">
                        <span class="rounded-2xl bg-amber-50 p-3 text-amber-700">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5l3 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pendientes</span>
                    </div>
                    <p class="mt-5 text-sm font-bold text-slate-500">Por atender</p>
                    <p class="mt-1 text-3xl font-extrabold text-slate-950">{{ $citasPendientes }}</p>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 lg:col-span-2">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-950">Accesos rápidos</h3>
                            <p class="mt-1 text-sm font-medium text-slate-500">Continúa con las tareas más frecuentes.</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @can('citas.crear')
                            <a href="{{ route('citas.create') }}" class="rounded-3xl border border-violet-100 bg-violet-50 p-5 transition hover:-translate-y-1 hover:bg-violet-100">
                                <p class="text-sm font-bold text-violet-700">Nueva cita</p>
                                <p class="mt-2 text-xs font-medium text-violet-700/70">Agenda o reagenda consultas.</p>
                            </a>
                        @endcan

                        @can('pacientes.crear')
                            <a href="{{ route('pacientes.create') }}" class="rounded-3xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-1 hover:border-violet-100 hover:bg-violet-50">
                                <p class="text-sm font-bold text-slate-800">Nuevo paciente</p>
                                <p class="mt-2 text-xs font-medium text-slate-500">Registra datos clínicos básicos.</p>
                            </a>
                        @endcan

                        @can('medicos.crear')
                            <a href="{{ route('medicos.create') }}" class="rounded-3xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-1 hover:border-violet-100 hover:bg-violet-50">
                                <p class="text-sm font-bold text-slate-800">Nuevo médico</p>
                                <p class="mt-2 text-xs font-medium text-slate-500">Vincula usuarios médicos.</p>
                            </a>
                        @endcan

                        @can('servicios.crear')
                            <a href="{{ route('servicios.create') }}" class="rounded-3xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-1 hover:border-violet-100 hover:bg-violet-50">
                                <p class="text-sm font-bold text-slate-800">Nuevo servicio</p>
                                <p class="mt-2 text-xs font-medium text-slate-500">Define duración de consultas.</p>
                            </a>
                        @endcan

                        @can('disponibilidades.crear')
                            <a href="{{ route('disponibilidades.create') }}" class="rounded-3xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-1 hover:border-violet-100 hover:bg-violet-50">
                                <p class="text-sm font-bold text-slate-800">Gestionar horario</p>
                                <p class="mt-2 text-xs font-medium text-slate-500">Configura disponibilidad médica semanal.</p>
                            </a>
                        @endcan

                        @role('admin')
                            <a href="{{ route('permisos.index') }}" class="rounded-3xl border border-violet-100 bg-white p-5 transition hover:-translate-y-1 hover:bg-violet-50">
                                <p class="text-sm font-bold text-violet-700">Permisos</p>
                                <p class="mt-2 text-xs font-medium text-slate-500">Configura accesos por rol.</p>
                            </a>
                        @endrole
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60">
                    <h3 class="text-lg font-extrabold text-slate-950">Estado del sistema</h3>
                    <div class="mt-5 space-y-4">
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <span class="text-sm font-semibold text-slate-600">Roles</span>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Activo</span>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <span class="text-sm font-semibold text-slate-600">Agenda</span>
                            <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700">Operativa</span>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <span class="text-sm font-semibold text-slate-600">Interfaz</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">SaaS</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    @endif
</x-app-layout>
