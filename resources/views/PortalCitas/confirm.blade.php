<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Confirmar cita | {{ config('app.name', 'MediLink') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/medilink_logo.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            ::selection {
                background: #ede9fe;
                color: #4c1d95;
            }
        </style>
    </head>

    <body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
        <main class="min-h-screen bg-[radial-gradient(circle_at_top_left,#f3e8ff_0,#f8fafc_35%,#f1f5f9_100%)]">
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex max-w-[1100px] items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                    <a href="{{ route('portal-citas.index') }}" class="inline-flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-700 text-sm font-black text-white shadow-sm shadow-violet-700/20">M</span>
                        <span class="text-lg font-black tracking-tight text-slate-950">MediLink</span>
                    </a>

                    <a href="{{ route('portal-citas.index', ['medico_id' => $payload['medico_id'], 'servicio_id' => $payload['servicio_id'], 'fecha' => $payload['fecha']]) }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700 focus:outline-none focus:ring-4 focus:ring-violet-100">
                        Editar selección
                    </a>
                </div>
            </header>

            <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
                <section class="rounded-[2rem] border border-white bg-white/90 px-5 py-7 text-center shadow-xl shadow-violet-950/10 backdrop-blur sm:px-8">
                    <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-violet-600">Último paso</p>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Revisa y confirma tu cita</h1>
                    <p class="mx-auto mt-3 max-w-2xl text-sm font-semibold leading-6 text-slate-600 sm:text-base">
                        Confirma que los datos sean correctos. Validaremos el horario una vez más antes de guardar la cita.
                    </p>
                </section>

                @if ($errors->any())
                    <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-800 shadow-sm">
                        <p class="font-black">No se pudo confirmar la cita.</p>
                        <p class="mt-1">El horario pudo haberse ocupado o los datos ya no son válidos. Revisa la selección e intenta nuevamente.</p>
                    </div>
                @endif

                <section class="mt-5 rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5 border-b border-slate-100 pb-4">
                        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Resumen</p>
                        <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Datos que vamos a registrar</h2>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <span class="block text-xs font-black uppercase tracking-wide text-slate-400">Médico</span>
                            <span class="mt-1 block text-sm font-bold text-slate-800">{{ $medico?->nombre }} {{ $medico?->apellido }}</span>
                        </div>

                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <span class="block text-xs font-black uppercase tracking-wide text-slate-400">Servicio</span>
                            <span class="mt-1 block text-sm font-bold text-slate-800">{{ $servicio?->nombre }} @if ($servicio) - {{ $servicio->duracion_minutos }} min @endif</span>
                        </div>

                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <span class="block text-xs font-black uppercase tracking-wide text-slate-400">Fecha y hora</span>
                            <span class="mt-1 block text-sm font-bold text-slate-800">{{ \Carbon\Carbon::parse($payload['horario'])->format('d/m/Y H:i') }}</span>
                        </div>

                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <span class="block text-xs font-black uppercase tracking-wide text-slate-400">Paciente</span>
                            <span class="mt-1 block text-sm font-bold text-slate-800">{{ $payload['nombre'] }} {{ $payload['apellido'] }}</span>
                        </div>

                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <span class="block text-xs font-black uppercase tracking-wide text-slate-400">Email</span>
                            <span class="mt-1 block text-sm font-bold text-slate-800">{{ $payload['email'] }}</span>
                        </div>

                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <span class="block text-xs font-black uppercase tracking-wide text-slate-400">Teléfono</span>
                            <span class="mt-1 block text-sm font-bold text-slate-800">{{ $payload['telefono'] }}</span>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl bg-slate-50 px-4 py-3">
                        <span class="block text-xs font-black uppercase tracking-wide text-slate-400">Motivo</span>
                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-700">{{ $payload['motivo'] }}</p>
                    </div>

                    <div class="mt-5 rounded-2xl border border-violet-100 bg-violet-50 px-4 py-3 text-sm font-semibold text-violet-800">
                        Al confirmar, la cita quedará vinculada a tu perfil de paciente.
                    </div>

                    <form action="{{ route('portal-citas.confirm.store') }}" method="POST" class="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-end">
                        @csrf
                        <a href="{{ route('portal-citas.index', ['medico_id' => $payload['medico_id'], 'servicio_id' => $payload['servicio_id'], 'fecha' => $payload['fecha']]) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3.5 text-sm font-black text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-violet-100">
                            Cambiar horario
                        </a>
                        <button type="submit" class="inline-flex select-none items-center justify-center rounded-2xl bg-violet-700 px-6 py-3.5 text-sm font-black text-white shadow-lg shadow-violet-700/20 transition hover:-translate-y-0.5 hover:bg-violet-800 focus:outline-none focus:ring-4 focus:ring-violet-200">
                            Confirmar cita
                        </button>
                    </form>
                </section>
            </div>
        </main>
    </body>
</html>
