<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Equipo clínico</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Médicos</h1>
            </div>
            @can('medicos.crear')
                <a href="{{ route('medicos.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                    Nuevo médico
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

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60">
            <div class="flex flex-col gap-2 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-950">Lista de médicos</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Gestiona especialistas, servicios asignados y usuario vinculado.</p>
                </div>
                <span class="rounded-full bg-violet-50 px-4 py-2 text-sm font-bold text-violet-700">{{ $medicos->count() }} registros</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Médico</th>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Especialidad</th>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Servicios</th>
                            <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Usuario</th>
                            <th class="px-6 py-4 text-right text-xs font-extrabold uppercase tracking-wider text-slate-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($medicos as $medico)
                            <tr class="transition hover:bg-violet-50/40">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-sm font-extrabold text-violet-700">
                                            {{ strtoupper(substr($medico->nombre, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-950">{{ $medico->nombre }} {{ $medico->apellido }}</p>
                                            <p class="text-sm font-medium text-slate-500">{{ $medico->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $medico->especialidad }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-600">
                                    @if($medico->servicios->isNotEmpty())
                                        {{ $medico->servicios->pluck('nombre')->join(', ') }}
                                    @else
                                        <span class="font-bold text-amber-700">Sin servicios asignados</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-600">
                                    {{ $medico->user?->email ?? 'Sin vincular' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        @can('medicos.editar')
                                            <a href="{{ route('medicos.edit', $medico->id) }}" class="rounded-xl bg-violet-50 px-3 py-2 text-xs font-bold text-violet-700 transition hover:bg-violet-100">Editar</a>
                                        @endcan
                                        @can('medicos.eliminar')
                                            <form action="{{ route('medicos.destroy', $medico->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este médico?');">
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
                                    <p class="text-sm font-semibold text-slate-500">Aún no hay médicos registrados.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
