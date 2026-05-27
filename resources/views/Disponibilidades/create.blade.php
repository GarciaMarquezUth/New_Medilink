<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Agregar Horario
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form action="{{ route('medicos.disponibilidades.store', $medico->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label>Día de la semana</label>
                        <select name="dia_semana" class="w-full border rounded p-2" required>
                            <option value="1">Lunes</option>
                            <option value="2">Martes</option>
                            <option value="3">Miércoles</option>
                            <option value="4">Jueves</option>
                            <option value="5">Viernes</option>
                            <option value="6">Sábado</option>
                            <option value="7">Domingo</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label>Hora Inicio</label>
                            <input type="time" name="hora_inicio" class="w-full border rounded p-2" required>
                        </div>
                        <div>
                            <label>Hora Fin</label>
                            <input type="time" name="hora_fin" class="w-full border rounded p-2" required>
                        </div>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Guardar Horario</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>