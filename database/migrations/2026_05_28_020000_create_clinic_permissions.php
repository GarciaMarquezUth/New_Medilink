<?php

use App\Support\ClinicPermissionCatalog;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (ClinicPermissionCatalog::allPermissionNames() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (array_keys(ClinicPermissionCatalog::roles()) as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->givePermissionTo(ClinicPermissionCatalog::defaultPermissionsForRole($roleName));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
