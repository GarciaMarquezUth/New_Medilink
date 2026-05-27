<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servicio extends Model
{
    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'nombre',
        'duracion_minutos',
    ];

    /**
     * Un servicio puede tener muchas citas asociadas.
     */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }
}