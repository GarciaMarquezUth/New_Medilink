<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\Servicio;
use App\Models\User;
use App\Services\AppointmentAvailabilityService;
use App\Services\PendingAppointmentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PacienteCitaController extends Controller
{
    public function __construct(
        private AppointmentAvailabilityService $availability,
        private PendingAppointmentService $appointments,
    ) {}

    public function create(Request $request): View
    {
        $medicos = Medico::with(['servicios' => fn ($query) => $query->where('activo', true)->orderBy('nombre')])
            ->whereHas('servicios', fn ($query) => $query->where('activo', true)->where('duracion_minutos', '>=', 5))
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();

        $selectedMedicoId = $request->old('medico_id', $request->query('medico_id'));
        $selectedServicioId = $request->old('servicio_id', $request->query('servicio_id'));
        $selectedFecha = $request->old('fecha', $request->query('fecha'));
        $selectedHorario = $request->old('horario');
        $servicios = collect();
        $serviciosIniciales = collect();
        $fechasDisponibles = [];
        $horarios = [];

        if ($selectedMedicoId && $medicos->contains('id', (int) $selectedMedicoId)) {
            $servicios = $this->serviciosDisponiblesParaMedico((int) $selectedMedicoId);
        } else {
            $selectedMedicoId = null;
            $selectedServicioId = null;
            $selectedFecha = null;
            $selectedHorario = null;
        }

        if ($selectedServicioId && ! $servicios->contains('id', (int) $selectedServicioId)) {
            $selectedServicioId = null;
            $selectedFecha = null;
            $selectedHorario = null;
        }

        $serviciosIniciales = $servicios
            ->map(fn (Servicio $servicio) => $this->formatServicio($servicio))
            ->values();

        $slotRequest = validator([
            'medico_id' => $selectedMedicoId,
            'servicio_id' => $selectedServicioId,
            'fecha' => $selectedFecha,
        ], [
            'medico_id' => ['nullable', 'integer', 'exists:medicos,id'],
            'servicio_id' => ['nullable', 'integer', Rule::exists('servicios', 'id')->where(fn ($query) => $query->where('activo', true)->where('duracion_minutos', '>=', 5))],
            'fecha' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        if ($selectedMedicoId && $selectedServicioId && ! $slotRequest->fails()) {
            $fechasDisponibles = $this->availableDatesForView((int) $selectedMedicoId, (int) $selectedServicioId);

            if ($selectedFecha) {
                $horarios = $this->availability->availableFutureSlots((int) $selectedMedicoId, $selectedFecha, (int) $selectedServicioId);
            }
        }

        return view('Pacientes.citas.create', compact('medicos', 'servicios', 'serviciosIniciales', 'fechasDisponibles', 'selectedMedicoId', 'selectedServicioId', 'selectedFecha', 'selectedHorario', 'horarios'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $this->appointments->validateAppointmentPayload($request->all(), requirePatientFields: false);
        $payload = $this->appointments->withAuthenticatedPatientData([
            'medico_id' => $validated['medico_id'],
            'servicio_id' => $validated['servicio_id'],
            'fecha' => $validated['fecha'],
            'horario' => $validated['horario'],
            'motivo' => $validated['motivo'],
        ], $user);

        $this->appointments->createAppointment($payload, $user);

        return redirect()->route('dashboard')->with('success', PendingAppointmentService::CONFIRMED_MESSAGE);
    }

    public function serviciosPorMedico(Medico $medico): JsonResponse
    {
        return response()->json([
            'servicios' => $this->serviciosDisponiblesParaMedico($medico->id)
                ->map(fn (Servicio $servicio) => $this->formatServicio($servicio))
                ->values(),
        ]);
    }

    public function fechasDisponibles(Request $request, Medico $medico, Servicio $servicio): JsonResponse
    {
        $this->ensureActiveServiceForMedico($medico->id, $servicio->id);

        $validated = validator($request->only(['dias', 'limite']), [
            'dias' => ['nullable', 'integer', 'min:1', 'max:90'],
            'limite' => ['nullable', 'integer', 'min:1', 'max:20'],
        ])->validate();

        return response()->json([
            'fechas' => $this->availableDatesForView(
                $medico->id,
                $servicio->id,
                (int) ($validated['dias'] ?? 30),
                (int) ($validated['limite'] ?? 8),
            ),
        ]);
    }

    public function horariosDisponibles(Medico $medico, Servicio $servicio, string $fecha): JsonResponse
    {
        $this->ensureActiveServiceForMedico($medico->id, $servicio->id);

        validator(['fecha' => $fecha], [
            'fecha' => ['required', 'date', 'after_or_equal:today'],
        ])->validate();

        return response()->json([
            'fecha' => $fecha,
            'horarios' => $this->availability->availableFutureSlots($medico->id, $fecha, $servicio->id),
        ]);
    }

    private function serviciosDisponiblesParaMedico(int $medicoId)
    {
        return Servicio::where('activo', true)
            ->where('duracion_minutos', '>=', 5)
            ->whereHas('medicos', fn ($query) => $query->where('medicos.id', $medicoId))
            ->orderBy('nombre')
            ->get();
    }

    private function availableDatesForView(int $medicoId, int $servicioId, int $daysAhead = 30, int $limit = 8): array
    {
        return array_map(function (array $availableDate) {
            $date = Carbon::parse($availableDate['date']);

            return [
                'value' => $availableDate['date'],
                'label' => $this->formatDateLabel($date),
                'short_day' => $this->formatShortWeekday($date),
                'day_number' => $date->format('d'),
                'month' => $this->monthName($date),
                'slots_count' => $availableDate['slots_count'],
            ];
        }, $this->availability->availableDates($medicoId, $servicioId, $daysAhead, $limit));
    }

    private function ensureActiveServiceForMedico(int $medicoId, int $servicioId): void
    {
        $servicio = Servicio::whereKey($servicioId)
            ->where('activo', true)
            ->where('duracion_minutos', '>=', 5)
            ->first();

        if (! $servicio) {
            throw ValidationException::withMessages([
                'servicio_id' => 'Selecciona un servicio activo.',
            ]);
        }

        $this->availability->ensureMedicoCanPerformService($medicoId, $servicioId);
    }

    private function formatServicio(Servicio $servicio): array
    {
        $precio = $servicio->precio !== null ? '$'.number_format((float) $servicio->precio, 2) : null;

        return [
            'id' => $servicio->id,
            'nombre' => $servicio->nombre,
            'duracion_minutos' => $servicio->duracion_minutos,
            'precio' => $servicio->precio !== null ? (float) $servicio->precio : null,
            'label' => $servicio->nombre.' - '.$servicio->duracion_minutos.' min'.($precio ? ' - '.$precio : ''),
        ];
    }

    private function formatDateLabel(Carbon $date): string
    {
        return $this->weekdayName($date).' '.$date->day.' '.$this->monthName($date);
    }

    private function formatShortWeekday(Carbon $date): string
    {
        return [
            1 => 'Lun',
            2 => 'Mar',
            3 => 'Mié',
            4 => 'Jue',
            5 => 'Vie',
            6 => 'Sáb',
            7 => 'Dom',
        ][$date->dayOfWeekIso];
    }

    private function weekdayName(Carbon $date): string
    {
        return [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ][$date->dayOfWeekIso];
    }

    private function monthName(Carbon $date): string
    {
        return [
            1 => 'enero',
            2 => 'febrero',
            3 => 'marzo',
            4 => 'abril',
            5 => 'mayo',
            6 => 'junio',
            7 => 'julio',
            8 => 'agosto',
            9 => 'septiembre',
            10 => 'octubre',
            11 => 'noviembre',
            12 => 'diciembre',
        ][$date->month];
    }
}
