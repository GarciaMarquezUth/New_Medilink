<?php

namespace App\Http\Controllers;

use App\Support\ClinicPermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermisoController extends Controller
{
    public function index(): View
    {
        $this->ensurePermissionCatalogExists();

        return view('Permisos.index', [
            'roles' => ClinicPermissionCatalog::roles(),
            'modules' => ClinicPermissionCatalog::modules(),
            'actions' => ClinicPermissionCatalog::actions(),
            'matrix' => $this->permissionMatrix(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->ensurePermissionCatalogExists();

        $catalogPermissions = ClinicPermissionCatalog::allPermissionNames();

        foreach (array_keys(ClinicPermissionCatalog::roles()) as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');
            $selectedPermissions = [];

            foreach (array_keys(ClinicPermissionCatalog::modules()) as $module) {
                foreach (array_keys(ClinicPermissionCatalog::actions()) as $action) {
                    if ($request->boolean("permissions.{$roleName}.{$module}.{$action}")) {
                        $selectedPermissions[] = ClinicPermissionCatalog::permissionName($module, $action);
                    }
                }
            }

            $otherPermissions = $role->permissions
                ->pluck('name')
                ->reject(fn (string $permission) => in_array($permission, $catalogPermissions, true))
                ->all();

            $role->syncPermissions(array_merge($otherPermissions, $selectedPermissions));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('permisos.index')->with('success', 'Permisos actualizados correctamente.');
    }

    private function permissionMatrix(): array
    {
        $matrix = [];

        $roles = Role::whereIn('name', array_keys(ClinicPermissionCatalog::roles()))
            ->with('permissions')
            ->get()
            ->keyBy('name');

        foreach (array_keys(ClinicPermissionCatalog::roles()) as $roleName) {
            $rolePermissions = $roles[$roleName]?->permissions->pluck('name')->all() ?? [];

            foreach (array_keys(ClinicPermissionCatalog::modules()) as $module) {
                foreach (array_keys(ClinicPermissionCatalog::actions()) as $action) {
                    $permission = ClinicPermissionCatalog::permissionName($module, $action);
                    $matrix[$roleName][$module][$action] = in_array($permission, $rolePermissions, true);
                }
            }
        }

        return $matrix;
    }

    private function ensurePermissionCatalogExists(): void
    {
        foreach (ClinicPermissionCatalog::allPermissionNames() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (array_keys(ClinicPermissionCatalog::roles()) as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }
    }
}
