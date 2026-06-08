<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Administración</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Reportes clínicos</h1>
            </div>
            <a href="{{ route('citas.calendar') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">Ver calendario</a>
        </div>
    </x-slot>

    @php
        $maxMedicos = max(1, collect($citasPorMedico)->max('count') ?? 1);
        $maxServicios = max(1, collect($citasPorServicio)->max('count') ?? 1);
    @endphp

    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <form method="GET" action="{{ route('reportes.index') }}" class="grid gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                <div>
                    <x-input-label for="fecha_desde" value="Desde" />
                    <x-text-input id="fecha_desde" type="date" name="fecha_desde" value="{{ $filters['fecha_desde'] }}" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="fecha_hasta" value="Hasta" />
                    <x-text-input id="fecha_hasta" type="date" name="fecha_hasta" value="{{ $filters['fecha_hasta'] }}" class="mt-2" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:bg-violet-700">Filtrar</button>
                    <a href="{{ route('reportes.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">Limpiar</a>
                </div>
            </form>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60">
                <p class="text-sm font-bold text-slate-500">Total de citas</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $totalCitas }}</p>
                <p class="mt-1 text-xs font-semibold text-violet-600">Rango seleccionado</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60">
                <p class="text-sm font-bold text-slate-500">Ingresos registrados</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-950">${{ number_format($ingresosRegistrados, 2) }}</p>
                <p class="mt-1 text-xs font-semibold text-violet-600">Según monto pagado</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60">
                <p class="text-sm font-bold text-slate-500">Citas pagadas</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $citasPagadas }}</p>
                <p class="mt-1 text-xs font-semibold text-violet-600">Estado de pago pagado</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60">
                <p class="text-sm font-bold text-slate-500">Pendientes de pago</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $citasPendientesPago }}</p>
                <p class="mt-1 text-xs font-semibold text-violet-600">Pendiente o parcial</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60">
                <h2 class="text-lg font-extrabold text-slate-950">Citas por estado</h2>
                <div class="mt-5 space-y-3">
                    @foreach($citasPorEstado as $estado)
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm font-bold text-slate-700">
                                <span>{{ $estado['label'] }}</span>
                                <span>{{ $estado['count'] }}</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-violet-600" style="width: {{ $totalCitas > 0 ? ($estado['count'] / $totalCitas) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60">
                <h2 class="text-lg font-extrabold text-slate-950">Citas por pago</h2>
                <div class="mt-5 space-y-3">
                    @foreach($citasPorPago as $estado)
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm font-bold text-slate-700">
                                <span>{{ $estado['label'] }}</span>
                                <span>{{ $estado['count'] }}</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-emerald-500" style="width: {{ $totalCitas > 0 ? ($estado['count'] / $totalCitas) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60">
                <h2 class="text-lg font-extrabold text-slate-950">Médicos con más citas</h2>
                <div class="mt-5 space-y-4">
                    @forelse($citasPorMedico as $item)
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm font-bold text-slate-700">
                                <span>{{ $item['label'] }}</span>
                                <span>{{ $item['count'] }}</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-blue-500" style="width: {{ ($item['count'] / $maxMedicos) * 100 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm font-semibold text-slate-500">No hay citas para este rango.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60">
                <h2 class="text-lg font-extrabold text-slate-950">Servicios más solicitados</h2>
                <div class="mt-5 space-y-4">
                    @forelse($citasPorServicio as $item)
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm font-bold text-slate-700">
                                <span>{{ $item['label'] }}</span>
                                <span>{{ $item['count'] }}</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-fuchsia-500" style="width: {{ ($item['count'] / $maxServicios) * 100 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm font-semibold text-slate-500">No hay servicios para este rango.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-lg font-extrabold text-slate-950">Citas recientes del rango</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Últimos registros incluidos en el reporte.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Fecha</th>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Paciente</th>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Médico</th>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Servicio</th>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Pago</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($citasRecientes as $cita)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-slate-800">{{ $cita->fecha_hora->format('d/m/Y H:i') }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-700">{{ $cita->paciente?->nombre }} {{ $cita->paciente?->apellido }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-700">{{ $cita->medico?->nombre }} {{ $cita->medico?->apellido }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-700">{{ $cita->servicio?->nombre ?? 'Sin servicio' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-slate-800">${{ number_format((float) ($cita->monto_pagado ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm font-semibold text-slate-500">No hay citas en este rango.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
