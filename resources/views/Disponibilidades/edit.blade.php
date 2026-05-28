<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Agenda clínica</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Editar disponibilidad</h1>
        </div>
    </x-slot>

    <form action="{{ route('disponibilidades.update', $disponibilidad->id) }}" method="POST" class="mx-auto max-w-4xl space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-950">Horario disponible</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Actualiza el bloque semanal del médico.</p>
                </div>
                <span class="inline-flex rounded-full bg-violet-50 px-4 py-2 text-xs font-extrabold uppercase tracking-wider text-violet-700">Horario #{{ $disponibilidad->id }}</span>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input-label for="medico_id" value="Médico" />
                    <select id="medico_id" name="medico_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>
                        <option value="">Selecciona un médico</option>
                        @foreach($medicos as $medico)
                            <option value="{{ $medico->id }}" {{ (int) old('medico_id', $disponibilidad->medico_id) === $medico->id ? 'selected' : '' }}>
                                {{ $medico->nombre }} {{ $medico->apellido }} - {{ $medico->especialidad }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('medico_id')" />
                </div>

                <div>
                    <x-input-label for="dia_semana" value="Día de la semana" />
                    <select id="dia_semana" name="dia_semana" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>
                        <option value="">Selecciona un día</option>
                        @foreach($diasSemana as $dia => $nombre)
                            <option value="{{ $dia }}" {{ (int) old('dia_semana', $disponibilidad->dia_semana) === $dia ? 'selected' : '' }}>{{ $nombre }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('dia_semana')" />
                </div>

                <div class="flex items-end">
                    <label for="activo" class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
                        <input id="activo" type="checkbox" name="activo" value="1" class="rounded border-slate-300 text-violet-600 shadow-sm focus:ring-violet-500" @checked(old('activo', $disponibilidad->activo))>
                        Disponibilidad activa
                    </label>
                </div>

                <div>
                    <x-input-label for="hora_inicio" value="Hora inicio" />
                    <x-text-input id="hora_inicio" type="time" name="hora_inicio" value="{{ old('hora_inicio', substr($disponibilidad->hora_inicio, 0, 5)) }}" class="mt-2" required />
                    <x-input-error :messages="$errors->get('hora_inicio')" />
                </div>

                <div>
                    <x-input-label for="hora_fin" value="Hora fin" />
                    <x-text-input id="hora_fin" type="time" name="hora_fin" value="{{ old('hora_fin', substr($disponibilidad->hora_fin, 0, 5)) }}" class="mt-2" required />
                    <x-input-error :messages="$errors->get('hora_fin')" />
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('disponibilidades.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">Cancelar</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">Actualizar disponibilidad</button>
        </div>
    </form>
</x-app-layout>
