<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'duration_minutes',
        'price',
        'is_active',
        'color',
        'category',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Obtener las citas asociadas al servicio.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Filtrar solo servicios activos.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Obtener la duración formateada.
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}min";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes} minutos";
        }
    }

    /**
     * Obtener el precio formateado.
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->price > 0) {
            return '$' . number_format($this->price, 2);
        }
        return 'Gratuito';
    }

    /**
     * Obtener el estado en texto.
     */
    public function getStatusTextAttribute(): string
    {
        return $this->is_active ? 'Activo' : 'Inactivo';
    }

    /**
     * Obtener el color del estado.
     */
    public function getStatusColorAttribute(): string
    {
        return $this->is_active ? 'green' : 'red';
    }
}