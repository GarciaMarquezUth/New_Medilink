<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Servicios') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <a href="{{ route('servicios.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Nuevo Servicio
                </a>
                
                <table class="table-auto w-full mt-6">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">Nombre</th>
                            <th class="text-left py-2">Duración (min)</th>
                            <th class="text-left py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($servicios as $servicio)
                        <tr class="border-b">
                            <td class="py-2">{{ $servicio->nombre }}</td>
                            <td class="py-2">{{ $servicio->duracion_minutos }}</td>
                            <td class="py-2">
                                <a href="{{ route('servicios.edit', $servicio->id) }}" class="text-yellow-600 hover:text-yellow-900 mr-3">Editar</a>
                                <form action="{{ route('servicios.destroy', $servicio->id) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>