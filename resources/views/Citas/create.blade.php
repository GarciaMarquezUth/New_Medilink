<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Agenda clínica</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Nueva cita</h1>
        </div>
    </x-slot>

    @php
        $serviciosPorMedico = $medicos->mapWithKeys(fn ($medico) => [
            (string) $medico->id => $medico->servicios->map(fn ($servicio) => [
                'id' => (string) $servicio->id,
                'label' => $servicio->nombre.' · '.$servicio->duracion_minutos.' min'.($servicio->precio !== null ? ' · $'.number_format((float) $servicio->precio, 2) : ''),
            ])->values()->all(),
        ])->all();
    @endphp

    <form action="{{ route('citas.store') }}" method="POST" class="mx-auto max-w-4xl space-y-6">
        @csrf

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8">
            <div class="mb-6">
                <h2 class="text-lg font-extrabold text-slate-950">Información de la cita</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Selecciona paciente, médico, servicio, fecha y motivo de la atención.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="paciente_id" value="Paciente" />
                    <select id="paciente_id" name="paciente_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>
                        @foreach($pacientes as $p)
                            <option value="{{ $p->id }}" {{ (int) old('paciente_id') === $p->id ? 'selected' : '' }}>{{ $p->nombre }} {{ $p->apellido }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('paciente_id')" />
                </div>

                <div>
                    <x-input-label for="medico_id" value="Médico" />
                    <select id="medico_id" name="medico_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>
                        <option value="">Selecciona un médico</option>
                        @foreach($medicos as $m)
                            <option value="{{ $m->id }}" {{ (int) $selectedMedicoId === $m->id ? 'selected' : '' }}>{{ $m->nombre }} {{ $m->apellido }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('medico_id')" />
                </div>

                <div>
                    <x-input-label for="servicio_id" value="Servicio" />
                    <select id="servicio_id" name="servicio_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required {{ ! $selectedMedicoId || $servicios->isEmpty() ? 'disabled' : '' }}>
                        <option value="">{{ $selectedMedicoId ? 'Selecciona un servicio' : 'Selecciona primero un médico' }}</option>
                        @foreach($servicios as $servicio)
                            <option value="{{ $servicio->id }}" {{ (int) $selectedServicioId === $servicio->id ? 'selected' : '' }}>
                                {{ $servicio->nombre }} · {{ $servicio->duracion_minutos }} min @if($servicio->precio !== null) · ${{ number_format((float) $servicio->precio, 2) }} @endif
                            </option>
                        @endforeach
                    </select>
                    <p id="sin-servicios-medico" class="{{ $selectedMedicoId && $servicios->isEmpty() ? '' : 'hidden' }} mt-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-800">Este médico no tiene servicios disponibles.</p>
                    <x-input-error :messages="$errors->get('servicio_id')" />
                </div>

                <div>
                    <x-input-label for="fecha_hora" value="Fecha y hora" />
                    <x-text-input id="fecha_hora" type="datetime-local" name="fecha_hora" value="{{ old('fecha_hora') }}" class="mt-2" required :disabled="! $selectedMedicoId || ! $selectedServicioId" />
                    <x-input-error :messages="$errors->get('fecha_hora')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="motivo" value="Motivo" />
                    <textarea id="motivo" name="motivo" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>{{ old('motivo') }}</textarea>
                    <x-input-error :messages="$errors->get('motivo')" />
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8">
            <div class="mb-6">
                <h2 class="text-lg font-extrabold text-slate-950">Control de pago</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Registra el estado financiero de la cita. Puedes dejarlo pendiente.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <x-input-label for="estado_pago" value="Estado de pago" />
                    <select id="estado_pago" name="estado_pago" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10">
                        @foreach($estadosPago as $estadoPago => $label)
                            <option value="{{ $estadoPago }}" @selected(old('estado_pago', \App\Models\Cita::PAGO_PENDIENTE) === $estadoPago)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('estado_pago')" />
                </div>

                <div>
                    <x-input-label for="monto_pagado" value="Monto pagado" />
                    <x-text-input id="monto_pagado" type="number" name="monto_pagado" value="{{ old('monto_pagado') }}" class="mt-2" min="0" step="0.01" placeholder="0.00" />
                    <x-input-error :messages="$errors->get('monto_pagado')" />
                </div>

                <div>
                    <x-input-label for="metodo_pago" value="Método" />
                    <select id="metodo_pago" name="metodo_pago" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10">
                        <option value="">Sin registrar</option>
                        @foreach($metodosPago as $metodoPago => $label)
                            <option value="{{ $metodoPago }}" @selected(old('metodo_pago') === $metodoPago)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('metodo_pago')" />
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('citas.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">Cancelar</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">Guardar cita</button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const serviciosPorMedico = @json($serviciosPorMedico);
            const medicoSelect = document.getElementById('medico_id');
            const servicioSelect = document.getElementById('servicio_id');
            const fechaInput = document.getElementById('fecha_hora');
            const sinServicios = document.getElementById('sin-servicios-medico');
            const initialServicioId = @json((string) $selectedServicioId);

            const renderServicios = (selectedServicioId = '', clearDate = false) => {
                const servicios = serviciosPorMedico[medicoSelect.value] || [];

                servicioSelect.innerHTML = '';
                servicioSelect.append(new Option(medicoSelect.value ? 'Selecciona un servicio' : 'Selecciona primero un médico', ''));

                servicios.forEach((servicio) => {
                    const option = new Option(servicio.label, servicio.id);
                    option.selected = servicio.id === String(selectedServicioId);
                    servicioSelect.append(option);
                });

                servicioSelect.disabled = ! medicoSelect.value || servicios.length === 0;
                sinServicios.classList.toggle('hidden', ! medicoSelect.value || servicios.length > 0);

                if (fechaInput) {
                    if (clearDate) {
                        fechaInput.value = '';
                    }

                    fechaInput.disabled = ! servicioSelect.value;
                }
            };

            medicoSelect.addEventListener('change', () => renderServicios('', true));
            servicioSelect.addEventListener('change', () => {
                if (fechaInput) {
                    fechaInput.value = '';
                    fechaInput.disabled = ! servicioSelect.value;
                }
            });

            renderServicios(initialServicioId, false);
        });
    </script>
</x-app-layout>
