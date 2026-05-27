<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Paciente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow sm:rounded-lg">
                <form action="{{ route('pacientes.update', $paciente->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">Nombre</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $paciente->nombre) }}" class="w-full border rounded p-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Apellido</label>
                            <input type="text" name="apellido" value="{{ old('apellido', $paciente->apellido) }}" class="w-full border rounded p-2" required>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">Email</label>
                            <input type="email" name="email" value="{{ old('email', $paciente->email) }}" class="w-full border rounded p-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Teléfono</label>
                            <input type="text" name="telefono" value="{{ old('telefono', $paciente->telefono) }}" class="w-full border rounded p-2" required>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">Fecha Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $paciente->fecha_nacimiento) }}" class="w-full border rounded p-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Género</label>
                            <input type="text" name="genero" value="{{ old('genero', $paciente->genero) }}" class="w-full border rounded p-2" required>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium">Tipo Sangre</label>
                        <input type="text" name="tipo_sangre" value="{{ old('tipo_sangre', $paciente->tipo_sangre) }}" class="w-full border rounded p-2">
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium">Alergias</label>
                        <textarea name="alergias" class="w-full border rounded p-2">{{ old('alergias', $paciente->alergias) }}</textarea>
                    </div>

                    <div class="flex justify-end mt-6">
                        <a href="{{ route('pacientes.index') }}" class="mr-4 text-gray-600 hover:text-gray-800">Cancelar</a>
                        <button type="submit" class="bg-indigo-600 text-black px-4 py-2 rounded">Actualizar Paciente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>