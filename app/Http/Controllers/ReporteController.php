<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $baseQuery = Cita::with(['medico', 'paciente', 'servicio'])
            ->whereBetween('fecha_hora', [$filters['desde']->copy()->startOfDay(), $filters['hasta']->copy()->endOfDay()]);

        $citas = (clone $baseQuery)->orderByDesc('fecha_hora')->get();
        $totalCitas = $citas->count();
        $ingresosRegistrados = (float) $citas->sum(fn (Cita $cita) => (float) ($cita->monto_pagado ?? 0));
        $citasPagadas = $citas->where('estado_pago', Cita::PAGO_PAGADO)->count();
        $citasPendientesPago = $citas->whereIn('estado_pago', [null, Cita::PAGO_PENDIENTE, Cita::PAGO_PARCIAL])->count();

        return view('Reportes.index', [
            'filters' => [
                'fecha_desde' => $filters['desde']->toDateString(),
                'fecha_hasta' => $filters['hasta']->toDateString(),
            ],
            'totalCitas' => $totalCitas,
            'ingresosRegistrados' => $ingresosRegistrados,
            'citasPagadas' => $citasPagadas,
            'citasPendientesPago' => $citasPendientesPago,
            'citasPorEstado' => $this->countsBy($citas, 'estado', Cita::estados()),
            'citasPorPago' => $this->countsBy($citas, 'estado_pago', Cita::estadosPago(), Cita::PAGO_PENDIENTE),
            'citasPorMedico' => $this->topMedicos($citas),
            'citasPorServicio' => $this->topServicios($citas),
            'citasRecientes' => $citas->take(8),
        ]);
    }

    private function filters(Request $request): array
    {
        $desde = $request->filled('fecha_desde')
            ? Carbon::parse((string) $request->query('fecha_desde'))
            : Carbon::today()->startOfMonth();
        $hasta = $request->filled('fecha_hasta')
            ? Carbon::parse((string) $request->query('fecha_hasta'))
            : Carbon::today()->endOfMonth();

        if ($desde->greaterThan($hasta)) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        return ['desde' => $desde, 'hasta' => $hasta];
    }

    private function countsBy($citas, string $field, array $labels, ?string $default = null): array
    {
        return collect($labels)
            ->map(function (string $label, string $key) use ($citas, $field, $default) {
                return [
                    'key' => $key,
                    'label' => $label,
                    'count' => $citas->filter(fn (Cita $cita) => ($cita->{$field} ?: $default) === $key)->count(),
                ];
            })
            ->values()
            ->all();
    }

    private function topMedicos($citas): array
    {
        return $citas->groupBy('medico_id')
            ->map(function ($items) {
                /** @var Cita $cita */
                $cita = $items->first();

                return [
                    'label' => trim(($cita->medico?->nombre ?? '').' '.($cita->medico?->apellido ?? '')) ?: 'Sin médico',
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(8)
            ->values()
            ->all();
    }

    private function topServicios($citas): array
    {
        return $citas->groupBy('servicio_id')
            ->map(function ($items) {
                /** @var Cita $cita */
                $cita = $items->first();

                return [
                    'label' => $cita->servicio?->nombre ?? 'Sin servicio',
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(8)
            ->values()
            ->all();
    }
}
