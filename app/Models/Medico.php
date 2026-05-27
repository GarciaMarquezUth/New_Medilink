<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medico extends Model
{
    protected $fillable = [
        'nombre', 
        'apellido', 
        'email', 
        'especialidad', 
        'telefono'
    ];

    /**
     * Un médico tiene muchas disponibilidades.
     */
    public function disponibilidades(): HasMany
    {
        // Usamos la ruta absoluta de la clase para evitar errores de carga
        return $this->hasMany(\App\Models\Disponibilidad::class);
    }
}