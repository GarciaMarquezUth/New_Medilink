<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Catálogo clínico</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Servicios</h1>
            </div>
            @can('servicios.crear')
                <a href="{{ route('servicios.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                    Nuevo servicio
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('success'))
            <div class="rounded-3xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <form method="GET" action="{{ route('servicios.index') }}" class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_180px_auto] sm:items-end">
                <div>
                    <x-input-label for="q" value="Buscar servicio" />
                    <x-text-input id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="mt-2" placeholder="Nombre o descripción" />
                </div>
                <div>
                    <x-input-label for="activo" value="Estado" />
                    <select id="activo" name="activo" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm transition focus:border-violet-500 focus:ring-violet-500">
                        <option value="">Todos</option>
                        <option value="1" @selected(($filters['activo'] ?? '') === '1')>Activos</option>
                        <option value="0" @selected(($filters['activo'] ?? '') === '0')>Inactivos</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:bg-violet-700">Filtrar</button>
                    <a href="{{ route('servicios.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">Limpiar</a>
                </div>
            </form>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60">
            <div class="flex flex-col gap-2 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-950">Lista de servicios</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Define duración, precio y estado para agendar citas.</p>
                </div>
                <span class="rounded-full bg-violet-50 px-4 py-2 text-sm font-bold text-violet-700">{{ $servicios->total() }} registros</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Servicio</th>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Duración</th>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Precio</th>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Estado</th>
                            <th class="px-6 py-4 text-right text-xs font-extrabold uppercase tracking-wider text-slate-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($servicios as $servicio)
                            <tr class="transition hover:bg-violet-50/40">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-slate-950">{{ $servicio->nombre }}</p>
                                    <p class="text-sm font-medium text-slate-500">{{ $servicio->descripcion ?: 'Sin descripción' }}</p>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-700">{{ $servicio->duracion_minutos }} min</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-700">
                                    {{ $servicio->precio !== null ? '$'.number_format((float) $servicio->precio, 2) : 'Sin precio' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold ring-1 {{ $servicio->activo ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                        {{ $servicio->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        @can('servicios.editar')
                                            <a href="{{ route('servicios.edit', $servicio->id) }}" class="rounded-xl bg-violet-50 px-3 py-2 text-xs font-bold text-violet-700 transition hover:bg-violet-100">Editar</a>
                                        @endcan
                                        @can('servicios.eliminar')
                                            <form action="{{ route('servicios.destroy', $servicio->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este servicio?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100">Eliminar</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <p class="text-sm font-semibold text-slate-500">Aún no hay servicios registrados.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $servicios->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
