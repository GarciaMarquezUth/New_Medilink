<?php

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

        $role = Role::findOrCreate('medico', 'web');
        $permissions = collect([
            'disponibilidades.ver',
            'disponibilidades.crear',
            'disponibilidades.editar',
        ])->map(fn (string $permission) => Permission::findOrCreate($permission, 'web'));

        $role->givePermissionTo($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::where('name', 'medico')->where('guard_name', 'web')->first();

        if ($role) {
            $role->revokePermissionTo([
                'disponibilidades.ver',
                'disponibilidades.crear',
                'disponibilidades.editar',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
