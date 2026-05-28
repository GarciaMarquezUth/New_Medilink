<?php

namespace Database\Seeders;

use App\Models\Servicio;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['admin', 'recepcionista', 'medico', 'paciente'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $users = [
            ['name' => 'Administrador', 'email' => 'admin@example.com', 'role' => 'admin'],
            ['name' => 'Recepcionista', 'email' => 'recepcionista@example.com', 'role' => 'recepcionista'],
            ['name' => 'Médico', 'email' => 'medico@example.com', 'role' => 'medico'],
            ['name' => 'Paciente', 'email' => 'paciente@example.com', 'role' => 'paciente'],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                ]
            );

            $user->syncRoles([$data['role']]);
        }

        foreach ([
            ['nombre' => 'Consulta general', 'duracion_minutos' => 30, 'precio' => 500],
            ['nombre' => 'Consulta especializada', 'duracion_minutos' => 45, 'precio' => 750],
            ['nombre' => 'Control de seguimiento', 'duracion_minutos' => 20, 'precio' => 350],
        ] as $servicio) {
            Servicio::updateOrCreate(
                ['nombre' => $servicio['nombre']],
                [
                    'descripcion' => 'Servicio clínico disponible para agenda.',
                    'duracion_minutos' => $servicio['duracion_minutos'],
                    'precio' => $servicio['precio'],
                    'activo' => true,
                ],
            );
        }
    }
}
