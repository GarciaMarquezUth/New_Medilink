<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Catálogo clínico</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Editar servicio</h1>
        </div>
    </x-slot>

    <form action="{{ route('servicios.update', $servicio->id) }}" method="POST" class="mx-auto max-w-4xl space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-950">Datos del servicio</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Actualiza duración, precio y disponibilidad para nuevas citas.</p>
                </div>
                <span class="inline-flex rounded-full bg-violet-50 px-4 py-2 text-xs font-extrabold uppercase tracking-wider text-violet-700">Servicio #{{ $servicio->id }}</span>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="nombre" value="Nombre" />
                    <x-text-input id="nombre" name="nombre" value="{{ old('nombre', $servicio->nombre) }}" class="mt-2" required />
                    <x-input-error :messages="$errors->get('nombre')" />
                </div>

                <div>
                    <x-input-label for="duracion_minutos" value="Duración en minutos" />
                    <x-text-input id="duracion_minutos" type="number" min="5" max="480" name="duracion_minutos" value="{{ old('duracion_minutos', $servicio->duracion_minutos) }}" class="mt-2" required />
                    <x-input-error :messages="$errors->get('duracion_minutos')" />
                </div>

                <div>
                    <x-input-label for="precio" value="Precio" />
                    <x-text-input id="precio" type="number" min="0" step="0.01" name="precio" value="{{ old('precio', $servicio->precio) }}" class="mt-2" />
                    <x-input-error :messages="$errors->get('precio')" />
                </div>

                <div class="flex items-end">
                    <label for="activo" class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
                        <input id="activo" type="checkbox" name="activo" value="1" class="rounded border-slate-300 text-violet-600 shadow-sm focus:ring-violet-500" @checked(old('activo', $servicio->activo))>
                        Servicio activo
                    </label>
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="descripcion" value="Descripción" />
                    <textarea id="descripcion" name="descripcion" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10">{{ old('descripcion', $servicio->descripcion) }}</textarea>
                    <x-input-error :messages="$errors->get('descripcion')" />
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('servicios.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">Cancelar</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">Actualizar servicio</button>
        </div>
    </form>
</x-app-layout>
