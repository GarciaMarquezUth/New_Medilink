<x-app-layout>
    <div class="py-12 max-w-3xl mx-auto">
        <form action="{{ route('citas.update', $cita->id) }}" method="POST" class="bg-white p-6 rounded shadow">
            @csrf @method('PUT')
            
            <label>Paciente</label>
            <select name="paciente_id" class="w-full mb-4 border rounded p-2">
                @foreach($pacientes as $p) 
                    <option value="{{ $p->id }}" {{ $cita->paciente_id == $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option> 
                @endforeach
            </select>

            <label>Estado</label>
            <select name="estado" class="w-full mb-4 border rounded p-2">
                <option value="pendiente" {{ $cita->estado == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                <option value="confirmada" {{ $cita->estado == 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                <option value="cancelada" {{ $cita->estado == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
            </select>

            <button type="submit" class="bg-blue-600 text-black px-4 py-2 rounded">Actualizar Cita</button>
        </form>
    </div>
</x-app-layout>