<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'receptionist']);
    }

    /**
     * Obtener las reglas de validación que se aplican a la solicitud.
     */
    public function rules(): array
    {
        $serviceId = $this->route('service')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:200',
                Rule::unique('services', 'name')->ignore($serviceId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'duration_minutes' => [
                'required',
                'integer',
                'min:5',
                'max:480',
            ],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'is_active' => ['nullable', 'boolean'],
            'color' => ['nullable', 'string', 'max:7'],
            'category' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Obtener los mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            // Nombre del servicio
            'name.required' => 'El nombre del servicio es obligatorio.',
            'name.string' => 'El nombre del servicio debe ser un texto válido.',
            'name.max' => 'El nombre del servicio no debe exceder los 200 caracteres.',
            'name.unique' => 'Ya existe un servicio registrado con este nombre.',
            
            // Descripción
            'description.string' => 'La descripción debe ser un texto válido.',
            'description.max' => 'La descripción no debe exceder los 2000 caracteres.',
            
            // Duración
            'duration_minutes.required' => 'La duración del servicio es obligatoria.',
            'duration_minutes.integer' => 'La duración debe ser un número entero.',
            'duration_minutes.min' => 'La duración mínima es de 5 minutos.',
            'duration_minutes.max' => 'La duración máxima es de 480 minutos (8 horas).',
            
            // Precio
            'price.numeric' => 'El precio debe ser un valor numérico.',
            'price.min' => 'El precio no puede ser negativo.',
            'price.max' => 'El precio no puede exceder $999,999.99.',
            
            // Estado
            'is_active.boolean' => 'El estado debe ser verdadero o falso.',
            
            // Color
            'color.string' => 'El color debe ser un texto válido.',
            'color.max' => 'El color no debe exceder los 7 caracteres.',
            
            // Categoría
            'category.string' => 'La categoría debe ser un texto válido.',
            'category.max' => 'La categoría no debe exceder los 100 caracteres.',
        ];
    }

    /**
     * Obtener los nombres de atributos personalizados.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre del servicio',
            'description' => 'descripción',
            'duration_minutes' => 'duración',
            'price' => 'precio',
            'is_active' => 'estado',
            'color' => 'color',
            'category' => 'categoría',
        ];
    }

    /**
     * Preparar los datos para la validación.
     */
    protected function prepareForValidation(): void
    {
        // Asegurar que is_active sea booleano
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // Limpiar el nombre
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }
    }
}