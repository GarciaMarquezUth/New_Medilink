@php
    $totalMedicos = \App\Models\Medico::count();
    $totalPacientes = \App\Models\Paciente::count();
    $totalCitas = \App\Models\Cita::count();
    $citasPendientes = \App\Models\Cita::whereIn('estado', \App\Models\Cita::estadosOcupantes())->count();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Panel principal</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Dashboard clínico</h1>
            </div>
            <div class="inline-flex items-center rounded-2xl border border-violet-100 bg-violet-50 px-4 py-2 text-sm font-bold text-violet-700">
                {{ now()->format('d/m/Y') }}
            </div>
        </div>
    </x-slot>

    <div class="space-y-8">
        <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 p-6 text-white shadow-2xl shadow-violet-900/20 sm:p-8">
            <div class="grid gap-6 lg:grid-cols-[1.4fr_0.8fr] lg:items-center">
                <div>
                    <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-violet-100 ring-1 ring-white/20">MediLink</span>
                    <h2 class="mt-5 text-3xl font-extrabold tracking-tight sm:text-4xl">Hola, {{ Auth::user()->name }}</h2>
                    <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-violet-100 sm:text-base">Gestiona la operación diaria de la clínica con una vista clara de citas, pacientes y médicos.</p>
                </div>

                <div class="rounded-3xl bg-white/12 p-5 ring-1 ring-white/20 backdrop-blur">
                    @role('admin')
                        <p class="text-sm font-bold text-violet-100">Rol activo</p>
                        <h3 class="mt-1 text-2xl font-extrabold">Administrador</h3>
                        <p class="mt-2 text-sm text-violet-100">Acceso completo a médicos, pacientes y agenda clínica.</p>
                    @endrole

                    @role('recepcionista')
                        <p class="text-sm font-bold text-violet-100">Rol activo</p>
                        <h3 class="mt-1 text-2xl font-extrabold">Recepción</h3>
                        <p class="mt-2 text-sm text-violet-100">Gestión de pacientes y agenda completa de citas.</p>
                    @endrole

                    @role('medico')
                        <p class="text-sm font-bold text-violet-100">Rol activo</p>
                        <h3 class="mt-1 text-2xl font-extrabold">Médico</h3>
                        <p class="mt-2 text-sm text-violet-100">Consulta tus citas asignadas y actualiza su estado.</p>
                    @endrole

                    @role('paciente')
                        <p class="text-sm font-bold text-violet-100">Rol activo</p>
                        <h3 class="mt-1 text-2xl font-extrabold">Paciente</h3>
                        <p class="mt-2 text-sm text-violet-100">Consulta el estado de tus próximas citas.</p>
                    @endrole
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60">
                <div class="flex items-center justify-between">
                    <span class="rounded-2xl bg-violet-50 p-3 text-violet-700">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 0116 0"/></svg>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total</span>
                </div>
                <p class="mt-5 text-sm font-bold text-slate-500">Médicos</p>
                <p class="mt-1 text-3xl font-extrabold text-slate-950">{{ $totalMedicos }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60">
                <div class="flex items-center justify-between">
                    <span class="rounded-2xl bg-fuchsia-50 p-3 text-fuchsia-700">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20a5 5 0 00-10 0M12 12a4 4 0 100-8 4 4 0 000 8z"/></svg>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total</span>
                </div>
                <p class="mt-5 text-sm font-bold text-slate-500">Pacientes</p>
                <p class="mt-1 text-3xl font-extrabold text-slate-950">{{ $totalPacientes }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60">
                <div class="flex items-center justify-between">
                    <span class="rounded-2xl bg-indigo-50 p-3 text-indigo-700">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/></svg>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Agenda</span>
                </div>
                <p class="mt-5 text-sm font-bold text-slate-500">Citas</p>
                <p class="mt-1 text-3xl font-extrabold text-slate-950">{{ $totalCitas }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60">
                <div class="flex items-center justify-between">
                    <span class="rounded-2xl bg-amber-50 p-3 text-amber-700">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5l3 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pendientes</span>
                </div>
                <p class="mt-5 text-sm font-bold text-slate-500">Por atender</p>
                <p class="mt-1 text-3xl font-extrabold text-slate-950">{{ $citasPendientes }}</p>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 lg:col-span-2">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-950">Accesos rápidos</h3>
                        <p class="mt-1 text-sm font-medium text-slate-500">Continúa con las tareas más frecuentes.</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @hasanyrole(['admin', 'recepcionista'])
                        <a href="{{ route('citas.create') }}" class="rounded-3xl border border-violet-100 bg-violet-50 p-5 transition hover:-translate-y-1 hover:bg-violet-100">
                            <p class="text-sm font-bold text-violet-700">Nueva cita</p>
                            <p class="mt-2 text-xs font-medium text-violet-700/70">Agenda o reagenda consultas.</p>
                        </a>
                        <a href="{{ route('pacientes.create') }}" class="rounded-3xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-1 hover:border-violet-100 hover:bg-violet-50">
                            <p class="text-sm font-bold text-slate-800">Nuevo paciente</p>
                            <p class="mt-2 text-xs font-medium text-slate-500">Registra datos clínicos básicos.</p>
                        </a>
                        <a href="{{ route('medicos.create') }}" class="rounded-3xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-1 hover:border-violet-100 hover:bg-violet-50">
                            <p class="text-sm font-bold text-slate-800">Nuevo médico</p>
                            <p class="mt-2 text-xs font-medium text-slate-500">Vincula usuarios médicos.</p>
                        </a>
                        <a href="{{ route('servicios.create') }}" class="rounded-3xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-1 hover:border-violet-100 hover:bg-violet-50">
                            <p class="text-sm font-bold text-slate-800">Nuevo servicio</p>
                            <p class="mt-2 text-xs font-medium text-slate-500">Define duración de consultas.</p>
                        </a>
                        <a href="{{ route('disponibilidades.create') }}" class="rounded-3xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-1 hover:border-violet-100 hover:bg-violet-50">
                            <p class="text-sm font-bold text-slate-800">Nuevo horario</p>
                            <p class="mt-2 text-xs font-medium text-slate-500">Configura disponibilidad médica.</p>
                        </a>
                    @else
                        <a href="{{ route('citas.index') }}" class="rounded-3xl border border-violet-100 bg-violet-50 p-5 transition hover:-translate-y-1 hover:bg-violet-100">
                            <p class="text-sm font-bold text-violet-700">Ver citas</p>
                            <p class="mt-2 text-xs font-medium text-violet-700/70">Consulta tu agenda asignada.</p>
                        </a>
                    @endhasanyrole
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60">
                <h3 class="text-lg font-extrabold text-slate-950">Estado del sistema</h3>
                <div class="mt-5 space-y-4">
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                        <span class="text-sm font-semibold text-slate-600">Roles</span>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Activo</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                        <span class="text-sm font-semibold text-slate-600">Agenda</span>
                        <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700">Operativa</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                        <span class="text-sm font-semibold text-slate-600">Interfaz</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">SaaS</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
