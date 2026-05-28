<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cita extends Model
{
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
        'estado_pago',
        'monto_pagado',
        'fecha_pago',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'monto_pagado' => 'decimal:2',
            'fecha_pago' => 'datetime',
        ];
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
