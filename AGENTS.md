# Agent Notes

## Project Shape
- Laravel 12 / PHP ^8.2 clinic scheduling app; the stock `README.md` is not project-specific.
- Domain routes live in `routes/web.php` under authenticated `/dashboard`: `medicos`, `pacientes`, and `citas` resource controllers.
- Auth pages are Livewire Volt components from `routes/auth.php`; Volt mounts `resources/views/livewire` and `resources/views/pages` in `app/Providers/VoltServiceProvider.php`.
- Auth is hybrid/duplicated: the later Volt route owns GET `/login` and `/register`, while `routes/web.php` still keeps custom POST `/login` and `/logout` plus an overwritten GET `/login` on `Auth\LoginController`.
- Blade views use Spanish, capitalized folders such as `resources/views/Medicos`, `Pacientes`, `Citas`, and `Inicio-de-sesion`; match existing casing and labels.

## Roles And Data
- Spatie roles are active: `App\Models\User` uses `HasRoles`, and `bootstrap/app.php` aliases `role`, `permission`, and `role_or_permission` middleware.
- Role-gated code expects `admin`, `recepcionista`, `medico`, and `paciente`; appointment visibility depends on `medicos.user_id` and `pacientes.user_id` linking records to users.
- `DatabaseSeeder` only creates `test@example.com`; it does not create roles or clinic fixtures, so role-aware tests/features must create their own data.

## Commands
- Fresh setup: `composer install`, `npm install`, copy `.env.example` to `.env`, ensure `database/database.sqlite` exists for default sqlite config, then `php artisan key:generate` and `php artisan migrate`.
- Full local dev: `composer run dev` starts `php artisan serve`, `php artisan queue:listen --tries=1`, `php artisan pail --timeout=0`, and `npm run dev` concurrently.
- Frontend only: `npm run dev`; production assets: `npm run build`.
- Tests use PHPUnit, not Pest: `vendor/bin/phpunit`; focus with `vendor/bin/phpunit --filter AuthenticationTest` or a test file path.
- CI only runs `composer install`, copies `.env.example`, generates the app key, and runs `vendor/bin/phpunit` across PHP 8.2, 8.3, and 8.4.
- No Composer lint script exists; PHP formatting is via `vendor/bin/pint` or `vendor/bin/pint --test`, and `.styleci.yml` uses the Laravel preset with `no_unused_imports` disabled.

## Testing And Cache Gotchas
- `phpunit.xml` sets testing env, cache/session/mail/queue drivers, but leaves the DB override commented; tests use the active DB config unless the environment supplies sqlite.
- Ignored `bootstrap/cache/routes-v7.php` can be stale and currently makes `php artisan route:list` fail on missing `ServicioController`; run `php artisan route:clear` or point `APP_ROUTES_CACHE` at a nonexistent temp file before trusting route inspection.
- With fresh source routes, `/` redirects to `login`; `tests/Feature/ExampleTest.php` still asserts `/` returns 200, so update that test if the route cache is cleared or removed.
