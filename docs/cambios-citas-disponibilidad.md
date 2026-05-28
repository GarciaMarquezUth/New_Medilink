# Cambios en citas, disponibilidad y base de datos

Este documento resume los problemas encontrados en el modulo de citas de MediLink, las soluciones implementadas y los archivos modificados. El proyecto usa MySQL como base de datos principal.

## 1. Las citas no validaban disponibilidad

**Problema**

El proyecto ya tenia un servicio llamado `AppointmentAvailabilityService` para validar horarios, servicios activos y traslapes entre citas. Sin embargo, `CitaController` no lo usaba al crear o actualizar citas.

Eso permitia registrar una cita en cualquier fecha y hora, aunque el medico no tuviera disponibilidad o ya tuviera otra cita en el mismo rango.

**Solucion implementada**

Ahora `CitaController` inyecta y usa `AppointmentAvailabilityService` en `store` y `update`. Cuando se agenda o confirma una cita, el sistema valida:

- que el servicio exista y este activo;
- que la cita este dentro de la disponibilidad del medico;
- que no se traslape con otra cita `agendada` o `confirmada`;
- que la validacion ocurra dentro de una transaccion.

**Archivos modificados**

- `app/Http/Controllers/CitaController.php`
- `tests/Feature/RoleAccessTest.php`

## 2. Faltaba seleccionar el servicio de la cita

**Problema**

El modelo `Cita` ya aceptaba `servicio_id`, y la migracion mas reciente tambien agregaba esa columna. Aun asi, los formularios de crear y editar cita no mostraban un campo para seleccionar servicio.

Sin servicio no era posible calcular correctamente la duracion de una cita, por lo que la validacion de traslapes quedaba incompleta.

**Solucion implementada**

Se agrego el campo `servicio_id` a los formularios de citas. El controlador carga servicios activos y valida que el valor enviado exista en la tabla `servicios`.

**Archivos modificados**

- `resources/views/Citas/create.blade.php`
- `resources/views/Citas/edit.blade.php`
- `app/Http/Controllers/CitaController.php`

## 3. Estados inconsistentes de citas

**Problema**

Habia dos grupos de estados mezclados:

- `pendiente` y `no_presentada` en controlador, vistas y pruebas;
- `agendada` y `no_show` en el servicio de disponibilidad y en la migracion que normaliza datos antiguos.

Esa inconsistencia podia causar que una cita no bloqueara disponibilidad o que una accion del medico no coincidiera con los datos ya migrados.

**Solucion implementada**

Se normalizaron los estados a:

- `agendada`
- `confirmada`
- `cancelada`
- `atendida`
- `no_show`

Las citas nuevas se crean como `agendada`. El medico puede marcar citas `agendada` o `confirmada` como `atendida` o `no_show`.

**Archivos modificados**

- `app/Http/Controllers/CitaController.php`
- `resources/views/Citas/edit.blade.php`
- `resources/views/Citas/index.blade.php`
- `resources/views/dashboard.blade.php`
- `database/migrations/2026_05_27_044054_create_citas_table.php`
- `tests/Feature/RoleAccessTest.php`

## 4. El modelo Disponibilidad usaba una tabla incorrecta

**Problema**

Laravel inferia el nombre de tabla `disponibilidads` para el modelo `Disponibilidad`, pero la tabla real se llama `disponibilidades`.

Esto rompia la creacion y consulta de disponibilidades, especialmente al ejecutar pruebas o validar horarios.

**Solucion implementada**

Se declaro explicitamente el nombre de tabla en el modelo:

```php
protected $table = 'disponibilidades';
```

**Archivo modificado**

- `app/Models/Disponibilidad.php`

## 5. Faltaban servicios base para poder agendar

**Problema**

El flujo de agenda ahora requiere un servicio activo, pero el seeder no creaba ningun servicio inicial. En una instalacion nueva, el formulario de cita quedaria sin opciones.

**Solucion implementada**

El `DatabaseSeeder` ahora crea servicios clinicos base:

- Consulta general
- Consulta especializada
- Control de seguimiento

**Archivo modificado**

- `database/seeders/DatabaseSeeder.php`

## 6. Configuracion de base de datos incorrecta

**Problema**

El README y `.env.example` indicaban SQLite, pero el proyecto trabaja con MySQL.

**Solucion implementada**

Se actualizo la configuracion de ejemplo para MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medilink
DB_USERNAME=root
DB_PASSWORD=
```

Tambien se actualizo `phpunit.xml` para usar una base MySQL de pruebas llamada `medilink_testing`.

**Archivos modificados**

- `.env.example`
- `README.md`
- `phpunit.xml`

## 7. CI no estaba alineado con main ni con MySQL

**Problema**

El workflow de pruebas no corria en pushes a `main`, aunque `main` es la rama principal. Ademas, el workflow instalaba extensiones de SQLite, no de MySQL.

**Solucion implementada**

El workflow ahora:

- corre en pushes a `main`;
- levanta un servicio MySQL 8.0 para pruebas;
- instala `pdo_mysql`;
- usa la base `medilink_testing`.

**Archivo modificado**

- `.github/workflows/tests.yml`

## 8. Las pruebas dependian del build de Vite

**Problema**

Al renderizar vistas Blade en pruebas, Laravel buscaba `public/build/manifest.json`. Ese archivo solo existe despues de compilar assets con Vite, por lo que las pruebas fallaban sin ejecutar `npm run build`.

**Solucion implementada**

El `TestCase` base desactiva Vite durante las pruebas con `withoutVite()`.

**Archivo modificado**

- `tests/TestCase.php`

## Validacion realizada

Antes de alinear PHPUnit a MySQL se ejecuto la suite completa con base de pruebas aislada y paso:

```text
33 tests, 106 assertions
```

Despues del cambio a MySQL, las pruebas requieren que exista una base local `medilink_testing` o que se ejecuten en GitHub Actions con el servicio MySQL configurado.

## Resumen de impacto

Con estos cambios el modulo de citas queda mas consistente:

- no permite doble reserva de un medico en horarios traslapados;
- respeta disponibilidad medica;
- usa la duracion real del servicio;
- mantiene estados uniformes;
- documenta MySQL como base real del proyecto;
- deja CI preparado para validar contra MySQL.
