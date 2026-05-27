<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Panel de Control') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-purple-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-purple-700">
                <h1 class="text-xl font-bold text-purple-800">¡Bienvenido a la Clínica!</h1>
                <p class="text-gray-600">Gestiona tus médicos, pacientes y citas desde el menú superior.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-purple-700 p-6 rounded-lg shadow-sm text-white">
                    <p class="font-bold">Médicos Activos</p>
                    <p class="text-2xl font-bold">{{ $totalMedicos ?? 0 }}</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-sm border border-purple-200">
                    <p class="font-bold text-gray-700">Citas Totales</p>
                    <p class="text-2xl font-bold text-purple-700">{{ $citasPendientes ?? 0 }}</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-sm border border-purple-200">
                    <p class="font-bold text-gray-700">Pacientes</p>
                    <p class="text-2xl font-bold text-purple-700">{{ $totalPacientes ?? 0 }}</p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>