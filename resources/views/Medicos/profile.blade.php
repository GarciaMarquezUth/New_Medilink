<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Panel médico</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Mi perfil médico</h1>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-violet-100">
                Volver al dashboard
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 selection:bg-violet-100 selection:text-violet-950">
        @if(session('success'))
            <div class="rounded-3xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 p-6 text-white shadow-2xl shadow-violet-900/20 sm:p-8">
            <div class="flex flex-col gap-6 md:flex-row md:items-center">
                <x-medico-avatar :medico="$medico" class="h-28 w-28 rounded-full" text-class="text-4xl" />
                <div>
                    <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-violet-100 ring-1 ring-white/20">Perfil público</span>
                    <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">Dr. {{ $medico->nombre }} {{ $medico->apellido }}</h2>
                    <p class="mt-2 text-sm font-semibold text-violet-100">{{ $medico->especialidad }}</p>
                    <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-violet-100">Estos datos se usan para presentarte en el portal público y para asociar tu agenda clínica.</p>
                </div>
            </div>
        </section>

        <form action="{{ route('medicos.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            @include('Medicos.partials.photo-upload', ['medico' => $medico])

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8">
                <div class="mb-6">
                    <h2 class="text-lg font-extrabold text-slate-950">Datos profesionales</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Actualiza la información visible para pacientes. Los servicios se gestionan por administración.</p>
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
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-950">Servicios que ofreces</h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">Estos servicios aparecen al agendar contigo en el portal público.</p>
                    </div>
                    <span class="rounded-full bg-violet-50 px-4 py-2 text-sm font-bold text-violet-700">{{ $medico->servicios->count() }} servicios</span>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    @forelse($medico->servicios as $servicio)
                        <span class="rounded-full bg-violet-50 px-4 py-2 text-sm font-bold text-violet-700">{{ $servicio->nombre }} · {{ $servicio->duracion_minutos }} min</span>
                    @empty
                        <span class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">Aún no tienes servicios asignados.</span>
                    @endforelse
                </div>
            </section>

            <div class="sticky bottom-4 z-10 rounded-3xl border border-slate-200 bg-white/95 p-4 shadow-xl shadow-slate-900/10 backdrop-blur sm:flex sm:items-center sm:justify-between sm:gap-4">
                <p class="mb-3 text-sm font-semibold text-slate-500 sm:mb-0">Los cambios se aplicarán al portal público inmediatamente.</p>
                <button type="submit" class="inline-flex w-full select-none items-center justify-center rounded-2xl bg-violet-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700 focus:outline-none focus:ring-4 focus:ring-violet-200 sm:w-auto">
                    Guardar perfil
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
