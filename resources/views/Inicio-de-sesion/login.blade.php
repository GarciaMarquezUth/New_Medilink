<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MediLink - Iniciar Sesión</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans antialiased">
    <main class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">
        <div class="absolute inset-x-0 top-0 -z-10 h-72 bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-500"></div>
        <div class="absolute left-1/2 top-24 -z-10 h-72 w-72 -translate-x-1/2 rounded-full bg-white/20 blur-3xl"></div>

        <section class="w-full max-w-md">
            <div class="mb-8 text-center text-white">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 text-2xl font-black shadow-lg ring-1 ring-white/30 backdrop-blur">M</div>
                <h1 class="mt-5 text-3xl font-extrabold tracking-tight">MediLink</h1>
                <p class="mt-2 text-sm font-medium text-violet-100">Gestión clínica moderna y segura</p>
            </div>

            <div class="rounded-3xl border border-white/70 bg-white/95 p-8 shadow-2xl shadow-violet-950/15 backdrop-blur">
                <div class="mb-8 text-center">
                    <span class="inline-flex rounded-full bg-violet-50 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-violet-700">Acceso seguro</span>
                    <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">Bienvenido</h2>
                    <p class="mt-2 text-sm font-medium text-slate-500">Ingresa tus credenciales para continuar.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-5 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-5 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700">Correo electrónico</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nombre@clinica.com" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700">Contraseña</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-500/10" required>
                    </div>

                    <div class="flex justify-end">
                        <a href="{{ route('password.request') }}" class="text-sm font-bold text-violet-700 transition hover:text-violet-900">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="w-full rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">Iniciar sesión</button>
                </form>

                <p class="mt-6 text-center text-sm font-medium text-slate-500">
                    ¿No tienes cuenta?
                    <a href="{{ route('register') }}" class="font-bold text-violet-700 hover:text-violet-900">Regístrate</a>
                </p>
            </div>
        </section>
    </main>
</body>
</html>
