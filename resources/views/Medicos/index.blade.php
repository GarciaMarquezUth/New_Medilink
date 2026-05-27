<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Médicos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Lista de Médicos</h1>
                    <a href="{{ route('medicos.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                        Nuevo Médico
                    </a>
                </div>
                
                <table class="w-full mt-4 border-collapse border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 p-2 text-left">Nombre</th>
                            <th class="border border-gray-300 p-2 text-left">Especialidad</th>
                            <th class="border border-gray-300 p-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($medicos as $medico)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 p-2">{{ $medico->nombre }} {{ $medico->apellido }}</td>
                            <td class="border border-gray-300 p-2">{{ $medico->especialidad }}</td>
                            <td class="border border-gray-300 p-2 text-center">
                                <a href="{{ route('medicos.edit', $medico->id) }}" class="text-yellow-600 hover:text-yellow-800 font-semibold mr-3">Editar</a>
                                
                                <a href="{{ route('medicos.disponibilidades.index', $medico->id) }}" class="text-green-600 hover:text-green-800 font-semibold mr-3">Horarios</a>
                                
                                <form action="{{ route('medicos.destroy', $medico->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este médico?');">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-semibold">Eliminar</button>
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