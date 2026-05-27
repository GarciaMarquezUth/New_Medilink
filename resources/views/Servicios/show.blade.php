<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
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
                    </div>
                </div>
            @endif

            {{-- Encabezado --}}
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center mb-2">
                        <a href="{{ route('admin.services.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Volver al listado de servicios
                        </a>
                    </div>
                    <div class="flex items-center space-x-3">
                        @if($service->color)
                            <div class="h-8 w-8 rounded-full border-2 border-gray-300 flex-shrink-0" 
                                 style="background-color: {{ $service->color }}"></div>
                        @endif
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            {{ $service->name }}
                        </h2>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        Servicio registrado el {{ $service->created_at->format('d \d\e F \d\e Y') }}
                    </p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                    <a href="{{ route('admin.services.edit', $service) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar Servicio
                    </a>
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
                </div>
            </div>

            {{-- Tarjetas de Estadísticas --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                {{-- Total Citas --}}
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-gray-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total de Citas</dt>
                                    <dd class="text-2xl font-semibold text-gray-900">{{ $stats['total_appointments'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Citas del Mes --}}
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Este Mes</dt>
                                    <dd class="text-2xl font-semibold text-blue-600">{{ $stats['appointments_this_month'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Citas Activas --}}
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Citas Pendientes</dt>
                                    <dd class="text-2xl font-semibold text-yellow-600">{{ $stats['active_appointments'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Citas Atendidas --}}
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Citas Atendidas</dt>
                                    <dd class="text-2xl font-semibold text-green-600">{{ $stats['attended_appointments'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contenido Principal --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Columna Izquierda: Detalles del Servicio --}}
                <div class="lg:col-span-1">
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Detalles del Servicio
                            </h3>
                            
                            <dl class="space-y-4">
                                <div class="border-b border-gray-100 pb-3">
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Nombre</dt>
                                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $service->name }}</dd>
                                </div>

                                @if($service->category)
                                <div class="border-b border-gray-100 pb-3">
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Categoría</dt>
                                    <dd class="mt-1">
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                            {{ $service->category }}
                                        </span>
                                    </dd>
                                </div>
                                @endif

                                <div class="border-b border-gray-100 pb-3">
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Duración</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $service->formatted_duration }}</dd>
                                </div>

                                <div class="border-b border-gray-100 pb-3">
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Precio</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $service->formatted_price }}</dd>
                                </div>

                                <div class="border-b border-gray-100 pb-3">
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Estado</dt>
                                    <dd class="mt-1">
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            bg-{{ $service->status_color }}-100 text-{{ $service->status_color }}-800">
                                            {{ $service->status_text }}
                                        </span>
                                    </dd>
                                </div>

                                @if($service->color)
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Color Identificativo</dt>
                                    <dd class="mt-1 flex items-center space-x-2">
                                        <div class="h-6 w-6 rounded-full border border-gray-300" 
                                             style="background-color: {{ $service->color }}"></div>
                                        <span class="text-sm text-gray-600">{{ $service->color }}</span>
                                    </dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>

                {{-- Columna Derecha: Descripción y Citas --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Descripción --}}
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                </svg>
                                Descripción del Servicio
                            </h3>
                            
                            <div class="prose max-w-none text-sm text-gray-700">
                                @if($service->description)
                                    <p class="whitespace-pre-line">{{ $service->description }}</p>
                                @else
                                    <div class="text-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">Sin descripción</h3>
                                        <p class="mt-1 text-sm text-gray-500">Este servicio no tiene una descripción detallada.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Historial de Citas --}}
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <svg class="h-5 w-5 text-indigo-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Citas Recientes con este Servicio
                            </h3>
                            
                            @if($service->appointments->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Fecha
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Paciente
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Doctor
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Horario
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Estado
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($service->appointments as $appointment)
                                                <tr class="hover:bg-gray-50 transition-colors">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $appointment->date->format('d/m/Y') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $appointment->patient->full_name }}
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        Dr. {{ $appointment->doctor->full_name }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $appointment->start_time->format('H:i') }} - {{ $appointment->end_time->format('H:i') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @php
                                                            $statusLabels = [
                                                                'scheduled' => 'Programada',
                                                                'confirmed' => 'Confirmada',
                                                                'attended' => 'Atendida',
                                                                'cancelled' => 'Cancelada',
                                                                'no_show' => 'No Asistió'
                                                            ];
                                                        @endphp
                                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                            bg-{{ $appointment->status->color() }}-100 
                                                            text-{{ $appointment->status->color() }}-800">
                                                            {{ $statusLabels[$appointment->status->value] ?? $appointment->status->label() }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Sin citas registradas</h3>
                                    <p class="mt-1 text-sm text-gray-500">Este servicio aún no tiene historial de citas.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>