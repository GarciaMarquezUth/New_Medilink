<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paciente extends Model
{
    public const REQUIRED_PROFILE_FIELDS = [
        'fecha_nacimiento',
        'genero',
        'telefono',
        'direccion',
        'tipo_sangre',
        'alergias',
    ];

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
        'contacto_emergencia',
        'telefono_emergencia',
        'user_id',
    ];

    public function isProfileComplete(): bool
    {
        foreach (self::REQUIRED_PROFILE_FIELDS as $field) {
            if (blank($this->{$field})) {
                return false;
            }
        }

        return true;
    }

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
