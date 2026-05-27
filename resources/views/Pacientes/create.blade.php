<x-app-layout>
    <div class="py-12 max-w-3xl mx-auto">
        <div class="bg-white p-6 shadow sm:rounded-lg">
            <h2 class="text-xl font-bold mb-4">Registrar Paciente</h2>
            <form action="{{ route('pacientes.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div><label>Nombre</label><input type="text" name="nombre" class="w-full border rounded p-2" required></div>
                    <div><label>Apellido</label><input type="text" name="apellido" class="w-full border rounded p-2" required></div>
                </div>
                <div class="mt-4"><label>Email</label><input type="email" name="email" class="w-full border rounded p-2" required></div>
                <div class="mt-4"><label>Teléfono</label><input type="text" name="telefono" class="w-full border rounded p-2" required></div>
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div><label>Fecha Nac.</label><input type="date" name="fecha_nacimiento" class="w-full border rounded p-2" required></div>
                    <div><label>Género</label><input type="text" name="genero" class="w-full border rounded p-2" required></div>
                </div>
                <div class="mt-4"><label>Alergias</label><textarea name="alergias" class="w-full border rounded p-2"></textarea></div>
                <button type="submit" class="mt-4 bg-green-600 text-white px-4 py-2 rounded">Guardar</button>
            </form>
        </div>
    </div>
</x-app-layout>