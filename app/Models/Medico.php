<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Medico extends Model
{
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'especialidad',
        'telefono',
        'user_id',
        'photo_path',
    ];

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path || ! Storage::disk('public')->exists($this->photo_path)) {
            return null;
        }

        return asset('storage/'.ltrim(str_replace('\\', '/', $this->photo_path), '/'));
    }

    public function getInitialsAttribute(): string
    {
        $nombre = Str::of($this->nombre)->trim()->substr(0, 1);
        $apellido = Str::of($this->apellido)->trim()->substr(0, 1);
        $initials = $nombre->toString().$apellido->toString();

        return Str::upper($initials ?: 'DR');
    }

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
