<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Lista de Citas</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <a href="{{ route('citas.create') }}" class="bg-blue-600 text-black px-4 py-2 rounded">Nueva Cita</a>
                
                <table class="w-full mt-6 border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="p-3">Paciente</th>
                            <th class="p-3">Médico</th>
                            <th class="p-3">Fecha</th>
                            <th class="p-3">Estado</th>
                            <th class="p-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($citas as $cita)
                        <tr class="border-b">
                            <td class="p-3">{{ $cita->paciente->nombre }} {{ $cita->paciente->apellido }}</td>
                            <td class="p-3">{{ $cita->medico->nombre }}</td>
                            <td class="p-3">{{ $cita->fecha_hora }}</td>
                            <td class="p-3 capitalize">{{ $cita->estado }}</td>
                            <td class="p-3 flex gap-2">
                                <a href="{{ route('citas.edit', $cita->id) }}" class="text-blue-500">Editar</a>
                                <form action="{{ route('citas.destroy', $cita->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500" onclick="return confirm('¿Borrar?')">Eliminar</button>
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