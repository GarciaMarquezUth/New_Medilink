<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    // Asegúrate de incluir todos los campos que se guardarán desde el formulario
    protected $fillable = [
        'nombre', 
        'apellido', 
        'email', 
        'especialidad', 
        'telefono'
    ];
}