<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Agenda clínica</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Editar cita</h1>
        </div>
    </x-slot>

    <form action="{{ route('citas.update', $cita->id) }}" method="POST" class="mx-auto max-w-4xl space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-950">Reagendar o actualizar</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Modifica paciente, médico, servicio, fecha, motivo o estado de la cita.</p>
                </div>
                <span class="inline-flex rounded-full bg-violet-50 px-4 py-2 text-xs font-extrabold uppercase tracking-wider text-violet-700">Cita #{{ $cita->id }}</span>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="paciente_id" value="Paciente" />
                    <select id="paciente_id" name="paciente_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>
                        @foreach($pacientes as $p)
                            <option value="{{ $p->id }}" {{ (int) old('paciente_id', $cita->paciente_id) === $p->id ? 'selected' : '' }}>
                                {{ $p->nombre }} {{ $p->apellido }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('paciente_id')" />
                </div>

                <div>
                    <x-input-label for="medico_id" value="Médico" />
                    <select id="medico_id" name="medico_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>
                        @foreach($medicos as $m)
                            <option value="{{ $m->id }}" {{ (int) old('medico_id', $cita->medico_id) === $m->id ? 'selected' : '' }}>
                                {{ $m->nombre }} {{ $m->apellido }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('medico_id')" />
                </div>

                <div>
                    <x-input-label for="servicio_id" value="Servicio" />
                    <select id="servicio_id" name="servicio_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>
                        <option value="">Selecciona un servicio</option>
                        @foreach($servicios as $servicio)
                            <option value="{{ $servicio->id }}" {{ (int) old('servicio_id', $cita->servicio_id) === $servicio->id ? 'selected' : '' }}>
                                {{ $servicio->nombre }} · {{ $servicio->duracion_minutos }} min @if($servicio->precio !== null) · ${{ number_format((float) $servicio->precio, 2) }} @endif
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('servicio_id')" />
                </div>

                <div>
                    <x-input-label for="fecha_hora" value="Fecha y hora" />
                    <x-text-input id="fecha_hora" type="datetime-local" name="fecha_hora" value="{{ old('fecha_hora', $cita->fecha_hora ? \Illuminate\Support\Carbon::parse($cita->fecha_hora)->format('Y-m-d\TH:i') : '') }}" class="mt-2" required />
                    <x-input-error :messages="$errors->get('fecha_hora')" />
                </div>

                <div>
                    <x-input-label for="estado" value="Estado" />
                    <select id="estado" name="estado" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>
                        @foreach($estados as $estado => $label)
                            <option value="{{ $estado }}" {{ old('estado', $cita->estado) === $estado ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('estado')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="motivo" value="Motivo" />
                    <textarea id="motivo" name="motivo" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>{{ old('motivo', $cita->motivo) }}</textarea>
                    <x-input-error :messages="$errors->get('motivo')" />
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('citas.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">Cancelar</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">Actualizar cita</button>
        </div>
    </form>
</x-app-layout>
