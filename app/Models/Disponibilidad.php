<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disponibilidad extends Model
{
    // Esta línea corrige el error de "Table doesn't exist"
    protected $table = 'disponibilidades';

    protected $fillable = [
        'medico_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
    ];

    /**
     * Una disponibilidad pertenece a un médico.
     */
    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }
}