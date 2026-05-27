<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,receptionist']);
    }

    /**
     * Mostrar listado de servicios.
     */
    public function index(Request $request)
    {
        $query = Service::query();

        // Búsqueda por texto
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filtro por categoría
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $services = $query->withCount('appointments')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        // Obtener categorías únicas para el filtro
        $categories = Service::whereNotNull('category')
            ->distinct('category')
            ->pluck('category');

        return view('admin.services.index', compact('services', 'categories'));
    }

    /**
     * Mostrar formulario para crear servicio.
     */
    public function create()
    {
        return view('admin.services.create');
    }

    /**
     * Guardar nuevo servicio.
     */
    public function store(StoreServiceRequest $request)
    {
        $service = Service::create($request->validated());

        return redirect()
            ->route('admin.services.show', $service)
            ->with('success', '¡Servicio creado exitosamente!');
    }

    /**
     * Mostrar detalles del servicio.
     */
    public function show(Service $service)
    {
        $service->load(['appointments' => function ($query) {
            $query->with(['patient', 'doctor'])
                  ->orderBy('date', 'desc')
                  ->limit(10);
        }]);

        // Estadísticas del servicio
        $stats = [
            'total_appointments' => $service->appointments()->count(),
            'appointments_this_month' => $service->appointments()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->count(),
            'active_appointments' => $service->appointments()
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->count(),
            'attended_appointments' => $service->appointments()
                ->where('status', 'attended')
                ->count(),
        ];

        return view('admin.services.show', compact('service', 'stats'));
    }

    /**
     * Mostrar formulario para editar servicio.
     */
    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    /**
     * Actualizar servicio.
     */
    public function update(StoreServiceRequest $request, Service $service)
    {
        $service->update($request->validated());

        return redirect()
            ->route('admin.services.show', $service)
            ->with('success', '¡Servicio actualizado exitosamente!');
    }

    /**
     * Eliminar servicio.
     */
    public function destroy(Service $service)
    {
        // Verificar si tiene citas activas
        if ($service->appointments()->whereIn('status', ['scheduled', 'confirmed'])->exists()) {
            return back()->withErrors([
                'error' => 'No se puede eliminar un servicio con citas activas. Por favor, cancele o reasigne las citas pendientes primero.'
            ]);
        }

        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('success', '¡Servicio eliminado exitosamente!');
    }
}