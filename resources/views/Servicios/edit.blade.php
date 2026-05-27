<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Encabezado --}}
            <div class="mb-8">
                <div class="flex items-center mb-2">
                    <a href="{{ route('admin.services.show', $service) }}" class="text-sm text-indigo-600 hover:text-indigo-900 flex items-center">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Volver a detalles del servicio
                    </a>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Editar Servicio</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Actualice la información del servicio <span class="font-medium">{{ $service->name }}</span>. Los campos marcados con <span class="text-red-500">*</span> son obligatorios.
                </p>
            </div>

            {{-- Formulario --}}
            <form action="{{ route('admin.services.update', $service) }}" method="POST" novalidate>
                @csrf
                @method('PUT')
                
                <div class="bg-white shadow rounded-lg divide-y divide-gray-200">
                    
                    {{-- Sección 1: Información General --}}
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Información General del Servicio</h3>
                        
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4">
                            {{-- Nombre del Servicio --}}
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">
                                    Nombre del Servicio <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1">
                                    <input type="text" name="name" id="name" 
                                           value="{{ old('name', $service->name) }}"
                                           class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md 
                                                  @error('name') text-red-900 @enderror">
                                </div>
                                @error('name')
                                    <p class="mt-2 text-sm text-red-600">
                                        <span class="font-medium">Error:</span> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Descripción --}}
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">
                                    Descripción del Servicio
                                </label>
                                <div class="mt-1">
                                    <textarea name="description" id="description" rows="3" 
                                              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md 
                                                     @error('description') text-red-900 @enderror">{{ old('description', $service->description) }}</textarea>
                                </div>
                                @error('description')
                                    <p class="mt-2 text-sm text-red-600">
                                        <span class="font-medium">Error:</span> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Categoría y Color --}}
                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-700">
                                        Categoría
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" name="category" id="category" 
                                               value="{{ old('category', $service->category) }}"
                                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md 
                                                      @error('category') text-red-900 @enderror">
                                    </div>
                                    @error('category')
                                        <p class="mt-2 text-sm text-red-600">
                                            <span class="font-medium">Error:</span> {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="color" class="block text-sm font-medium text-gray-700">
                                        Color Identificativo
                                    </label>
                                    <div class="mt-1 flex items-center space-x-3">
                                        <input type="color" name="color" id="color" 
                                               value="{{ old('color', $service->color ?? '#6366f1') }}"
                                               class="h-10 w-20 cursor-pointer border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500
                                                      @error('color') @enderror">
                                        <span class="text-sm text-gray-500">Color actual: {{ $service->color ?? 'No definido' }}</span>
                                    </div>
                                    @error('color')
                                        <p class="mt-2 text-sm text-red-600">
                                            <span class="font-medium">Error:</span> {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Sección 2: Configuración del Servicio --}}
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Configuración del Servicio</h3>
                        
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-3">
                            {{-- Duración --}}
                            <div>
                                <label for="duration_minutes" class="block text-sm font-medium text-gray-700">
                                    Duración (minutos) <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="number" name="duration_minutes" id="duration_minutes" 
                                           value="{{ old('duration_minutes', $service->duration_minutes) }}"
                                           min="5" max="480" step="5"
                                           class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md 
                                                  @error('duration_minutes') text-red-900 @enderror">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">min</span>
                                    </div>
                                </div>
                                @error('duration_minutes')
                                    <p class="mt-2 text-sm text-red-600">
                                        <span class="font-medium">Error:</span> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Precio --}}
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700">
                                    Precio (USD)
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" name="price" id="price" 
                                           value="{{ old('price', $service->price) }}"
                                           min="0" step="0.01" max="999999.99"
                                           class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 sm:text-sm border-gray-300 rounded-md 
                                                  @error('price') text-red-900 @enderror">
                                </div>
                                @error('price')
                                    <p class="mt-2 text-sm text-red-600">
                                        <span class="font-medium">Error:</span> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Estado --}}
                            <div>
                                <label for="is_active" class="block text-sm font-medium text-gray-700">
                                    Estado del Servicio
                                </label>
                                <div class="mt-1">
                                    <select name="is_active" id="is_active" 
                                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md 
                                                   @error('is_active') text-red-900 @enderror">
                                        <option value="1" {{ old('is_active', $service->is_active) == '1' ? 'selected' : '' }}>Activo</option>
                                        <option value="0" {{ old('is_active', $service->is_active) == '0' ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                </div>
                                @error('is_active')
                                    <p class="mt-2 text-sm text-red-600">
                                        <span class="font-medium">Error:</span> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Botones de Acción --}}
                    <div class="px-4 py-4 sm:px-6 bg-gray-50 rounded-b-lg">
                        <div class="flex justify-between items-center">
                            <form action="{{ route('admin.services.destroy', $service) }}" method="POST" 
                                  onsubmit="return confirm('¿Está seguro de que desea eliminar el servicio "{{ $service->name }}"?\n\nEsta acción es permanente y no se puede deshacer.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Eliminar Servicio
                                </button>
                            </form>
                            <div class="flex space-x-3">
                                <a href="{{ route('admin.services.show', $service) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Cancelar
                                </a>
                                <button type="submit" 
                                        class="inline-flex items-center px-6 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Actualizar Servicio
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>