<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\Disponibilidad;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Servicio;
use App\Models\User;
use App\Support\ClinicPermissionCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
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

        foreach (ClinicPermissionCatalog::allPermissionNames() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (array_keys(ClinicPermissionCatalog::roles()) as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions(ClinicPermissionCatalog::defaultPermissionsForRole($roleName));
        }

        Role::findOrCreate('paciente', 'web');

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

        $servicios = collect([
            ['nombre' => 'Consulta general', 'descripcion' => 'Evaluación médica general y orientación inicial.', 'duracion_minutos' => 30, 'precio' => 100],
            ['nombre' => 'Control cardiológico', 'descripcion' => 'Consulta especializada de seguimiento cardiovascular.', 'duracion_minutos' => 45, 'precio' => 180],
            ['nombre' => 'Pediatría', 'descripcion' => 'Atención médica para niños y adolescentes.', 'duracion_minutos' => 30, 'precio' => 130],
            ['nombre' => 'Dermatología', 'descripcion' => 'Diagnóstico y tratamiento de condiciones de la piel.', 'duracion_minutos' => 30, 'precio' => 150],
            ['nombre' => 'Odontología', 'descripcion' => 'Evaluación dental preventiva y atención básica.', 'duracion_minutos' => 40, 'precio' => 160],
            ['nombre' => 'Ginecología', 'descripcion' => 'Consulta especializada de salud femenina.', 'duracion_minutos' => 45, 'precio' => 190],
            ['nombre' => 'Nutrición', 'descripcion' => 'Plan nutricional y seguimiento de hábitos alimentarios.', 'duracion_minutos' => 30, 'precio' => 120],
            ['nombre' => 'Psicología', 'descripcion' => 'Sesión de orientación y acompañamiento psicológico.', 'duracion_minutos' => 50, 'precio' => 170],
            ['nombre' => 'Traumatología', 'descripcion' => 'Evaluación de lesiones, dolor muscular y articulaciones.', 'duracion_minutos' => 40, 'precio' => 200],
            ['nombre' => 'Laboratorio clínico', 'descripcion' => 'Toma de muestras y orientación de resultados básicos.', 'duracion_minutos' => 20, 'precio' => 90],
        ])->map(fn (array $data) => Servicio::updateOrCreate(
            ['nombre' => $data['nombre']],
            [...$data, 'activo' => true]
        ))->values();

        $medicosData = [
            ['nombre' => 'Andrea', 'apellido' => 'Pérez', 'especialidad' => 'Medicina general', 'email' => 'medico@example.com', 'telefono' => '555-0101'],
            ['nombre' => 'Carlos', 'apellido' => 'Ramírez', 'especialidad' => 'Cardiología', 'email' => 'medico2@example.com', 'telefono' => '555-0102'],
            ['nombre' => 'María', 'apellido' => 'González', 'especialidad' => 'Pediatría', 'email' => 'medico3@example.com', 'telefono' => '555-0103'],
            ['nombre' => 'Luis', 'apellido' => 'Torres', 'especialidad' => 'Dermatología', 'email' => 'medico4@example.com', 'telefono' => '555-0104'],
            ['nombre' => 'Sofía', 'apellido' => 'Martínez', 'especialidad' => 'Odontología', 'email' => 'medico5@example.com', 'telefono' => '555-0105'],
            ['nombre' => 'Daniel', 'apellido' => 'Hernández', 'especialidad' => 'Ginecología', 'email' => 'medico6@example.com', 'telefono' => '555-0106'],
            ['nombre' => 'Valeria', 'apellido' => 'López', 'especialidad' => 'Nutrición', 'email' => 'medico7@example.com', 'telefono' => '555-0107'],
            ['nombre' => 'Javier', 'apellido' => 'Castro', 'especialidad' => 'Psicología', 'email' => 'medico8@example.com', 'telefono' => '555-0108'],
            ['nombre' => 'Paula', 'apellido' => 'Morales', 'especialidad' => 'Traumatología', 'email' => 'medico9@example.com', 'telefono' => '555-0109'],
            ['nombre' => 'Miguel', 'apellido' => 'Vargas', 'especialidad' => 'Laboratorio clínico', 'email' => 'medico10@example.com', 'telefono' => '555-0110'],
        ];

        $medicos = collect($medicosData)->map(function (array $data, int $index) use ($servicios) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['nombre'].' '.$data['apellido'],
                    'password' => Hash::make('password'),
                ]
            );
            $user->syncRoles(['medico']);

            $medico = Medico::updateOrCreate(
                ['email' => $data['email']],
                [
                    'nombre' => $data['nombre'],
                    'apellido' => $data['apellido'],
                    'especialidad' => $data['especialidad'],
                    'telefono' => $data['telefono'],
                    'user_id' => $user->id,
                ]
            );

            $servicioIds = $servicios
                ->slice($index, 3)
                ->merge($servicios->take(max(0, $index + 3 - $servicios->count())))
                ->pluck('id')
                ->all();
            $medico->servicios()->sync($servicioIds);

            return $medico;
        })->values();

        $pacientesData = [
            ['nombre' => 'Paciente', 'apellido' => 'Demo', 'email' => 'paciente@example.com', 'telefono' => '555-0201', 'genero' => 'Otro', 'tipo_sangre' => 'O+'],
            ['nombre' => 'Ana', 'apellido' => 'Flores', 'email' => 'paciente2@example.com', 'telefono' => '555-0202', 'genero' => 'Femenino', 'tipo_sangre' => 'A+'],
            ['nombre' => 'Roberto', 'apellido' => 'Mendoza', 'email' => 'paciente3@example.com', 'telefono' => '555-0203', 'genero' => 'Masculino', 'tipo_sangre' => 'B+'],
            ['nombre' => 'Lucía', 'apellido' => 'Herrera', 'email' => 'paciente4@example.com', 'telefono' => '555-0204', 'genero' => 'Femenino', 'tipo_sangre' => 'AB+'],
            ['nombre' => 'Pedro', 'apellido' => 'Rojas', 'email' => 'paciente5@example.com', 'telefono' => '555-0205', 'genero' => 'Masculino', 'tipo_sangre' => 'O-'],
            ['nombre' => 'Carolina', 'apellido' => 'Navarro', 'email' => 'paciente6@example.com', 'telefono' => '555-0206', 'genero' => 'Femenino', 'tipo_sangre' => 'A-'],
            ['nombre' => 'Hugo', 'apellido' => 'Santos', 'email' => 'paciente7@example.com', 'telefono' => '555-0207', 'genero' => 'Masculino', 'tipo_sangre' => 'B-'],
            ['nombre' => 'Elena', 'apellido' => 'Campos', 'email' => 'paciente8@example.com', 'telefono' => '555-0208', 'genero' => 'Femenino', 'tipo_sangre' => 'AB-'],
            ['nombre' => 'Mario', 'apellido' => 'Ortega', 'email' => 'paciente9@example.com', 'telefono' => '555-0209', 'genero' => 'Masculino', 'tipo_sangre' => 'No sé'],
            ['nombre' => 'Isabel', 'apellido' => 'Reyes', 'email' => 'paciente10@example.com', 'telefono' => '555-0210', 'genero' => 'Femenino', 'tipo_sangre' => 'O+'],
        ];

        $pacientes = collect($pacientesData)->map(function (array $data, int $index) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['nombre'].' '.$data['apellido'],
                    'password' => Hash::make('password'),
                ]
            );
            $user->syncRoles(['paciente']);

            return Paciente::updateOrCreate(
                ['email' => $data['email']],
                [
                    'nombre' => $data['nombre'],
                    'apellido' => $data['apellido'],
                    'fecha_nacimiento' => sprintf('%d-01-%02d', 1990 - $index, $index + 1),
                    'genero' => $data['genero'],
                    'telefono' => $data['telefono'],
                    'direccion' => 'Calle Demo '.($index + 1).' #123',
                    'tipo_sangre' => $data['tipo_sangre'],
                    'alergias' => $index % 3 === 0 ? 'Penicilina' : 'Ninguna conocida',
                    'contacto_emergencia' => 'Contacto '.$data['nombre'],
                    'telefono_emergencia' => '555-03'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                    'user_id' => $user->id,
                ]
            );
        })->values();

        $medicos->each(function (Medico $medico, int $index) {
            $diaSemana = ($index % 5) + 1;

            Disponibilidad::updateOrCreate(
                ['medico_id' => $medico->id, 'dia_semana' => $diaSemana],
                ['hora_inicio' => '08:00', 'hora_fin' => '14:00', 'activo' => true]
            );
        });

        foreach (range(0, 9) as $index) {
            /** @var Medico $medico */
            $medico = $medicos[$index];
            /** @var Paciente $paciente */
            $paciente = $pacientes[$index];
            /** @var Servicio $servicio */
            $servicio = $medico->servicios()->orderBy('servicios.id')->first() ?: $servicios[$index];
            $diaSemana = ($index % 5) + 1;
            $fechaCita = now()->addDays(7 + $index);

            while ((int) $fechaCita->dayOfWeekIso !== $diaSemana) {
                $fechaCita->addDay();
            }

            Cita::updateOrCreate(
                ['motivo' => 'Cita demo '.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)],
                [
                    'medico_id' => $medico->id,
                    'paciente_id' => $paciente->id,
                    'servicio_id' => $servicio->id,
                    'fecha_hora' => $fechaCita->setTime(9 + ($index % 3), 0)->format('Y-m-d H:i:s'),
                    'estado' => Cita::ESTADO_AGENDADA,
                ]
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
