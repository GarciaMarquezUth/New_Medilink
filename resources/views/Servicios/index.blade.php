<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Encabezado --}}
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Servicios Médicos
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Gestionar todos los servicios y procedimientos disponibles en la clínica
                    </p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <a href="{{ route('admin.services.create') }}" 
                       class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nuevo Servicio
                    </a>
                </div>
            </div>

            {{-- Mensaje de éxito --}}
            @if(session('success'))
                <div class="rounded-md bg-green-50 p-4 mb-6 border border-green-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button type="button" onclick="this.parentElement.parentElement.parentElement.remove()" class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none">
                                    <span class="sr-only">Cerrar</span>
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Mensaje de error --}}
            @if($errors->any())
                <div class="rounded-md bg-red-50 p-4 mb-6 border border-red-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Se encontraron los siguientes errores:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Búsqueda y Filtros --}}
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <form action="{{ route('admin.services.index') }}" method="GET">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            {{-- Buscador --}}
                            <div class="sm:col-span-2">
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                    Buscar Servicio
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                                           class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
                                           placeholder="Buscar por nombre, descripción o categoría...">
                                </div>
                            </div>

                            {{-- Filtro por estado --}}
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                    Estado
                                </label>
                                <select name="status" id="status" 
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">Todos los estados</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                                </select>
                            </div>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="mt-4 flex items-center justify-end space-x-3">
                            @if(request()->anyFilled(['search', 'status']))
                                <a href="{{ route('admin.services.index') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Limpiar Filtros
                                </a>
                            @endif
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                Aplicar Filtros
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Contador de resultados --}}
            <div class="mb-4">
                <p class="text-sm text-gray-600">
                    Mostrando <span class="font-medium">{{ $services->firstItem() ?? 0 }}</span> 
                    a <span class="font-medium">{{ $services->lastItem() ?? 0 }}</span> 
                    de <span class="font-medium">{{ $services->total() }}</span> servicios
                </p>
            </div>

            {{-- Cuadrícula de Servicios --}}
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($services as $service)
                    <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow duration-300">
                        {{-- Cabecera de la tarjeta --}}
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3 min-w-0">
                                    @if($service->color)
                                        <div class="h-5 w-5 rounded-full flex-shrink-0 border-2 border-gray-200" 
                                             style="background-color: {{ $service->color }}"></div>
                                    @endif
                                    <h3 class="text-lg font-semibold text-gray-900 truncate">
                                        <a href="{{ route('admin.services.show', $service) }}" class="hover:text-indigo-600 transition-colors">
                                            {{ $service->name }}
                                        </a>
                                    </h3>
                                </div>
                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $service->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $service->status_text }}
                                </span>
                            </div>

                            {{-- Categoría --}}
                            @if($service->category)
                                <p class="text-xs font-medium text-indigo-600 uppercase tracking-wider mb-2">
                                    {{ $service->category }}
                                </p>
                            @endif

                            {{-- Descripción --}}
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2">
                                {{ $service->description ?? 'Sin descripción disponible' }}
                            </p>

                            {{-- Detalles --}}
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center space-x-4">
                                    {{-- Duración --}}
                                    <span class="inline-flex items-center text-gray-600">
                                        <svg class="h-4 w-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $service->formatted_duration }}
                                    </span>
                                    
                                    {{-- Precio --}}
                                    <span class="font-medium text-gray-900">
                                        {{ $service->formatted_price }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Pie de la tarjeta --}}
                        <div class="border-t border-gray-200 bg-gray-50 px-6 py-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">
                                    {{ $service->appointments_count }} {{ $service->appointments_count == 1 ? 'cita' : 'citas' }}
                                </span>
                                <div class="flex space-x-3">
                                    <a href="{{ route('admin.services.edit', $service) }}" 
                                       class="text-sm font-medium text-indigo-600 hover:text-indigo-900 transition-colors"
                                       title="Editar servicio">
                                        Editar
                                    </a>
                                    <form action="{{ route('admin.services.destroy', $service) }}" method="POST" class="inline"
                                          onsubmit="return confirm('¿Está seguro de que desea eliminar el servicio "{{ $service->name }}"?\n\nEsta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-sm font-medium text-red-600 hover:text-red-900 transition-colors"
                                                title="Eliminar servicio">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="text-center py-12 bg-white shadow rounded-lg">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900">
                                @if(request()->anyFilled(['search', 'status']))
                                    No se encontraron servicios con los filtros aplicados
                                @else
                                    No hay servicios registrados
                                @endif
                            </h3>
                            <p class="mt-2 text-sm text-gray-500">
                                @if(request()->anyFilled(['search', 'status']))
                                    Intente con diferentes criterios de búsqueda o limpie los filtros.
                                @else
                                    Comience registrando el primer servicio médico en el sistema.
                                @endif
                            </p>
                            <div class="mt-6">
                                @if(request()->anyFilled(['search', 'status']))
                                    <a href="{{ route('admin.services.index') }}" 
                                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Limpiar Filtros
                                    </a>
                                @else
                                    <a href="{{ route('admin.services.create') }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Registrar Primer Servicio
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Paginación --}}
            @if($services->hasPages())
                <div class="mt-6 bg-white shadow rounded-lg">
                    <div class="px-4 py-3 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Página {{ $services->currentPage() }} de {{ $services->lastPage() }}
                            </div>
                            {{ $services->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>