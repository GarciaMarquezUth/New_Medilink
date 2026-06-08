<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Agenda visual</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Calendario de citas</h1>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('citas.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">Ver tabla</a>
                @can('citas.crear')
                    <a href="{{ route('citas.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:bg-violet-700">Nueva cita</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-500">Mes seleccionado</p>
                    <h2 class="mt-1 text-2xl font-black capitalize text-slate-950">{{ $month->translatedFormat('F Y') }}</h2>
                    <p class="mt-1 text-sm font-semibold text-violet-600">{{ $citas->count() }} citas en este mes</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="{{ route('citas.calendar', ['mes' => $previousMonth]) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">Mes anterior</a>
                    <form method="GET" action="{{ route('citas.calendar') }}" class="flex gap-2">
                        <input type="month" name="mes" value="{{ $month->format('Y-m') }}" class="rounded-2xl border-slate-200 text-sm font-semibold text-slate-800 shadow-sm focus:border-violet-500 focus:ring-violet-500">
                        <button type="submit" class="rounded-2xl bg-violet-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:bg-violet-700">Ir</button>
                    </form>
                    <a href="{{ route('citas.calendar', ['mes' => $nextMonth]) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">Mes siguiente</a>
                </div>
            </div>
        </section>

        <section class="hidden overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60 lg:block">
            <div class="grid grid-cols-7 border-b border-slate-100 bg-slate-50/80">
                @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $dayName)
                    <div class="px-4 py-3 text-xs font-extrabold uppercase tracking-wider text-slate-500">{{ $dayName }}</div>
                @endforeach
            </div>

            <div class="grid grid-cols-7">
                @foreach($calendarDays as $day)
                    <div class="min-h-44 border-b border-r border-slate-100 p-3 {{ $day['is_current_month'] ? 'bg-white' : 'bg-slate-50/60' }}">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full text-sm font-black {{ $day['is_today'] ? 'bg-violet-600 text-white' : ($day['is_current_month'] ? 'text-slate-900' : 'text-slate-400') }}">
                                {{ $day['date']->day }}
                            </span>
                            @if($day['citas']->isNotEmpty())
                                <span class="rounded-full bg-violet-50 px-2 py-1 text-[11px] font-black text-violet-700">{{ $day['citas']->count() }}</span>
                            @endif
                        </div>

                        <div class="mt-3 space-y-2">
                            @foreach($day['citas']->take(3) as $cita)
                                @php
                                    $estadoClasses = match ($cita->estado) {
                                        'atendida' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                                        'no_show' => 'border-orange-200 bg-orange-50 text-orange-800',
                                        'cancelada' => 'border-rose-200 bg-rose-50 text-rose-800',
                                        'confirmada' => 'border-blue-200 bg-blue-50 text-blue-800',
                                        default => 'border-amber-200 bg-amber-50 text-amber-800',
                                    };
                                @endphp
                                <article class="rounded-2xl border px-3 py-2 {{ $estadoClasses }}">
                                    <p class="text-xs font-black">{{ $cita->fecha_hora->format('H:i') }} · {{ $estadoLabels[$cita->estado] ?? $cita->estado }}</p>
                                    <p class="mt-1 truncate text-xs font-bold">{{ $cita->paciente?->nombre }} {{ $cita->paciente?->apellido }}</p>
                                    <p class="truncate text-[11px] font-semibold opacity-80">{{ $cita->servicio?->nombre ?? 'Sin servicio' }}</p>
                                </article>
                            @endforeach

                            @if($day['citas']->count() > 3)
                                <p class="text-xs font-bold text-slate-400">+{{ $day['citas']->count() - 3 }} citas más</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60 lg:hidden">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-extrabold text-slate-950">Agenda del mes</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Vista optimizada para móviles.</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($citas as $cita)
                    <article class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-black text-slate-950">{{ $cita->fecha_hora->format('d/m/Y H:i') }}</p>
                                <p class="mt-1 text-sm font-semibold text-slate-700">{{ $cita->paciente?->nombre }} {{ $cita->paciente?->apellido }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">{{ $cita->medico?->nombre }} {{ $cita->medico?->apellido }} · {{ $cita->servicio?->nombre ?? 'Sin servicio' }}</p>
                            </div>
                            <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-black text-violet-700">{{ $estadoLabels[$cita->estado] ?? $cita->estado }}</span>
                        </div>
                    </article>
                @empty
                    <div class="px-5 py-12 text-center">
                        <p class="text-sm font-semibold text-slate-500">No hay citas en este mes.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
