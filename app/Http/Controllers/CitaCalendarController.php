<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CitaCalendarController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User|null $user */
        $user = Auth::user();
        $month = $this->selectedMonth((string) $request->query('mes', ''));
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $query = Cita::with(['paciente', 'medico', 'servicio'])
            ->whereBetween('fecha_hora', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);

        if ($user?->hasRole('medico') && ! $user->hasAnyRole(['admin', 'recepcionista'])) {
            $query->whereHas('medico', fn ($query) => $query->where('user_id', $user->id));
        } elseif ($user?->hasRole('paciente') && ! $user->hasAnyRole(['admin', 'recepcionista', 'medico'])) {
            $query->whereHas('paciente', fn ($query) => $query->where('user_id', $user->id));
        } elseif (! $user?->can('citas.ver')) {
            abort(403, 'Acceso denegado');
        }

        $citas = $query->orderBy('fecha_hora')->get();
        $citasPorDia = $citas->groupBy(fn (Cita $cita) => $cita->fecha_hora->toDateString());
        $calendarDays = $this->calendarDays($month, $citasPorDia);

        return view('Citas.calendar', [
            'calendarDays' => $calendarDays,
            'citas' => $citas,
            'citasPorDia' => $citasPorDia,
            'estadoLabels' => Cita::estados(),
            'estadosPago' => Cita::estadosPago(),
            'month' => $month,
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
            'previousMonth' => $month->copy()->subMonth()->format('Y-m'),
        ]);
    }

    private function selectedMonth(string $value): Carbon
    {
        if (preg_match('/^\d{4}-\d{2}$/', $value) === 1) {
            return Carbon::createFromFormat('Y-m-d', $value.'-01')->startOfMonth();
        }

        return Carbon::today()->startOfMonth();
    }

    private function calendarDays(Carbon $month, $citasPorDia): array
    {
        $gridStart = $month->copy()->startOfMonth()->subDays($month->copy()->startOfMonth()->dayOfWeekIso - 1);
        $gridEnd = $month->copy()->endOfMonth()->addDays(7 - $month->copy()->endOfMonth()->dayOfWeekIso);
        $days = [];

        for ($date = $gridStart->copy(); $date->lessThanOrEqualTo($gridEnd); $date->addDay()) {
            $key = $date->toDateString();
            $days[] = [
                'date' => $date->copy(),
                'is_current_month' => $date->isSameMonth($month),
                'is_today' => $date->isToday(),
                'citas' => $citasPorDia->get($key, collect()),
            ];
        }

        return $days;
    }
}
