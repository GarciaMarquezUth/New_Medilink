<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_access_permissions_module(): void
    {
        $admin = $this->userWithRole('admin');
        $recepcionista = $this->userWithRole('recepcionista');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Permisos')
            ->assertSee(route('permisos.index'), false);

        $this->actingAs($recepcionista)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('permisos.index'), false);

        $this->actingAs($admin)
            ->get(route('permisos.index'))
            ->assertOk()
            ->assertSee('Permisos por rol');

        $this->actingAs($recepcionista)
            ->get(route('permisos.index'))
            ->assertForbidden();
    }

    public function test_admin_can_update_role_permissions(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->put(route('permisos.update'), [
                'permissions' => [
                    'recepcionista' => [
                        'pacientes' => [
                            'ver' => '1',
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('permisos.index'));

        $role = Role::findByName('recepcionista', 'web');

        $this->assertTrue($role->hasPermissionTo('pacientes.ver'));
        $this->assertFalse($role->hasPermissionTo('pacientes.crear'));
    }

    public function test_revoked_permission_hides_button_and_blocks_route(): void
    {
        $recepcionista = $this->userWithRole('recepcionista');
        $role = Role::findByName('recepcionista', 'web');
        $role->revokePermissionTo('pacientes.crear');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($recepcionista)
            ->get(route('pacientes.index'))
            ->assertOk()
            ->assertDontSee('Nuevo paciente');

        $this->actingAs($recepcionista)
            ->get(route('pacientes.create'))
            ->assertForbidden();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
