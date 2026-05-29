<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Agenda clínica</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Disponibilidades</h1>
            </div>

            @can('disponibilidades.crear')
                <a href="{{ route('disponibilidades.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700 focus:outline-none focus:ring-4 focus:ring-violet-100">
                    Vista avanzada
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-6 selection:bg-violet-100 selection:text-violet-950">
        @if(session('success'))
            <div class="rounded-3xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-3xl border border-rose-100 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700 shadow-sm">
                Revisa la disponibilidad semanal marcada. No se guardaron cambios.
            </div>
        @endif

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Disponibilidad semanal por médico</p>
                    <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Edita toda la semana desde una sola pantalla</h2>
                    <p class="mt-2 max-w-3xl text-sm font-medium leading-6 text-slate-500">
                        Cada card representa un médico. Al guardar, se actualiza o crea un registro por día sin borrar citas existentes.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full bg-violet-50 px-4 py-2 text-sm font-bold text-violet-700">{{ $medicos->count() }} médicos</span>
                    <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-bold text-slate-600">{{ $totalRegistros }} registros</span>
                </div>
            </div>
        </section>

        @forelse($medicos as $medico)
            @php
                $semana = $semanas[(string) $medico->id] ?? [];
                $formHasOld = (int) old('medico_id') === $medico->id;
                $editing = $formHasOld && $errors->any();
                $diasActivos = collect($semana)->where('activo', true)->count();
                $diasConMultiples = collect($semana)->filter(fn ($dia) => $dia['registros'] > 1 || $dia['activos'] > 1)->count();
            @endphp

            <form action="{{ route('disponibilidades.store') }}" method="POST" class="rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60" data-weekly-availability-card data-initial-editing="{{ $editing ? 'true' : 'false' }}">
                @csrf
                <input type="hidden" name="medico_id" value="{{ $medico->id }}">
                <input type="hidden" name="redirect_to" value="index">

                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="flex min-w-0 gap-4">
                            <x-medico-avatar :medico="$medico" class="h-16 w-16 rounded-2xl" text-class="text-xl" />
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex rounded-full bg-violet-50 px-3 py-1 text-xs font-extrabold uppercase tracking-[0.18em] text-violet-700 ring-1 ring-violet-100">Médico</span>
                                    @if($diasConMultiples > 0)
                                        <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-extrabold text-amber-700 ring-1 ring-amber-100">{{ $diasConMultiples }} días con registros extra</span>
                                    @endif
                                </div>
                                <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-950">{{ $medico->nombre }} {{ $medico->apellido }}</h2>
                                <p class="mt-1 text-sm font-semibold text-slate-500">{{ $medico->especialidad ?: 'Sin especialidad registrada' }}</p>
                                <p class="mt-3 text-sm font-medium leading-6 text-slate-500">
                                    {{ $diasActivos }} de 7 días activos. Los días inactivos no aparecerán como fechas disponibles para agendar.
                                </p>
                                @if($formHasOld)
                                    <x-input-error :messages="$errors->get('medico_id')" />
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row lg:shrink-0">
                            @can('disponibilidades.crear')
                                <button type="button" class="inline-flex select-none items-center justify-center rounded-2xl border border-violet-200 bg-violet-50 px-5 py-3 text-sm font-black text-violet-700 transition hover:-translate-y-0.5 hover:bg-violet-100 focus:outline-none focus:ring-4 focus:ring-violet-100" data-edit-week>
                                    Editar semana
                                </button>
                                <button type="submit" class="inline-flex select-none items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700 focus:outline-none focus:ring-4 focus:ring-violet-200 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 disabled:shadow-none disabled:hover:translate-y-0" data-save-week @disabled(! $editing)>
                                    Guardar cambios
                                </button>
                            @else
                                <span class="inline-flex rounded-2xl bg-slate-100 px-5 py-3 text-sm font-black text-slate-500">Solo lectura</span>
                            @endcan
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 p-5 sm:p-6 lg:grid-cols-7" data-medico-card="{{ $medico->id }}">
                    @foreach($diasSemana as $dia => $nombre)
                        @php
                            $config = $semana[$dia];
                            $activo = $formHasOld ? filter_var(old("dias.$dia.activo", $config['activo']), FILTER_VALIDATE_BOOLEAN) : $config['activo'];
                            $horaInicio = $formHasOld ? old("dias.$dia.hora_inicio", $config['hora_inicio']) : $config['hora_inicio'];
                            $horaFin = $formHasOld ? old("dias.$dia.hora_fin", $config['hora_fin']) : $config['hora_fin'];
                        @endphp

                        <article class="rounded-3xl border bg-white p-4 transition" data-day-card data-day="{{ $dia }}">
                            <div class="flex items-start justify-between gap-3 lg:block">
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-violet-600">{{ $nombre }}</p>
                                    <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-extrabold ring-1" data-active-label></span>
                                </div>

                                <label class="inline-flex select-none items-center gap-2 rounded-full bg-slate-50 p-1 pr-2 text-xs font-extrabold text-slate-600 ring-1 ring-slate-200 transition lg:mt-4" data-toggle-shell>
                                    <input type="hidden" name="dias[{{ $dia }}][activo]" value="0">
                                    <input type="checkbox" name="dias[{{ $dia }}][activo]" value="1" class="sr-only" data-active-toggle data-editable-input @checked($activo) @disabled(! $editing)>
                                    <span class="relative h-6 w-11 rounded-full bg-slate-300 transition" data-toggle-track>
                                        <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow transition" data-toggle-thumb></span>
                                    </span>
                                    <span data-toggle-text>Inactivo</span>
                                </label>
                            </div>

                            <div class="mt-4 space-y-3">
                                <div>
                                    <label for="medico_{{ $medico->id }}_dia_{{ $dia }}_inicio" class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Hora inicio</label>
                                    <input id="medico_{{ $medico->id }}_dia_{{ $dia }}_inicio" type="time" name="dias[{{ $dia }}][hora_inicio]" value="{{ $horaInicio }}" class="mt-1 block w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10 disabled:bg-slate-100 disabled:text-slate-500" data-time-input data-editable-input @disabled(! $editing)>
                                    @if($formHasOld)
                                        <x-input-error :messages="$errors->get('dias.'.$dia.'.hora_inicio')" />
                                    @endif
                                </div>

                                <div>
                                    <label for="medico_{{ $medico->id }}_dia_{{ $dia }}_fin" class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Hora fin</label>
                                    <input id="medico_{{ $medico->id }}_dia_{{ $dia }}_fin" type="time" name="dias[{{ $dia }}][hora_fin]" value="{{ $horaFin }}" class="mt-1 block w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10 disabled:bg-slate-100 disabled:text-slate-500" data-time-input data-editable-input @disabled(! $editing)>
                                    @if($formHasOld)
                                        <x-input-error :messages="$errors->get('dias.'.$dia.'.hora_fin')" />
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </form>
        @empty
            <section class="rounded-3xl border border-violet-200 bg-violet-50 p-8 text-center shadow-sm">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-xl font-black text-violet-700 shadow-sm">i</div>
                <h2 class="mt-4 text-xl font-black text-violet-950">No hay médicos disponibles</h2>
                <p class="mx-auto mt-2 max-w-xl text-sm font-semibold leading-6 text-violet-800">Registra médicos o revisa los permisos del usuario actual para gestionar disponibilidades.</p>
            </section>
        @endforelse
    </div>

    <script>
        function initDisponibilidadesIndex() {
            document.querySelectorAll('[data-weekly-availability-card]').forEach((form) => {
                if (form.dataset.enhanced === 'true') {
                    return;
                }

                form.dataset.enhanced = 'true';

                const editButton = form.querySelector('[data-edit-week]');
                const saveButton = form.querySelector('[data-save-week]');

                const updateDay = (card) => {
                    const active = card.querySelector('[data-active-toggle]').checked;
                    const editing = form.dataset.editing === 'true';
                    const status = card.querySelector('[data-active-label]');
                    const shell = card.querySelector('[data-toggle-shell]');
                    const track = card.querySelector('[data-toggle-track]');
                    const thumb = card.querySelector('[data-toggle-thumb]');
                    const text = card.querySelector('[data-toggle-text]');

                    card.classList.toggle('border-violet-200', active);
                    card.classList.toggle('bg-violet-50/40', active);
                    card.classList.toggle('border-slate-200', ! active);
                    card.classList.toggle('opacity-75', ! active && ! editing);

                    status.textContent = active ? 'Activo' : 'Inactivo';
                    status.className = active
                        ? 'mt-2 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-extrabold text-emerald-700 ring-1 ring-emerald-100'
                        : 'mt-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-extrabold text-slate-600 ring-1 ring-slate-200';

                    shell.classList.toggle('bg-violet-50', active);
                    shell.classList.toggle('text-violet-700', active);
                    shell.classList.toggle('ring-violet-100', active);
                    shell.classList.toggle('bg-slate-50', ! active);
                    shell.classList.toggle('text-slate-600', ! active);
                    shell.classList.toggle('ring-slate-200', ! active);

                    track.classList.toggle('bg-violet-600', active);
                    track.classList.toggle('bg-slate-300', ! active);
                    thumb.classList.toggle('translate-x-5', active);
                    text.textContent = active ? 'Activo' : 'Inactivo';
                };

                const setEditing = (editing) => {
                    form.dataset.editing = editing ? 'true' : 'false';

                    form.querySelectorAll('[data-editable-input]').forEach((input) => {
                        input.disabled = ! editing;
                    });

                    form.querySelectorAll('[data-time-input]').forEach((input) => {
                        input.required = editing;
                    });

                    if (saveButton) {
                        saveButton.disabled = ! editing;
                    }

                    if (editButton) {
                        editButton.textContent = editing ? 'Editando semana' : 'Editar semana';
                        editButton.classList.toggle('bg-violet-100', editing);
                    }

                    form.querySelectorAll('[data-day-card]').forEach(updateDay);
                };

                editButton?.addEventListener('click', () => {
                    setEditing(true);
                    form.querySelector('[data-time-input]')?.focus();
                });

                form.querySelectorAll('[data-active-toggle]').forEach((toggle) => {
                    toggle.addEventListener('change', () => updateDay(toggle.closest('[data-day-card]')));
                });

                form.addEventListener('submit', (event) => {
                    if (form.dataset.editing !== 'true') {
                        event.preventDefault();
                    }
                });

                setEditing(form.dataset.initialEditing === 'true');
            });
        }

        document.addEventListener('DOMContentLoaded', initDisponibilidadesIndex);
        document.addEventListener('livewire:navigated', initDisponibilidadesIndex);
    </script>
</x-app-layout>
