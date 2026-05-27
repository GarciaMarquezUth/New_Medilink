<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'alergias'
    ];
}