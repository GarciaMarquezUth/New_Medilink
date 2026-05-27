<x-app-layout>
    <div class="py-12 max-w-3xl mx-auto">
        <form action="{{ route('citas.store') }}" method="POST" class="bg-white p-6 rounded shadow">
            @csrf
            <label>Paciente</label>
            <select name="paciente_id" class="w-full mb-4 border rounded p-2">
                @foreach($pacientes as $p) <option value="{{ $p->id }}">{{ $p->nombre }}</option> @endforeach
            </select>

            <label>Médico</label>
            <select name="medico_id" class="w-full mb-4 border rounded p-2">
                @foreach($medicos as $m) <option value="{{ $m->id }}">{{ $m->nombre }}</option> @endforeach
            </select>

            <label>Fecha y Hora</label>
            <input type="datetime-local" name="fecha_hora" class="w-full mb-4 border rounded p-2" required>

            <label>Motivo</label>
            <textarea name="motivo" class="w-full mb-4 border rounded p-2" required></textarea>

            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Guardar Cita</button>
        </form>
    </div>
</x-app-layout>