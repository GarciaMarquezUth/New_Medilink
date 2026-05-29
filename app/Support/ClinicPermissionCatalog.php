<?php

namespace App\Support;

class ClinicPermissionCatalog
{
    public static function roles(): array
    {
        return [
            'admin' => 'Admin',
            'medico' => 'Médico',
            'recepcionista' => 'Recepcionista',
        ];
    }

    public static function modules(): array
    {
        return [
            'medicos' => 'Médicos',
            'pacientes' => 'Pacientes',
            'citas' => 'Citas',
            'servicios' => 'Servicios',
            'disponibilidades' => 'Disponibilidades',
        ];
    }

    public static function actions(): array
    {
        return [
            'ver' => 'Ver',
            'crear' => 'Crear',
            'editar' => 'Editar',
            'eliminar' => 'Eliminar',
        ];
    }

    public static function permissionName(string $module, string $action): string
    {
        return "{$module}.{$action}";
    }

    public static function allPermissionNames(): array
    {
        $permissions = [];

        foreach (array_keys(self::modules()) as $module) {
            foreach (array_keys(self::actions()) as $action) {
                $permissions[] = self::permissionName($module, $action);
            }
        }

        return $permissions;
    }

    public static function defaultPermissionsForRole(string $role): array
    {
        if (in_array($role, ['admin', 'recepcionista'], true)) {
            return self::allPermissionNames();
        }

        if ($role === 'medico') {
            return [
                self::permissionName('citas', 'ver'),
                self::permissionName('citas', 'editar'),
            ];
        }

        return [];
    }
}
