<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Equipo clínico</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Editar médico</h1>
        </div>
    </x-slot>

    @php
        $selectedServicios = collect(old('servicio_ids', $medico->servicios->pluck('id')->all()))->map(fn ($id) => (int) $id)->all();
    @endphp

    <form action="{{ route('medicos.update', $medico->id) }}" method="POST" enctype="multipart/form-data" class="mx-auto max-w-4xl space-y-6">
        @csrf
        @method('PUT')

        @include('Medicos.partials.photo-upload', ['medico' => $medico])

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8">
            <div class="mb-6">
                <h2 class="text-lg font-extrabold text-slate-950">Datos del médico</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Actualiza información profesional y usuario vinculado.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="nombre" value="Nombre" />
                    <x-text-input id="nombre" name="nombre" value="{{ old('nombre', $medico->nombre) }}" class="mt-2" required />
                    <x-input-error :messages="$errors->get('nombre')" />
                </div>

                <div>
                    <x-input-label for="apellido" value="Apellido" />
                    <x-text-input id="apellido" name="apellido" value="{{ old('apellido', $medico->apellido) }}" class="mt-2" required />
                    <x-input-error :messages="$errors->get('apellido')" />
                </div>

                <div>
                    <x-input-label for="email" value="Correo electrónico" />
                    <x-text-input id="email" type="email" name="email" value="{{ old('email', $medico->email) }}" class="mt-2" required />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div>
                    <x-input-label for="telefono" value="Teléfono" />
                    <x-text-input id="telefono" name="telefono" value="{{ old('telefono', $medico->telefono) }}" class="mt-2" />
                    <x-input-error :messages="$errors->get('telefono')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="especialidad" value="Especialidad" />
                    <x-text-input id="especialidad" name="especialidad" value="{{ old('especialidad', $medico->especialidad) }}" class="mt-2" required />
                    <x-input-error :messages="$errors->get('especialidad')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="user_id" value="Usuario vinculado" />
                    <select id="user_id" name="user_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10">
                        <option value="">Sin usuario vinculado</option>
                        @foreach($usuariosMedicos as $usuario)
                            <option value="{{ $usuario->id }}" {{ (int) old('user_id', $medico->user_id) === $usuario->id ? 'selected' : '' }}>
                                {{ $usuario->name }} - {{ $usuario->email }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-sm font-medium text-slate-500">Solo aparecen usuarios con rol médico.</p>
                    <x-input-error :messages="$errors->get('user_id')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label value="Servicios que atiende" />
                    <p class="mt-2 text-sm font-medium text-slate-500">Selecciona al menos un servicio para que el médico aparezca con servicios disponibles al agendar.</p>

                    @if($servicios->isEmpty())
                        <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                            No hay servicios registrados. Crea servicios antes de actualizar médicos.
                        </div>
                    @else
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            @foreach($servicios as $servicio)
                                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm transition hover:border-violet-200 hover:bg-violet-50/60">
                                    <input type="checkbox" name="servicio_ids[]" value="{{ $servicio->id }}" class="mt-1 rounded border-slate-300 text-violet-600 shadow-sm focus:ring-violet-500" {{ in_array($servicio->id, $selectedServicios, true) ? 'checked' : '' }}>
                                    <span>
                                        <span class="block font-bold text-slate-800">{{ $servicio->nombre }}</span>
                                        <span class="mt-1 block text-xs font-semibold text-slate-500">{{ $servicio->duracion_minutos }} min @if($servicio->precio !== null) · ${{ number_format((float) $servicio->precio, 2) }} @endif @unless($servicio->activo) · Inactivo @endunless</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    <x-input-error :messages="$errors->get('servicio_ids')" />
                    <x-input-error :messages="$errors->get('servicio_ids.*')" />
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('medicos.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">Cancelar</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">Actualizar médico</button>
        </div>
    </form>
</x-app-layout>
