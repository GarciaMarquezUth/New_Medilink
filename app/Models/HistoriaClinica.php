<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriaClinica extends Model
{
    protected $table = 'historias_clinicas';

    protected $fillable = [
        'cita_id',
        'diagnostico',
        'tratamiento',
        'observaciones',
        'receta',
        'indicaciones',
        'seguimiento_fecha',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'seguimiento_fecha' => 'date',
        ];
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
