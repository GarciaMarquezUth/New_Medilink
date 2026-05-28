<section class="mx-auto grid max-w-6xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[0.72fr_1.28fr] lg:px-8">
    <aside class="space-y-5">
        <div class="rounded-3xl bg-violet-600 p-6 text-white shadow-2xl shadow-violet-900/20">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-100">Citas en linea</p>
            <h1 class="mt-4 text-3xl font-extrabold tracking-tight">Solicita tu cita</h1>
            <p class="mt-3 text-sm font-medium leading-6 text-violet-100">
                Completa los datos de la cita y los horarios libres apareceran automaticamente dentro del formulario.
            </p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60">
            <h2 class="text-base font-extrabold text-slate-950">Disponibilidad en vivo</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">
                MediLink valida la agenda del medico y oculta horarios ocupados antes de enviar la solicitud.
            </p>
            <div wire:loading wire:target="medicoId,servicioId,fecha" class="mt-4 rounded-2xl bg-violet-50 px-4 py-3 text-sm font-bold text-violet-700">
                Actualizando horarios...
            </div>
        </div>
    </aside>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60 sm:p-6">
        @if(session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-violet-600">Nueva solicitud</p>
            <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Datos de la cita</h2>
        </div>

        <form wire:submit="submit" class="mt-6 space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <h3 class="text-base font-extrabold text-slate-950">Selecciona atencion</h3>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="medicoId" value="Medico" />
                        <select id="medicoId" wire:model.live="medicoId" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>
                            <option value="">Selecciona un medico</option>
                            @foreach($medicos as $medico)
                                <option value="{{ $medico->id }}">
                                    {{ $medico->nombre }} {{ $medico->apellido }} - {{ $medico->especialidad }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('medicoId')" />
                    </div>

                    <div>
                        <x-input-label for="servicioId" value="Servicio" />
                        <select id="servicioId" wire:model.live="servicioId" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>
                            <option value="">Selecciona un servicio</option>
                            @foreach($servicios as $servicio)
                                <option value="{{ $servicio->id }}">
                                    {{ $servicio->nombre }} - {{ $servicio->duracion_minutos }} min
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('servicioId')" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="fecha" value="Fecha" />
                        <x-text-input id="fecha" type="date" wire:model.live="fecha" class="mt-2" required />
                        <x-input-error :messages="$errors->get('fecha')" />
                    </div>
                </div>

                <div class="mt-5">
                    <x-input-label value="Horarios disponibles" />
                    @if($medicoId && $servicioId && $fecha)
                        @if(count($slots) > 0)
                            <div class="mt-3 grid gap-2 sm:grid-cols-3">
                                @foreach($slots as $slot)
                                    <label wire:key="slot-{{ $slot['value'] }}" class="flex cursor-pointer items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-violet-200 hover:bg-violet-50 has-[:checked]:border-violet-600 has-[:checked]:bg-violet-600 has-[:checked]:text-white">
                                        <input type="radio" wire:model.live="fechaHora" value="{{ $slot['value'] }}" class="sr-only" required>
                                        {{ $slot['label'] }} - {{ $slot['ends_at'] }}
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-3 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-4 text-sm font-bold text-amber-800">
                                No hay horarios disponibles para esa combinacion.
                            </div>
                        @endif
                    @else
                        <div class="mt-3 rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm font-semibold text-slate-500">
                            Selecciona medico, servicio y fecha para ver horarios automaticamente.
                        </div>
                    @endif
                    <x-input-error :messages="$errors->get('fechaHora')" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="nombre" value="Nombre" />
                    <x-text-input id="nombre" wire:model="nombre" class="mt-2" required />
                    <x-input-error :messages="$errors->get('nombre')" />
                </div>
                <div>
                    <x-input-label for="apellido" value="Apellido" />
                    <x-text-input id="apellido" wire:model="apellido" class="mt-2" required />
                    <x-input-error :messages="$errors->get('apellido')" />
                </div>
                <div>
                    <x-input-label for="fechaNacimiento" value="Fecha de nacimiento" />
                    <x-text-input id="fechaNacimiento" type="date" wire:model="fechaNacimiento" class="mt-2" required />
                    <x-input-error :messages="$errors->get('fechaNacimiento')" />
                </div>
                <div>
                    <x-input-label for="genero" value="Genero" />
                    <select id="genero" wire:model="genero" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>
                        <option value="">Selecciona una opcion</option>
                        @foreach(['Femenino', 'Masculino', 'No especificado'] as $generoOption)
                            <option value="{{ $generoOption }}">{{ $generoOption }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('genero')" />
                </div>
                <div>
                    <x-input-label for="email" value="Correo electronico" />
                    <x-text-input id="email" type="email" wire:model="email" class="mt-2" required />
                    <x-input-error :messages="$errors->get('email')" />
                </div>
                <div>
                    <x-input-label for="telefono" value="Telefono" />
                    <x-text-input id="telefono" wire:model="telefono" class="mt-2" required />
                    <x-input-error :messages="$errors->get('telefono')" />
                </div>
            </div>

            <div>
                <x-input-label for="motivo" value="Motivo de la cita" />
                <textarea id="motivo" wire:model="motivo" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required></textarea>
                <x-input-error :messages="$errors->get('motivo')" />
            </div>

            <button type="submit" class="w-full rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-slate-950/15 transition hover:-translate-y-0.5 hover:bg-violet-700 disabled:cursor-wait disabled:opacity-70" wire:loading.attr="disabled">
                Solicitar cita
            </button>
        </form>
    </section>
</section>
