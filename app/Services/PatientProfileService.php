<?php

namespace App\Services;

use App\Models\Paciente;
use App\Models\User;

class PatientProfileService
{
    public const INCOMPLETE_MESSAGE = 'Completa tu perfil médico para poder agendar citas.';

    public function patientFor(User $user): ?Paciente
    {
        return Paciente::where('user_id', $user->id)->first()
            ?: Paciente::where('email', $user->email)
                ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', $user->id))
                ->first();
    }

    public function ensurePatientFor(User $user, array $attributes = []): Paciente
    {
        $paciente = $this->patientFor($user);
        [$nombre, $apellido] = $this->nameParts($user, $attributes);

        if (! $paciente) {
            return Paciente::create([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'email' => $user->email,
                'telefono' => $attributes['telefono'] ?? '',
                'user_id' => $user->id,
            ]);
        }

        $updates = [];

        if (! $paciente->user_id) {
            $updates['user_id'] = $user->id;
        }

        if (! $paciente->nombre) {
            $updates['nombre'] = $nombre;
        }

        if (! $paciente->apellido) {
            $updates['apellido'] = $apellido;
        }

        if (! $paciente->email) {
            $updates['email'] = $user->email;
        }

        if (! $paciente->telefono && ! empty($attributes['telefono'])) {
            $updates['telefono'] = $attributes['telefono'];
        }

        if ($updates !== []) {
            $paciente->update($updates);
        }

        return $paciente->refresh();
    }

    public function requiresCompletion(User $user): bool
    {
        if (! $user->hasRole('paciente')) {
            return false;
        }

        return ! $this->patientFor($user)?->isProfileComplete();
    }

    private function nameParts(User $user, array $attributes): array
    {
        $parts = explode(' ', trim($user->name), 2);

        return [
            $attributes['nombre'] ?? ($parts[0] ?: 'Paciente'),
            $attributes['apellido'] ?? (trim($parts[1] ?? '') ?: 'Registrado'),
        ];
    }
}
