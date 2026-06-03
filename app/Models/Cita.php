<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cita extends Model
{
    public const ESTADO_AGENDADA = 'agendada';

    public const ESTADO_CONFIRMADA = 'confirmada';

    public const ESTADO_CANCELADA = 'cancelada';

    public const ESTADO_ATENDIDA = 'atendida';

    public const ESTADO_NO_SHOW = 'no_show';

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'medico_id',
        'paciente_id',
        'servicio_id',
        'fecha_hora',
        'motivo',
        'estado',
        'recordatorio_enviado_at',
    ];

    protected $attributes = [
        'estado' => self::ESTADO_AGENDADA,
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'recordatorio_enviado_at' => 'datetime',
        ];
    }

    public static function estados(): array
    {
        return [
            self::ESTADO_AGENDADA => 'Agendada',
            self::ESTADO_CONFIRMADA => 'Confirmada',
            self::ESTADO_CANCELADA => 'Cancelada',
            self::ESTADO_ATENDIDA => 'Atendida',
            self::ESTADO_NO_SHOW => 'No show',
        ];
    }

    public static function estadosOcupantes(): array
    {
        return [self::ESTADO_AGENDADA, self::ESTADO_CONFIRMADA];
    }

    /**
     * Obtener el médico que atiende la cita.
     */
    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }

    /**
     * Obtener el paciente de la cita.
     */
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }
}
