<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MediLink - Iniciar Sesión</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 font-sans antialiased">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            <!-- Logo y título -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                    MediLink
                </h1>
                <p class="text-gray-600 mt-2">Conectamos tu salud con los mejores profesionales</p>
            </div>

            <!-- Tarjeta de inicio de sesión -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">Iniciar sesión</h2>
                    <p class="text-gray-500 mt-1">Bienvenido de vuelta a Medilink</p>
                </div>

                <!-- Mensajes de error/success -->
                @if ($errors->any())
                    <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200">
                        <ul class="text-sm text-red-600 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Formulario -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf
                    
                    <!-- Campo de email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Correo electrónico
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="ejemplo@correo.com"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('email') @enderror"
                               required>
                        @error('email')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Campo de contraseña -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Contraseña
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               placeholder="********"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('password') @enderror"
                               required>
                        @error('password')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Olvidó contraseña -->
                    <div class="flex justify-end">
                        <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-700 hover:underline">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    <!-- Botón de inicio de sesión -->
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-2 px-4 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 font-medium shadow-md hover:shadow-lg">
                        Iniciar sesión
                    </button>
                </form>

                <!-- Separador -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-3 bg-white text-gray-500">o continúa con</span>
                    </div>
                </div>

                <!-- Botones de redes sociales -->
                <div class="space-y-3">
                    <button onclick="handleSocialLogin('google')" 
                            class="w-full flex items-center justify-center gap-3 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span class="text-gray-700">Google</span>
                    </button>

                    <button onclick="handleSocialLogin('apple')" 
                            class="w-full flex items-center justify-center gap-3 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.05 20.28c-.98.95-2.05.88-3.08.42-1.09-.47-2.08-.48-3.24 0-1.44.62-2.2.44-3.06-.42C5.07 17.33 3.5 13.92 6.09 9.78c1.02-1.61 2.63-2.63 4.39-2.66 1.14-.01 2.23.49 3.03 1.29.74.74 1.93.82 2.8.27.92-.56 1.99-.52 2.91.21.68.57 1.05 1.31 1.17 2.13-1.26.61-2.02 1.68-1.93 3.07.09 1.01.78 1.97 1.67 2.46-.4 1.17-.91 2.32-1.79 3.19zM15.53 2.6c.86 1.01.73 2.13-.21 2.98-.98.87-2.15.78-2.85-.21-.69-1-.51-2.14.32-2.93.77-.73 1.96-.84 2.74.16z"/>
                        </svg>
                        <span class="text-gray-700">Apple</span>
                    </button>
                </div>

                <!-- Enlace de registro -->
                <div class="text-center mt-6">
                    <p class="text-sm text-gray-600">
                        ¿No tienes cuenta? 
                        <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700 font-medium hover:underline">
                            Regístrate
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function handleSocialLogin(provider) {
            // Aquí implementarías la lógica de autenticación social
            console.log(`Iniciando sesión con ${provider}`);
            // Redirigir a la ruta de autenticación social
            // window.location.href = `/auth/${provider}`;
        }
    </script>
</body>
</html>