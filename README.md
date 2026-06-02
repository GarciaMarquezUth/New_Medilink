# MediLink - Sistema Clinico

MediLink es una aplicacion Laravel para gestionar la operacion basica de una clinica: medicos, pacientes, servicios, disponibilidad semanal y citas.

## Stack

- Laravel 12
- PHP 8.2 o superior
- Livewire 3 y Volt
- Blade y Tailwind CSS
- Spatie Laravel Permission
- Vite
- PHPUnit

## Modulos Principales

- Portal publico de citas: permite seleccionar medico, servicio, fecha y horario.
- Dashboard: cambia segun el rol del usuario.
- Medicos: gestion de perfiles profesionales, foto, usuario vinculado y servicios.
- Pacientes: gestion de datos personales y perfil medico.
- Servicios: catalogo de atenciones con duracion, precio y estado.
- Disponibilidades: horarios semanales por medico.
- Citas: agenda clinica con validacion de disponibilidad y traslapes.
- Permisos: matriz administrativa para activar o desactivar permisos por rol.

## Roles

| Rol | Descripcion |
| --- | --- |
| admin | Administra permisos, medicos, pacientes, servicios, disponibilidades y citas. |
| recepcionista | Gestiona operacion diaria de agenda, pacientes, medicos y servicios. |
| medico | Consulta su agenda, gestiona su disponibilidad y marca citas propias como atendidas o no presentadas. |
| paciente | Completa su perfil, agenda citas y cancela citas futuras propias. |

## Instalacion Local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

Para desarrollo completo:

```bash
composer run dev
```

Si usas fotos de medicos, crea el enlace publico de storage:

```bash
php artisan storage:link
```

## Usuarios Demo

Despues de ejecutar `php artisan migrate --seed`, puedes entrar con:

| Rol | Email | Password |
| --- | --- | --- |
| Admin | admin@example.com | password |
| Recepcionista | recepcionista@example.com | password |
| Medico | medico@example.com | password |
| Paciente | paciente@example.com | password |

El seeder tambien crea 10 servicios, 10 medicos, 10 pacientes, 10 disponibilidades y 10 citas demo.

## Flujo De Citas

1. El paciente entra a `/portal-citas`.
2. Selecciona medico, servicio, fecha y horario.
3. El sistema valida que el servicio pertenezca al medico.
4. El sistema valida que el horario este dentro de la disponibilidad del medico.
5. El sistema valida que no exista traslape con otra cita agendada o confirmada.
6. Si el usuario es invitado, la cita queda pendiente en sesion hasta que inicie sesion o se registre.
7. Antes de confirmar, el sistema revalida el horario para evitar dobles reservas.

## Estados De Cita

- `agendada`
- `confirmada`
- `cancelada`
- `atendida`
- `no_show`

Solo `agendada` y `confirmada` ocupan horario en la agenda.

## Pruebas

```bash
vendor/bin/phpunit
```

Pruebas utiles por area:

```bash
vendor/bin/phpunit --filter PatientPortalTest
vendor/bin/phpunit --filter RoleAccessTest
vendor/bin/phpunit --filter PermissionManagementTest
```

## Archivos Clave

- `routes/web.php`: rutas publicas, dashboard y recursos protegidos.
- `routes/auth.php`: login, registro y paginas de autenticacion Volt.
- `app/Http/Controllers/DashboardController.php`: datos del dashboard por rol.
- `app/Services/AppointmentAvailabilityService.php`: calculo y validacion de horarios.
- `app/Services/PendingAppointmentService.php`: citas pendientes de invitados.
- `app/Services/PatientProfileService.php`: perfil medico del paciente.
- `app/Support/ClinicPermissionCatalog.php`: catalogo de permisos por modulo y rol.
- `resources/views/PortalCitas/create.blade.php`: portal publico de agendamiento.
- `resources/views/dashboard.blade.php`: dashboard por rol.

## Notas Tecnicas

- Las pantallas de login y registro activas son Livewire Volt.
- Los listados principales tienen filtros y paginacion.
- El middleware de email verificado no se exige en el dashboard; la pantalla de verificacion queda disponible si se decide activar ese flujo mas adelante.
- El rol medico no puede crear, editar ni eliminar citas desde el CRUD administrativo; solo puede cerrar sus propias citas como atendidas o no presentadas.
