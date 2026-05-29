<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Portal del paciente</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Agendar nueva cita</h1>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Volver a mis citas
            </a>
        </div>
    </x-slot>

    @php
        $user = Auth::user();
        $paciente = \App\Models\Paciente::where('user_id', $user?->id)->first();
        $selectedMedico = $medicos->firstWhere('id', (int) $selectedMedicoId);
        $selectedServicio = $servicios->firstWhere('id', (int) $selectedServicioId);
        $selectedFechaLabel = collect($fechasDisponibles)->firstWhere('value', $selectedFecha)['label'] ?? $selectedFecha;
        $selectedHorarioLabel = collect($horarios)->firstWhere('value', $selectedHorario)['label'] ?? null;
        $selectionComplete = $selectedMedicoId && $selectedServicioId && $selectedFecha;
        $canChooseDate = $selectedMedicoId && $selectedServicioId;
    @endphp

    <div class="mx-auto max-w-6xl space-y-6">
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

        @if($errors->any())
            <div class="rounded-3xl border border-rose-100 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700 shadow-sm">
                <p class="font-extrabold">No se pudo agendar la cita.</p>
                <p class="mt-1">Revisa la selección. El horario puede haberse ocupado recientemente.</p>
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <form
                id="patient-appointment-form"
                action="{{ route('pacientes.citas.store') }}"
                method="POST"
                class="space-y-6"
                data-servicios-url-template="{{ route('pacientes.citas.servicios', ['medico' => '__MEDICO__']) }}"
                data-fechas-url-template="{{ route('pacientes.citas.fechas', ['medico' => '__MEDICO__', 'servicio' => '__SERVICIO__']) }}"
                data-horarios-url-template="{{ route('pacientes.citas.horarios', ['medico' => '__MEDICO__', 'servicio' => '__SERVICIO__', 'fecha' => '__FECHA__']) }}"
            >
                @csrf
                <input id="appointment-fecha" type="hidden" name="fecha" value="{{ $selectedFecha }}">

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8">
                    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Paso 1</p>
                            <h2 class="mt-1 text-xl font-extrabold text-slate-950">Elige médico y servicio</h2>
                            <p class="mt-1 text-sm font-medium text-slate-500">Esta es una reserva interna vinculada a tu usuario. No necesitas escribir tus datos personales.</p>
                        </div>
                        <span class="inline-flex w-fit rounded-full bg-violet-50 px-4 py-2 text-xs font-extrabold uppercase tracking-wide text-violet-700">Paciente autenticado</span>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <x-input-label for="medico_id" value="Médico" />
                            <select id="medico_id" name="medico_id" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-sm font-bold text-slate-800 shadow-sm transition focus:border-violet-500 focus:ring-violet-500" required>
                                <option value="">Selecciona médico</option>
                                @foreach($medicos as $medico)
                                    <option value="{{ $medico->id }}" {{ (int) $selectedMedicoId === $medico->id ? 'selected' : '' }}>
                                        {{ $medico->nombre }} {{ $medico->apellido }} @if($medico->especialidad) - {{ $medico->especialidad }} @endif
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('medico_id')" />
                        </div>

                        <div>
                            <x-input-label for="servicio_id" value="Servicio" />
                            <select id="servicio_id" name="servicio_id" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-sm font-bold text-slate-800 shadow-sm transition focus:border-violet-500 focus:ring-violet-500" required {{ ! $selectedMedicoId || $servicios->isEmpty() ? 'disabled' : '' }}>
                                <option value="">{{ $selectedMedicoId ? 'Selecciona servicio' : 'Selecciona primero un médico' }}</option>
                                @foreach($servicios as $servicio)
                                    <option value="{{ $servicio->id }}" {{ (int) $selectedServicioId === $servicio->id ? 'selected' : '' }}>
                                        {{ $servicio->nombre }} - {{ $servicio->duracion_minutos }} min @if($servicio->precio !== null) - ${{ number_format((float) $servicio->precio, 2) }} @endif
                                    </option>
                                @endforeach
                            </select>
                            <p id="sin-servicios-medico" class="{{ $selectedMedicoId && $servicios->isEmpty() ? '' : 'hidden' }} mt-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-800">Este médico no tiene servicios disponibles.</p>
                            <x-input-error :messages="$errors->get('servicio_id')" />
                        </div>
                    </div>
                </section>

                <section id="fechas-section" class="{{ $canChooseDate ? '' : 'hidden' }} rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8">
                    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Paso 2</p>
                            <h2 class="mt-1 text-xl font-extrabold text-slate-950">Selecciona una fecha disponible</h2>
                        </div>
                        <span class="text-sm font-bold text-slate-500">Solo mostramos días con horarios reales.</span>
                    </div>

                    <div id="fechas-grid" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach($fechasDisponibles as $fechaDisponible)
                            @php
                                $selectedDateCard = $selectedFecha === $fechaDisponible['value'];
                            @endphp
                            <button type="button" data-date-value="{{ $fechaDisponible['value'] }}" data-date-label="{{ $fechaDisponible['label'] }}" class="group flex min-h-28 flex-col justify-between rounded-3xl border px-4 py-3 text-left shadow-sm transition focus:outline-none focus:ring-4 focus:ring-violet-200 {{ $selectedDateCard ? 'border-violet-700 bg-violet-700 text-white shadow-lg shadow-violet-700/20' : 'border-slate-200 bg-white text-slate-800 hover:-translate-y-0.5 hover:border-violet-300 hover:shadow-md' }}">
                                <span class="text-xs font-black uppercase tracking-[0.18em] {{ $selectedDateCard ? 'text-violet-100' : 'text-violet-600' }}">{{ $fechaDisponible['short_day'] ?? '' }}</span>
                                <span class="text-3xl font-black leading-none">{{ $fechaDisponible['day_number'] ?? \Carbon\Carbon::parse($fechaDisponible['value'])->format('d') }}</span>
                                <span class="text-[11px] font-extrabold {{ $selectedDateCard ? 'text-violet-100' : 'text-slate-500' }}">{{ $fechaDisponible['slots_count'] }} horarios disponibles</span>
                            </button>
                        @endforeach
                    </div>

                    <div id="no-fechas-message" class="{{ $canChooseDate && count($fechasDisponibles) === 0 ? '' : 'hidden' }} rounded-2xl border border-violet-200 bg-violet-50 p-5 text-center">
                        <p class="text-sm font-bold text-violet-800">No hay fechas disponibles para este médico y servicio.</p>
                    </div>
                    <x-input-error :messages="$errors->get('fecha')" />
                </section>

                <section id="horarios-section" class="{{ $selectionComplete ? '' : 'hidden' }} rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8">
                    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Paso 3</p>
                            <h2 class="mt-1 text-xl font-extrabold text-slate-950">Elige horario</h2>
                        </div>
                        <span class="text-sm font-bold text-slate-500">Validaremos nuevamente antes de guardar.</span>
                    </div>

                    <div id="horarios-grid" class="grid gap-3 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach($horarios as $slot)
                            <label class="block select-none">
                                <input type="radio" name="horario" value="{{ $slot['value'] }}" class="peer sr-only" {{ $selectedHorario === $slot['value'] ? 'checked' : '' }} required>
                                <span class="flex min-h-16 cursor-pointer flex-col items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-center shadow-sm transition hover:border-violet-300 hover:bg-white peer-checked:border-violet-700 peer-checked:bg-violet-700 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-violet-700/20 peer-focus:ring-4 peer-focus:ring-violet-200">
                                    <span class="text-base font-black leading-none">{{ $slot['label'] }}</span>
                                    <span class="mt-1 text-[11px] font-bold opacity-75">Termina {{ $slot['ends_at'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div id="no-horarios-message" class="{{ $selectionComplete && count($horarios) === 0 ? '' : 'hidden' }} rounded-2xl border border-violet-200 bg-violet-50 p-5 text-center">
                        <p class="text-sm font-bold text-violet-800">No hay horarios disponibles para esta fecha.</p>
                    </div>
                    <x-input-error :messages="$errors->get('horario')" />
                </section>

                <section id="motivo-section" class="{{ count($horarios) > 0 || $selectedHorario ? '' : 'hidden' }} rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8">
                    <div class="mb-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Paso 4</p>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-950">Motivo de consulta</h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">Describe brevemente el motivo. Tus datos personales se tomarán de tu cuenta.</p>
                    </div>

                    <textarea id="motivo" name="motivo" rows="4" class="block w-full resize-none rounded-2xl border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800 shadow-sm transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-violet-500" required>{{ old('motivo') }}</textarea>
                    <x-input-error :messages="$errors->get('motivo')" />

                    <button id="confirm-button" type="submit" class="mt-5 inline-flex w-full items-center justify-center rounded-2xl bg-violet-600 px-6 py-4 text-base font-black text-white shadow-xl shadow-violet-600/25 transition hover:-translate-y-0.5 hover:bg-violet-700 focus:outline-none focus:ring-4 focus:ring-violet-200 disabled:cursor-not-allowed disabled:opacity-60" {{ $selectedHorario ? '' : 'disabled' }}>
                        Confirmar cita
                    </button>
                </section>
            </form>

            <aside class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Paciente</p>
                    <h2 class="mt-2 text-xl font-extrabold text-slate-950">{{ $paciente?->nombre ?: $user?->name }} {{ $paciente?->apellido }}</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ $paciente?->email ?: $user?->email }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ $paciente?->telefono ?: 'Teléfono por confirmar' }}</p>
                    <div class="mt-4 rounded-2xl border border-violet-100 bg-violet-50 px-4 py-3 text-sm font-semibold text-violet-800">
                        No se pedirán nombre, email ni teléfono en este formulario. La cita se vinculará a tu usuario autenticado.
                    </div>
                </section>

                <section class="sticky top-24 rounded-3xl border border-slate-200 bg-slate-950 p-6 text-white shadow-2xl shadow-slate-950/15">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-200">Resumen</p>
                    <h2 class="mt-2 text-2xl font-black">Antes de confirmar</h2>

                    <div class="mt-5 space-y-3">
                        <div class="rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                            <span class="block text-xs font-bold uppercase tracking-wide text-slate-300">Médico</span>
                            <span id="summary-medico" class="mt-1 block text-sm font-black">{{ $selectedMedico ? $selectedMedico->nombre.' '.$selectedMedico->apellido : 'Pendiente' }}</span>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                            <span class="block text-xs font-bold uppercase tracking-wide text-slate-300">Servicio</span>
                            <span id="summary-servicio" class="mt-1 block text-sm font-black">{{ $selectedServicio ? $selectedServicio->nombre.' - '.$selectedServicio->duracion_minutos.' min' : 'Pendiente' }}</span>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                            <span class="block text-xs font-bold uppercase tracking-wide text-slate-300">Fecha</span>
                            <span id="summary-fecha" class="mt-1 block text-sm font-black">{{ $selectedFechaLabel ?: 'Pendiente' }}</span>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                            <span class="block text-xs font-bold uppercase tracking-wide text-slate-300">Horario</span>
                            <span id="summary-horario" class="mt-1 block text-sm font-black">{{ $selectedHorarioLabel ?: 'Pendiente' }}</span>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('patient-appointment-form');
            const medicoSelect = document.getElementById('medico_id');
            const servicioSelect = document.getElementById('servicio_id');
            const fechaInput = document.getElementById('appointment-fecha');
            const fechasSection = document.getElementById('fechas-section');
            const fechasGrid = document.getElementById('fechas-grid');
            const noFechasMessage = document.getElementById('no-fechas-message');
            const horariosSection = document.getElementById('horarios-section');
            const horariosGrid = document.getElementById('horarios-grid');
            const noHorariosMessage = document.getElementById('no-horarios-message');
            const motivoSection = document.getElementById('motivo-section');
            const sinServicios = document.getElementById('sin-servicios-medico');
            const confirmButton = document.getElementById('confirm-button');
            const summaryMedico = document.getElementById('summary-medico');
            const summaryServicio = document.getElementById('summary-servicio');
            const summaryFecha = document.getElementById('summary-fecha');
            const summaryHorario = document.getElementById('summary-horario');

            let selectedHorario = @json($selectedHorario);

            const serviciosIniciales = @json($serviciosIniciales);
            const fechasIniciales = @json($fechasDisponibles);
            const horariosIniciales = @json($horarios);
            const selectedServicioInicial = @json((string) $selectedServicioId);
            const selectedFechaInicial = @json($selectedFecha);

            const buildUrl = (template, replacements) => Object.entries(replacements).reduce(
                (url, [key, value]) => url.replace(key, encodeURIComponent(value)),
                template,
            );

            const fetchJson = async (url) => {
                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (! response.ok) {
                    throw new Error('No se pudo cargar la disponibilidad.');
                }

                return response.json();
            };

            const setSummary = () => {
                summaryMedico.textContent = medicoSelect.selectedOptions[0]?.textContent.trim() || 'Pendiente';
                summaryServicio.textContent = servicioSelect.value ? servicioSelect.selectedOptions[0]?.textContent.trim() : 'Pendiente';
                summaryFecha.textContent = fechaInput.value ? (document.querySelector(`[data-date-value="${fechaInput.value}"]`)?.dataset.dateLabel || fechaInput.value) : 'Pendiente';
                summaryHorario.textContent = selectedHorario ? (document.querySelector(`input[name="horario"][value="${selectedHorario}"]`)?.dataset.label || 'Horario seleccionado') : 'Pendiente';
                confirmButton.disabled = ! selectedHorario;
            };

            const resetDates = () => {
                fechaInput.value = '';
                fechasGrid.innerHTML = '';
                fechasSection.classList.add('hidden');
                noFechasMessage.classList.add('hidden');
                resetSlots();
            };

            const resetSlots = () => {
                selectedHorario = '';
                horariosGrid.innerHTML = '';
                horariosSection.classList.add('hidden');
                motivoSection.classList.add('hidden');
                noHorariosMessage.classList.add('hidden');
                setSummary();
            };

            const renderServicios = (servicios, selectedServicioId = '') => {
                servicioSelect.innerHTML = '';
                servicioSelect.append(new Option(medicoSelect.value ? 'Selecciona servicio' : 'Selecciona primero un médico', ''));

                servicios.forEach((servicio) => {
                    const option = new Option(servicio.label, servicio.id);
                    option.selected = String(servicio.id) === String(selectedServicioId);
                    servicioSelect.append(option);
                });

                servicioSelect.disabled = ! medicoSelect.value || servicios.length === 0;
                sinServicios.classList.toggle('hidden', ! medicoSelect.value || servicios.length > 0);
                setSummary();
            };

            const renderDates = (fechas, selectedFecha = '') => {
                fechasGrid.innerHTML = '';
                fechasSection.classList.toggle('hidden', ! medicoSelect.value || ! servicioSelect.value);
                noFechasMessage.classList.toggle('hidden', fechas.length > 0 || ! medicoSelect.value || ! servicioSelect.value);

                fechas.forEach((fecha) => {
                    const button = document.createElement('button');
                    const isSelected = selectedFecha === fecha.value;

                    button.type = 'button';
                    button.dataset.dateValue = fecha.value;
                    button.dataset.dateLabel = fecha.label;
                    button.className = `group flex min-h-28 flex-col justify-between rounded-3xl border px-4 py-3 text-left shadow-sm transition focus:outline-none focus:ring-4 focus:ring-violet-200 ${isSelected ? 'border-violet-700 bg-violet-700 text-white shadow-lg shadow-violet-700/20' : 'border-slate-200 bg-white text-slate-800 hover:-translate-y-0.5 hover:border-violet-300 hover:shadow-md'}`;
                    button.innerHTML = `
                        <span class="text-xs font-black uppercase tracking-[0.18em] ${isSelected ? 'text-violet-100' : 'text-violet-600'}">${fecha.short_day || ''}</span>
                        <span class="text-3xl font-black leading-none">${fecha.day_number || fecha.value.slice(-2)}</span>
                        <span class="text-[11px] font-extrabold ${isSelected ? 'text-violet-100' : 'text-slate-500'}">${fecha.slots_count} horarios disponibles</span>
                    `;
                    button.addEventListener('click', () => selectDate(fecha.value));
                    fechasGrid.append(button);
                });
            };

            const renderSlots = (horarios, selectedValue = '') => {
                horariosGrid.innerHTML = '';
                horariosSection.classList.toggle('hidden', ! fechaInput.value);
                noHorariosMessage.classList.toggle('hidden', horarios.length > 0 || ! fechaInput.value);
                motivoSection.classList.toggle('hidden', horarios.length === 0 && ! selectedValue);

                horarios.forEach((slot) => {
                    const label = document.createElement('label');
                    label.className = 'block select-none';
                    label.innerHTML = `
                        <input type="radio" name="horario" value="${slot.value}" data-label="${slot.label}" class="peer sr-only" ${selectedValue === slot.value ? 'checked' : ''} required>
                        <span class="flex min-h-16 cursor-pointer flex-col items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-center shadow-sm transition hover:border-violet-300 hover:bg-white peer-checked:border-violet-700 peer-checked:bg-violet-700 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-violet-700/20 peer-focus:ring-4 peer-focus:ring-violet-200">
                            <span class="text-base font-black leading-none">${slot.label}</span>
                            <span class="mt-1 text-[11px] font-bold opacity-75">Termina ${slot.ends_at}</span>
                        </span>
                    `;
                    label.querySelector('input').addEventListener('change', (event) => {
                        selectedHorario = event.target.value;
                        setSummary();
                    });
                    horariosGrid.append(label);
                });

                selectedHorario = selectedValue || '';
                setSummary();
            };

            const selectDate = async (fecha) => {
                fechaInput.value = fecha;
                selectedHorario = '';
                renderDates(Array.from(fechasGrid.children).map((button) => ({
                    value: button.dataset.dateValue,
                    label: button.dataset.dateLabel,
                    short_day: button.querySelector('span')?.textContent || '',
                    day_number: button.querySelectorAll('span')[1]?.textContent || '',
                    slots_count: Number((button.querySelectorAll('span')[2]?.textContent || '0').match(/\d+/)?.[0] || 0),
                })), fecha);
                resetSlots();

                try {
                    const url = buildUrl(form.dataset.horariosUrlTemplate, {
                        __MEDICO__: medicoSelect.value,
                        __SERVICIO__: servicioSelect.value,
                        __FECHA__: fecha,
                    });
                    const data = await fetchJson(url);
                    renderSlots(data.horarios || []);
                } catch (error) {
                    noHorariosMessage.textContent = error.message;
                    noHorariosMessage.classList.remove('hidden');
                }
            };

            medicoSelect.addEventListener('change', async () => {
                renderServicios([]);
                resetDates();

                if (! medicoSelect.value) {
                    return;
                }

                try {
                    const url = buildUrl(form.dataset.serviciosUrlTemplate, { __MEDICO__: medicoSelect.value });
                    const data = await fetchJson(url);
                    renderServicios(data.servicios || []);
                } catch (error) {
                    sinServicios.textContent = error.message;
                    sinServicios.classList.remove('hidden');
                }
            });

            servicioSelect.addEventListener('change', async () => {
                resetDates();
                setSummary();

                if (! medicoSelect.value || ! servicioSelect.value) {
                    return;
                }

                try {
                    const url = buildUrl(form.dataset.fechasUrlTemplate, {
                        __MEDICO__: medicoSelect.value,
                        __SERVICIO__: servicioSelect.value,
                    });
                    const data = await fetchJson(url);
                    renderDates(data.fechas || []);
                } catch (error) {
                    noFechasMessage.textContent = error.message;
                    noFechasMessage.classList.remove('hidden');
                }
            });

            fechasGrid.querySelectorAll('[data-date-value]').forEach((button) => {
                button.addEventListener('click', () => selectDate(button.dataset.dateValue));
            });

            horariosGrid.querySelectorAll('input[name="horario"]').forEach((input) => {
                input.dataset.label = input.closest('label').querySelector('.text-base')?.textContent.trim() || '';
                input.addEventListener('change', (event) => {
                    selectedHorario = event.target.value;
                    setSummary();
                });
            });

            if (serviciosIniciales.length > 0) {
                renderServicios(serviciosIniciales, selectedServicioInicial);
            }

            if (fechasIniciales.length > 0) {
                renderDates(fechasIniciales, selectedFechaInicial);
            }

            if (horariosIniciales.length > 0) {
                renderSlots(horariosIniciales, selectedHorario);
            }

            setSummary();
        });
    </script>
</x-app-layout>
