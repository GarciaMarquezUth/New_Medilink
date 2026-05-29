<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medico extends Model
{
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'especialidad',
        'telefono',
        'user_id',
    ];

    // Relación: Un médico pertenece a un usuario del sistema
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relación: Un médico tiene muchas citas
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }

    public function disponibilidades(): HasMany
    {
        return $this->hasMany(Disponibilidad::class);
    }

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'medico_servicio')->withTimestamps();
    }
}
