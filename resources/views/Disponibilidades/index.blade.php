<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Disponibilidad de: {{ $medico->nombre }} {{ $medico->apellido }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2">Día (1=Lun, 7=Dom)</th>
                            <th class="py-2">Inicio</th>
                            <th class="py-2">Fin</th>
                            <th class="py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($disponibilidades as $disp)
                        <tr class="border-b">
                            <td class="py-2">{{ $disp->dia_semana }}</td>
                            <td class="py-2">{{ $disp->hora_inicio }}</td>
                            <td class="py-2">{{ $disp->hora_fin }}</td>
                            <td class="py-2">
                                <form action="{{ route('medicos.disponibilidades.destroy', [$medico->id, $disp->id]) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:text-red-900">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <hr class="my-6">
                <h3 class="font-bold mb-4">Agregar nuevo horario</h3>
                <form action="{{ route('medicos.disponibilidades.store', $medico->id) }}" method="POST" class="flex gap-4">
                    @csrf
                    <input type="number" name="dia_semana" placeholder="Día (1-7)" class="border rounded px-2" required min="1" max="7">
                    <input type="time" name="hora_inicio" class="border rounded px-2" required>
                    <input type="time" name="hora_fin" class="border rounded px-2" required>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>