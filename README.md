# MediLink

MediLink es una aplicacion Laravel para gestionar la operacion basica de una clinica: usuarios con roles, medicos, pacientes, servicios, disponibilidad medica y citas.

## Stack

- PHP 8.2+
- Laravel 12
- Livewire 3 y Volt
- Laravel Breeze
- Spatie Laravel Permission
- Vite y Tailwind CSS
- PHPUnit

## Instalacion local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

El proyecto usa MySQL. Crea una base de datos local y configura tus credenciales en `.env`:

```sql
CREATE DATABASE medilink CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medilink
DB_USERNAME=root
DB_PASSWORD=
```

Despues ejecuta las migraciones y seeders:

```bash
php artisan migrate --seed
```

## Ejecutar la app

```bash
composer run dev
```

Ese comando levanta el servidor Laravel, la cola, los logs con Pail y Vite. Para solo compilar assets:

```bash
npm run dev
npm run build
```

## Usuarios de prueba

El seeder crea estos usuarios con password `password`:

- `admin@example.com` - rol `admin`
- `recepcionista@example.com` - rol `recepcionista`
- `medico@example.com` - rol `medico`
- `paciente@example.com` - rol `paciente`

Tambien crea servicios clinicos base para poder agendar citas.

## Flujo de citas

Los pacientes pueden solicitar citas desde el portal publico:

```text
/portal-paciente/citas
```

Desde esa vista seleccionan medico, servicio y fecha para consultar horarios disponibles antes de enviar sus datos.

Las citas usan estos estados:

- `agendada`
- `confirmada`
- `cancelada`
- `atendida`
- `no_show`

Al crear o actualizar una cita activa, el sistema valida que el servicio exista, que el medico tenga disponibilidad en ese horario y que no haya otra cita agendada o confirmada que se traslape.

## Pruebas

```bash
vendor/bin/phpunit
```

Para enfocarse en permisos y agenda:

```bash
vendor/bin/phpunit --filter RoleAccessTest
```

## Roles

- `admin`: administra medicos, pacientes y citas.
- `recepcionista`: administra medicos, pacientes y citas.
- `medico`: consulta sus citas y puede marcarlas como atendidas o no presentadas.
- `paciente`: consulta sus propias citas.
