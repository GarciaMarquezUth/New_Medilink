<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paciente extends Model
{
    protected $fillable = [
        'nombre', 
        'apellido', 
        'fecha_nacimiento', 
        'genero', 
        'email', 
        'telefono', 
        'direccion', 
        'tipo_sangre', 
        'alergias',
        'user_id' // <--- NECESARIO PARA VINCULAR CON EL USUARIO
    ];

    // Relación: Un paciente pertenece a un usuario del sistema
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relación: Un paciente tiene muchas citas
    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}