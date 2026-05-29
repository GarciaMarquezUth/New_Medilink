<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Atención clínica</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Editar paciente</h1>
        </div>
    </x-slot>

    <form action="{{ route('pacientes.update', $paciente->id) }}" method="POST" class="mx-auto max-w-4xl space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8">
            <div class="mb-6">
                <h2 class="text-lg font-extrabold text-slate-950">Datos del paciente</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Actualiza información personal, médica y usuario vinculado.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="nombre" value="Nombre" />
                    <x-text-input id="nombre" name="nombre" value="{{ old('nombre', $paciente->nombre) }}" class="mt-2" required />
                    <x-input-error :messages="$errors->get('nombre')" />
                </div>

                <div>
                    <x-input-label for="apellido" value="Apellido" />
                    <x-text-input id="apellido" name="apellido" value="{{ old('apellido', $paciente->apellido) }}" class="mt-2" required />
                    <x-input-error :messages="$errors->get('apellido')" />
                </div>

                <div>
                    <x-input-label for="email" value="Correo electrónico" />
                    <x-text-input id="email" type="email" name="email" value="{{ old('email', $paciente->email) }}" class="mt-2" required />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div>
                    <x-input-label for="telefono" value="Teléfono" />
                    <x-text-input id="telefono" name="telefono" value="{{ old('telefono', $paciente->telefono) }}" class="mt-2" required />
                    <x-input-error :messages="$errors->get('telefono')" />
                </div>

                <div>
                    <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
                    <x-text-input id="fecha_nacimiento" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $paciente->fecha_nacimiento) }}" class="mt-2" required />
                    <x-input-error :messages="$errors->get('fecha_nacimiento')" />
                </div>

                <div>
                    <x-input-label for="genero" value="Género" />
                    <x-text-input id="genero" name="genero" value="{{ old('genero', $paciente->genero) }}" class="mt-2" required />
                    <x-input-error :messages="$errors->get('genero')" />
                </div>

                <div>
                    <x-input-label for="tipo_sangre" value="Tipo de sangre" />
                    <x-text-input id="tipo_sangre" name="tipo_sangre" value="{{ old('tipo_sangre', $paciente->tipo_sangre) }}" class="mt-2" />
                    <x-input-error :messages="$errors->get('tipo_sangre')" />
                </div>

                <div>
                    <x-input-label for="user_id" value="Usuario vinculado" />
                    <select id="user_id" name="user_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10">
                        <option value="">Sin usuario vinculado</option>
                        @foreach($usuariosPacientes as $usuario)
                            <option value="{{ $usuario->id }}" {{ (int) old('user_id', $paciente->user_id) === $usuario->id ? 'selected' : '' }}>
                                {{ $usuario->name }} - {{ $usuario->email }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('user_id')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="direccion" value="Dirección" />
                    <textarea id="direccion" name="direccion" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10">{{ old('direccion', $paciente->direccion) }}</textarea>
                    <x-input-error :messages="$errors->get('direccion')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="alergias" value="Alergias" />
                    <textarea id="alergias" name="alergias" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10">{{ old('alergias', $paciente->alergias) }}</textarea>
                    <x-input-error :messages="$errors->get('alergias')" />
                </div>

                <div>
                    <x-input-label for="contacto_emergencia" value="Contacto de emergencia" />
                    <x-text-input id="contacto_emergencia" name="contacto_emergencia" value="{{ old('contacto_emergencia', $paciente->contacto_emergencia) }}" class="mt-2" />
                    <x-input-error :messages="$errors->get('contacto_emergencia')" />
                </div>

                <div>
                    <x-input-label for="telefono_emergencia" value="Teléfono de emergencia" />
                    <x-text-input id="telefono_emergencia" name="telefono_emergencia" value="{{ old('telefono_emergencia', $paciente->telefono_emergencia) }}" class="mt-2" />
                    <x-input-error :messages="$errors->get('telefono_emergencia')" />
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('pacientes.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">Cancelar</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">Actualizar paciente</button>
        </div>
    </form>
</x-app-layout>
