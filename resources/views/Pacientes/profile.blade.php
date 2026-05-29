<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Portal del paciente</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Completar perfil del paciente</h1>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Volver al dashboard
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
        @if(session('status'))
            <div class="rounded-3xl border border-violet-100 bg-violet-50 px-5 py-4 text-sm font-semibold text-violet-700 shadow-sm">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-3xl border border-rose-100 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700 shadow-sm">
                <p class="font-extrabold">Revisa tu perfil médico.</p>
                <p class="mt-1">Los campos marcados como obligatorios son necesarios para poder agendar citas.</p>
            </div>
        @endif

        <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 p-6 text-white shadow-2xl shadow-violet-900/20 sm:p-8">
            <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
                <div>
                    <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-violet-100 ring-1 ring-white/20">Perfil médico</span>
                    <h2 class="mt-5 text-3xl font-extrabold tracking-tight sm:text-4xl">Completa tus datos de paciente</h2>
                    <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-violet-100 sm:text-base">
                        Esta información será visible para los médicos que atiendan tus citas y nos ayuda a registrar tu expediente correctamente.
                    </p>
                </div>
                <div class="rounded-3xl bg-white/12 p-5 ring-1 ring-white/20 backdrop-blur">
                    <p class="text-sm font-bold text-violet-100">Cuenta vinculada</p>
                    <p class="mt-1 text-xl font-extrabold">{{ $user->name }}</p>
                    <p class="mt-1 text-sm text-violet-100">{{ $user->email }}</p>
                </div>
            </div>
        </section>

        <form action="{{ route('pacientes.profile.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8">
                <div class="mb-6">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Datos obligatorios</p>
                    <h2 class="mt-1 text-xl font-extrabold text-slate-950">Información médica básica</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Completa estos campos para poder agendar citas desde tu dashboard.</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
                        <x-text-input id="fecha_nacimiento" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $paciente->fecha_nacimiento) }}" class="mt-2" required />
                        <x-input-error :messages="$errors->get('fecha_nacimiento')" />
                    </div>

                    <div>
                        <x-input-label for="genero" value="Género" />
                        <select id="genero" name="genero" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm transition focus:border-violet-500 focus:ring-violet-500" required>
                            <option value="">Selecciona género</option>
                            @foreach(['Femenino', 'Masculino', 'Otro', 'Prefiero no decir'] as $genero)
                                <option value="{{ $genero }}" {{ old('genero', $paciente->genero) === $genero ? 'selected' : '' }}>{{ $genero }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('genero')" />
                    </div>

                    <div>
                        <x-input-label for="telefono" value="Teléfono" />
                        <x-text-input id="telefono" name="telefono" value="{{ old('telefono', $paciente->telefono) }}" class="mt-2" required />
                        <x-input-error :messages="$errors->get('telefono')" />
                    </div>

                    <div>
                        <x-input-label for="tipo_sangre" value="Tipo de sangre" />
                        <select id="tipo_sangre" name="tipo_sangre" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm transition focus:border-violet-500 focus:ring-violet-500" required>
                            <option value="">Selecciona tipo</option>
                            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'No sé'] as $tipo)
                                <option value="{{ $tipo }}" {{ old('tipo_sangre', $paciente->tipo_sangre) === $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('tipo_sangre')" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="direccion" value="Dirección" />
                        <textarea id="direccion" name="direccion" rows="3" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm transition focus:border-violet-500 focus:ring-violet-500" required>{{ old('direccion', $paciente->direccion) }}</textarea>
                        <x-input-error :messages="$errors->get('direccion')" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="alergias" value="Alergias" />
                        <textarea id="alergias" name="alergias" rows="3" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm transition focus:border-violet-500 focus:ring-violet-500" placeholder="Ejemplo: Ninguna conocida" required>{{ old('alergias', $paciente->alergias) }}</textarea>
                        <x-input-error :messages="$errors->get('alergias')" />
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8">
                <div class="mb-6">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Opcional</p>
                    <h2 class="mt-1 text-xl font-extrabold text-slate-950">Contacto de emergencia</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Puedes agregar una persona de contacto para casos importantes.</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
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
            </section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">Guardar perfil médico</button>
            </div>
        </form>
    </div>
</x-app-layout>
