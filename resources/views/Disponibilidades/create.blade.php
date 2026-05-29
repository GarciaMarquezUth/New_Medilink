@php
    $diasConMultiples = collect($semana)->filter(fn ($dia) => $dia['registros'] > 1 || $dia['activos'] > 1);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Agenda clínica</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Registrar disponibilidad semanal</h1>
            </div>

            <a href="{{ route('disponibilidades.index') }}" class="inline-flex select-none items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-violet-100">
                Ver registros
            </a>
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
                Revisa los horarios marcados. No se guardaron cambios.
            </div>
        @endif

        @if($diasConMultiples->isNotEmpty())
            <div class="rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-800 shadow-sm">
                Hay días con más de un bloque registrado. Al guardar desde esta pantalla se conservarán los registros, pero solo quedará activo un bloque por día.
            </div>
        @endif

        <form action="{{ route('disponibilidades.store') }}" method="POST" class="space-y-6" data-weekly-availability-form data-create-url="{{ route('disponibilidades.create') }}">
            @csrf

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60 sm:p-6 lg:p-8">
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-end">
                    <div>
                        <span class="inline-flex select-none rounded-full bg-violet-50 px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] text-violet-700 ring-1 ring-violet-100">Semana completa</span>
                        <h2 class="mt-4 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Configura la disponibilidad de toda la semana</h2>
                        <p class="mt-3 max-w-3xl text-sm font-medium leading-6 text-slate-500">Selecciona un médico, activa los días disponibles y define los rangos horarios. Las citas existentes se validan antes de guardar.</p>
                    </div>

                    <div>
                        <x-input-label for="medico_id" value="Médico" />
                        <select id="medico_id" name="medico_id" data-medico-select class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>
                            <option value="">Selecciona un médico</option>
                            @foreach($medicos as $medico)
                                <option value="{{ $medico->id }}" {{ (int) old('medico_id', $selectedMedicoId) === $medico->id ? 'selected' : '' }}>
                                    {{ $medico->nombre }} {{ $medico->apellido }} - {{ $medico->especialidad }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('medico_id')" />
                        <p class="mt-2 text-xs font-semibold text-slate-400">Al cambiar el médico se cargan sus horarios actuales.</p>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-violet-100 bg-violet-50/60 p-5 shadow-sm shadow-violet-100/60 sm:p-6">
                <div class="grid gap-5 xl:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] xl:items-end">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Copiar horario</p>
                        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">Usa un día como plantilla</h2>
                        <p class="mt-1 text-sm font-medium leading-6 text-slate-500">Elige un día origen y selecciona los días destino. Se copiarán estado, inicio y fin.</p>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-[220px_minmax(0,1fr)_auto] lg:items-end">
                        <div>
                            <label for="copy_source_day" class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Día origen</label>
                            <select id="copy_source_day" data-copy-source class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10">
                                @foreach($diasSemana as $dia => $nombre)
                                    <option value="{{ $dia }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Días destino</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($diasSemana as $dia => $nombre)
                                    <label class="inline-flex select-none items-center gap-2 rounded-2xl border border-white bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:border-violet-200 hover:text-violet-700">
                                        <input type="checkbox" value="{{ $dia }}" data-copy-target class="h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                                        {{ $nombre }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <button type="button" data-copy-button class="inline-flex select-none items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700 focus:outline-none focus:ring-4 focus:ring-violet-200">
                            Copiar
                        </button>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($semana as $dia => $config)
                    @php
                        $activo = filter_var(old("dias.$dia.activo", $config['activo']), FILTER_VALIDATE_BOOLEAN);
                        $horaInicio = old("dias.$dia.hora_inicio", $config['hora_inicio']);
                        $horaFin = old("dias.$dia.hora_fin", $config['hora_fin']);
                    @endphp

                    <article class="rounded-3xl border bg-white p-5 shadow-sm shadow-slate-200/60 transition" data-day-card data-day="{{ $dia }}">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">{{ $config['nombre'] }}</p>
                                <h3 class="mt-1 text-lg font-black text-slate-950">Disponibilidad</h3>
                                @if($config['registros'] > 1)
                                    <p class="mt-1 text-xs font-bold text-amber-600">{{ $config['registros'] }} bloques registrados</p>
                                @else
                                    <p class="mt-1 text-xs font-semibold text-slate-400">Un bloque horario</p>
                                @endif
                            </div>

                            <label class="inline-flex select-none items-center gap-3 rounded-full bg-slate-50 p-1 pr-3 text-xs font-extrabold text-slate-600 ring-1 ring-slate-200 transition" data-toggle-shell>
                                <input type="hidden" name="dias[{{ $dia }}][activo]" value="0">
                                <input type="checkbox" name="dias[{{ $dia }}][activo]" value="1" class="sr-only" data-active-toggle @checked($activo)>
                                <span class="relative h-7 w-12 rounded-full bg-slate-300 transition" data-toggle-track>
                                    <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition" data-toggle-thumb></span>
                                </span>
                                <span data-toggle-text>Activo</span>
                            </label>
                        </div>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="dia_{{ $dia }}_hora_inicio" class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Hora inicio</label>
                                <input id="dia_{{ $dia }}_hora_inicio" type="time" name="dias[{{ $dia }}][hora_inicio]" value="{{ $horaInicio }}" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" data-start-time>
                                <x-input-error :messages="$errors->get('dias.'.$dia.'.hora_inicio')" />
                            </div>

                            <div>
                                <label for="dia_{{ $dia }}_hora_fin" class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Hora fin</label>
                                <input id="dia_{{ $dia }}_hora_fin" type="time" name="dias[{{ $dia }}][hora_fin]" value="{{ $horaFin }}" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" data-end-time>
                                <x-input-error :messages="$errors->get('dias.'.$dia.'.hora_fin')" />
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                            <span class="inline-flex select-none rounded-full px-3 py-1 text-xs font-extrabold ring-1" data-active-label></span>
                            <span class="text-xs font-semibold text-slate-400">Editable sin afectar otros días</span>
                        </div>
                    </article>
                @endforeach
            </section>

            <div class="sticky bottom-4 z-10 rounded-3xl border border-slate-200 bg-white/95 p-4 shadow-xl shadow-slate-900/10 backdrop-blur sm:flex sm:items-center sm:justify-between sm:gap-4">
                <p class="mb-3 text-sm font-semibold text-slate-500 sm:mb-0">Guarda los cambios de la semana completa para el médico seleccionado.</p>
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('disponibilidades.index') }}" class="inline-flex select-none items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-violet-100">Cancelar</a>
                    <button type="submit" class="inline-flex select-none items-center justify-center rounded-2xl bg-violet-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700 focus:outline-none focus:ring-4 focus:ring-violet-200">Guardar disponibilidad</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function initDisponibilidadSemana() {
            document.querySelectorAll('[data-weekly-availability-form]').forEach((form) => {
                if (form.dataset.enhanced === 'true') {
                    return;
                }

                form.dataset.enhanced = 'true';

                const updateCard = (card) => {
                    const active = card.querySelector('[data-active-toggle]').checked;
                    const status = card.querySelector('[data-active-label]');
                    const shell = card.querySelector('[data-toggle-shell]');
                    const track = card.querySelector('[data-toggle-track]');
                    const thumb = card.querySelector('[data-toggle-thumb]');
                    const text = card.querySelector('[data-toggle-text]');
                    const inputs = card.querySelectorAll('[data-start-time], [data-end-time]');

                    card.classList.toggle('border-violet-200', active);
                    card.classList.toggle('border-slate-200', ! active);
                    card.classList.toggle('ring-2', active);
                    card.classList.toggle('ring-violet-100', active);

                    status.textContent = active ? 'Activo' : 'Desactivado';
                    status.className = active
                        ? 'inline-flex select-none rounded-full bg-emerald-50 px-3 py-1 text-xs font-extrabold text-emerald-700 ring-1 ring-emerald-100'
                        : 'inline-flex select-none rounded-full bg-slate-100 px-3 py-1 text-xs font-extrabold text-slate-600 ring-1 ring-slate-200';

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

                    inputs.forEach((input) => {
                        input.required = active;
                    });
                };

                form.querySelectorAll('[data-day-card]').forEach((card) => {
                    card.querySelector('[data-active-toggle]').addEventListener('change', () => updateCard(card));
                    updateCard(card);
                });

                form.querySelector('[data-medico-select]')?.addEventListener('change', (event) => {
                    const medicoId = event.target.value;
                    const url = form.dataset.createUrl;

                    window.location.href = medicoId ? `${url}?medico_id=${encodeURIComponent(medicoId)}` : url;
                });

                form.querySelector('[data-copy-button]')?.addEventListener('click', () => {
                    const sourceDay = form.querySelector('[data-copy-source]').value;
                    const sourceCard = form.querySelector(`[data-day-card][data-day="${sourceDay}"]`);
                    const targets = Array.from(form.querySelectorAll('[data-copy-target]:checked'));

                    if (! sourceCard || targets.length === 0) {
                        return;
                    }

                    const sourceActive = sourceCard.querySelector('[data-active-toggle]').checked;
                    const sourceStart = sourceCard.querySelector('[data-start-time]').value;
                    const sourceEnd = sourceCard.querySelector('[data-end-time]').value;

                    targets.forEach((target) => {
                        if (target.value === sourceDay) {
                            return;
                        }

                        const targetCard = form.querySelector(`[data-day-card][data-day="${target.value}"]`);

                        if (! targetCard) {
                            return;
                        }

                        targetCard.querySelector('[data-active-toggle]').checked = sourceActive;
                        targetCard.querySelector('[data-start-time]').value = sourceStart;
                        targetCard.querySelector('[data-end-time]').value = sourceEnd;
                        updateCard(targetCard);
                    });
                });
            });
        }

        document.addEventListener('DOMContentLoaded', initDisponibilidadSemana);
        document.addEventListener('livewire:navigated', initDisponibilidadSemana);
    </script>
</x-app-layout>
