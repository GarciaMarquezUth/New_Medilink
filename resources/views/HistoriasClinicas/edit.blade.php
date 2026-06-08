<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Historia clínica</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Cita #{{ $cita->id }}</h1>
            </div>
            <a href="{{ route('citas.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Volver a citas
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6">
        @if(session('success'))
            <div class="rounded-3xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8">
            <div class="grid gap-5 lg:grid-cols-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">Paciente</p>
                    <p class="mt-1 text-base font-extrabold text-slate-950">{{ $cita->paciente?->nombre }} {{ $cita->paciente?->apellido }}</p>
                    <a href="{{ route('medicos.pacientes.show', $cita->paciente_id) }}" class="mt-2 inline-flex text-sm font-bold text-violet-700 hover:text-violet-900">Ver paciente</a>
                </div>
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">Servicio</p>
                    <p class="mt-1 text-base font-extrabold text-slate-950">{{ $cita->servicio?->nombre ?? 'Servicio no especificado' }}</p>
                    @if($cita->servicio)
                        <p class="mt-1 text-sm font-semibold text-slate-500">{{ $cita->servicio->duracion_minutos }} min</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">Fecha y hora</p>
                    <p class="mt-1 text-base font-extrabold text-slate-950">{{ $cita->fecha_hora->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">Estado</p>
                    <p class="mt-1 text-base font-extrabold text-slate-950">{{ \App\Models\Cita::estados()[$cita->estado] ?? $cita->estado }}</p>
                </div>
            </div>

            <div class="mt-6 rounded-2xl bg-slate-50 px-5 py-4">
                <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">Motivo de consulta</p>
                <p class="mt-1 text-sm font-semibold leading-6 text-slate-700">{{ $cita->motivo }}</p>
            </div>
        </section>

        <form action="{{ route('historias-clinicas.update', $cita->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8">
                <div class="mb-6">
                    <h2 class="text-lg font-extrabold text-slate-950">Notas de atención</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Registra diagnóstico, tratamiento, receta e indicaciones para esta cita.</p>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <div>
                        <x-input-label for="diagnostico" value="Diagnóstico" />
                        <textarea id="diagnostico" name="diagnostico" rows="5" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10">{{ old('diagnostico', $historia?->diagnostico) }}</textarea>
                        <x-input-error :messages="$errors->get('diagnostico')" />
                    </div>

                    <div>
                        <x-input-label for="tratamiento" value="Tratamiento" />
                        <textarea id="tratamiento" name="tratamiento" rows="5" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10">{{ old('tratamiento', $historia?->tratamiento) }}</textarea>
                        <x-input-error :messages="$errors->get('tratamiento')" />
                    </div>

                    <div>
                        <x-input-label for="receta" value="Receta médica" />
                        <textarea id="receta" name="receta" rows="5" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10">{{ old('receta', $historia?->receta) }}</textarea>
                        <x-input-error :messages="$errors->get('receta')" />
                    </div>

                    <div>
                        <x-input-label for="indicaciones" value="Indicaciones para el paciente" />
                        <textarea id="indicaciones" name="indicaciones" rows="5" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10">{{ old('indicaciones', $historia?->indicaciones) }}</textarea>
                        <x-input-error :messages="$errors->get('indicaciones')" />
                    </div>

                    <div class="lg:col-span-2">
                        <x-input-label for="observaciones" value="Observaciones internas" />
                        <textarea id="observaciones" name="observaciones" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10">{{ old('observaciones', $historia?->observaciones) }}</textarea>
                        <x-input-error :messages="$errors->get('observaciones')" />
                    </div>

                    <div>
                        <x-input-label for="seguimiento_fecha" value="Fecha sugerida de seguimiento" />
                        <x-text-input id="seguimiento_fecha" type="date" name="seguimiento_fecha" value="{{ old('seguimiento_fecha', $historia?->seguimiento_fecha?->format('Y-m-d')) }}" class="mt-2" />
                        <x-input-error :messages="$errors->get('seguimiento_fecha')" />
                    </div>
                </div>

                @if(in_array($cita->estado, $estadosOcupantes, true))
                    <label class="mt-6 flex items-start gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800">
                        <input type="checkbox" name="marcar_atendida" value="1" class="mt-1 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                        <span>Marcar esta cita como atendida al guardar la historia clínica.</span>
                    </label>
                @endif
            </section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('citas.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">Guardar historia clínica</button>
            </div>
        </form>
    </div>
</x-app-layout>
