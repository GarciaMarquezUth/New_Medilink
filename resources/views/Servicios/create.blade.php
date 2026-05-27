<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Nuevo Servicio') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form action="{{ route('servicios.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Nombre del servicio</label>
                        <input type="text" name="nombre" class="w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Duración (minutos)</label>
                        <input type="number" name="duracion_minutos" class="w-full border-gray-300 rounded-md shadow-sm" min="5" required>
                    </div>
                    
                    <div class="flex justify-end">
                        <a href="{{ route('servicios.index') }}" class="mr-4 text-gray-600 hover:text-gray-900">Cancelar</a>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Guardar Servicio
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>