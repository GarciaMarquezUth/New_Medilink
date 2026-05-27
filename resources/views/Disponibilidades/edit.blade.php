<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Horario
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form action="{{ route('medicos.disponibilidades.update', [$medico->id, $disponibilidad->id]) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="time" name="hora_inicio" value="{{ $disponibilidad->hora_inicio }}" class="w-full border rounded p-2" required>
                    <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>