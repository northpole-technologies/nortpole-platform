<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    private TenantContext $tenantContext;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantContext = app(TenantContext::class);
    }

    protected function tearDown(): void
    {
        $this->tenantContext->clear();

        parent::tearDown();
    }

    public function test_a_role_belongs_to_an_organisation(): void
    {
        $organisation = $this->createOrganisation();

        $role = $this->tenantContext->runFor(
            $organisation,
            fn (): Role => Role::create([
                'key' => 'owner',
                'name' => 'Owner',
                'is_system' => true,
                'is_active' => true,
            ])
        );

        $this->assertSame(
            $organisation->id,
            $role->organisation_id
        );

        $this->assertTrue(
            $role->organisation->is($organisation)
        );
    }

    public function test_a_role_can_have_permissions(): void
    {
        $organisation = $this->createOrganisation();

        $permission = Permission::create([
            'key' => 'modules.install',
            'name' => 'Install modules',
            'description' => 'Allows modules to be installed.',
            'module_key' => 'core',
            'is_active' => true,
        ]);

        $role = $this->tenantContext->runFor(
            $organisation,
            fn (): Role => Role::create([
                'key' => 'owner',
                'name' => 'Owner',
                'is_system' => true,
                'is_active' => true,
            ])
        );

        $role->permissions()->attach($permission->id);

        $this->assertTrue(
            $role->hasPermission('modules.install')
        );

        $this->assertFalse(
            $role->hasPermission('modules.uninstall')
        );
    }

    public function test_a_user_can_have_a_role_for_an_organisation(): void
    {
        $organisation = $this->createOrganisation();

        $user = User::factory()->create();

        $role = $this->tenantContext->runFor(
            $organisation,
            fn (): Role => Role::create([
                'key' => 'owner',
                'name' => 'Owner',
                'is_system' => true,
                'is_active' => true,
            ])
        );

        $organisation->users()->attach(
            $user->id,
            [
                'role' => 'owner',
                'role_id' => $role->id,
                'is_active' => true,
                'joined_at' => now(),
            ]
        );

        $resolvedRole = $user->roleForOrganisation(
            $organisation
        );

        $this->assertNotNull($resolvedRole);

        $this->assertSame(
            $role->id,
            $resolvedRole->id
        );
    }

    public function test_a_user_inherits_permissions_from_their_role(): void
    {
        $organisation = $this->createOrganisation();

        $user = User::factory()->create();

        $permission = Permission::create([
            'key' => 'modules.install',
            'name' => 'Install modules',
            'description' => 'Allows modules to be installed.',
            'module_key' => 'core',
            'is_active' => true,
        ]);

        $role = $this->tenantContext->runFor(
            $organisation,
            fn (): Role => Role::create([
                'key' => 'owner',
                'name' => 'Owner',
                'is_system' => true,
                'is_active' => true,
            ])
        );

        $role->permissions()->attach($permission->id);

        $organisation->users()->attach(
            $user->id,
            [
                'role' => 'owner',
                'role_id' => $role->id,
                'is_active' => true,
                'joined_at' => now(),
            ]
        );

        $this->tenantContext->set($organisation);

        $this->assertTrue(
            $user->hasPermission('modules.install')
        );

        $this->assertFalse(
            $user->hasPermission('modules.uninstall')
        );
    }

    public function test_roles_are_scoped_to_the_active_tenant(): void
    {
        $firstOrganisation = $this->createOrganisation(
            'First Organisation',
            'first-organisation'
        );

        $secondOrganisation = $this->createOrganisation(
            'Second Organisation',
            'second-organisation'
        );

        $this->tenantContext->runFor(
            $firstOrganisation,
            fn (): Role => Role::create([
                'key' => 'owner',
                'name' => 'First Owner',
                'is_system' => true,
                'is_active' => true,
            ])
        );

        $this->tenantContext->runFor(
            $secondOrganisation,
            fn (): Role => Role::create([
                'key' => 'owner',
                'name' => 'Second Owner',
                'is_system' => true,
                'is_active' => true,
            ])
        );

        $roles = $this->tenantContext->runFor(
            $firstOrganisation,
            fn () => Role::query()->get()
        );

        $this->assertCount(1, $roles);

        $this->assertSame(
            'First Owner',
            $roles->first()->name
        );
    }

    public function test_inactive_permissions_are_not_granted(): void
    {
        $organisation = $this->createOrganisation();

        $permission = Permission::create([
            'key' => 'modules.install',
            'name' => 'Install modules',
            'description' => 'Allows modules to be installed.',
            'module_key' => 'core',
            'is_active' => false,
        ]);

        $role = $this->tenantContext->runFor(
            $organisation,
            fn (): Role => Role::create([
                'key' => 'owner',
                'name' => 'Owner',
                'is_system' => true,
                'is_active' => true,
            ])
        );

        $role->permissions()->attach($permission->id);

        $this->assertFalse(
            $role->hasPermission('modules.install')
        );
    }

    public function test_inactive_roles_do_not_grant_permissions(): void
    {
        $organisation = $this->createOrganisation();

        $user = User::factory()->create();

        $permission = Permission::create([
            'key' => 'modules.install',
            'name' => 'Install modules',
            'description' => 'Allows modules to be installed.',
            'module_key' => 'core',
            'is_active' => true,
        ]);

        $role = $this->tenantContext->runFor(
            $organisation,
            fn (): Role => Role::create([
                'key' => 'owner',
                'name' => 'Owner',
                'is_system' => true,
                'is_active' => false,
            ])
        );

        $role->permissions()->attach($permission->id);

        $organisation->users()->attach(
            $user->id,
            [
                'role' => 'owner',
                'role_id' => $role->id,
                'is_active' => true,
                'joined_at' => now(),
            ]
        );

        $this->tenantContext->set($organisation);

        $this->assertFalse(
            $user->hasPermission('modules.install')
        );
    }

    public function test_inactive_memberships_do_not_grant_permissions(): void
    {
        $organisation = $this->createOrganisation();

        $user = User::factory()->create();

        $permission = Permission::create([
            'key' => 'modules.install',
            'name' => 'Install modules',
            'description' => 'Allows modules to be installed.',
            'module_key' => 'core',
            'is_active' => true,
        ]);

        $role = $this->tenantContext->runFor(
            $organisation,
            fn (): Role => Role::create([
                'key' => 'owner',
                'name' => 'Owner',
                'is_system' => true,
                'is_active' => true,
            ])
        );

        $role->permissions()->attach($permission->id);

        $organisation->users()->attach(
            $user->id,
            [
                'role' => 'owner',
                'role_id' => $role->id,
                'is_active' => false,
                'joined_at' => now(),
            ]
        );

        $this->tenantContext->set($organisation);

        $this->assertFalse(
            $user->hasPermission('modules.install')
        );
    }

    public function test_a_user_does_not_receive_permissions_from_another_tenant(): void
    {
        $firstOrganisation = $this->createOrganisation(
            'First Organisation',
            'first-organisation'
        );

        $secondOrganisation = $this->createOrganisation(
            'Second Organisation',
            'second-organisation'
        );

        $user = User::factory()->create();

        $permission = Permission::create([
            'key' => 'modules.install',
            'name' => 'Install modules',
            'description' => 'Allows modules to be installed.',
            'module_key' => 'core',
            'is_active' => true,
        ]);

        $firstRole = $this->tenantContext->runFor(
            $firstOrganisation,
            fn (): Role => Role::create([
                'key' => 'owner',
                'name' => 'First Owner',
                'is_system' => true,
                'is_active' => true,
            ])
        );

        $secondRole = $this->tenantContext->runFor(
            $secondOrganisation,
            fn (): Role => Role::create([
                'key' => 'member',
                'name' => 'Second Member',
                'is_system' => true,
                'is_active' => true,
            ])
        );

        $firstRole->permissions()->attach($permission->id);

        $firstOrganisation->users()->attach(
            $user->id,
            [
                'role' => 'owner',
                'role_id' => $firstRole->id,
                'is_active' => true,
                'joined_at' => now(),
            ]
        );

        $secondOrganisation->users()->attach(
            $user->id,
            [
                'role' => 'member',
                'role_id' => $secondRole->id,
                'is_active' => true,
                'joined_at' => now(),
            ]
        );

        $this->tenantContext->set($secondOrganisation);

        $this->assertFalse(
            $user->hasPermission('modules.install')
        );
    }

    private function createOrganisation(
        string $name = 'NorthPole Technologies',
        string $slug = 'northpole-technologies'
    ): Organisation {
        return Organisation::create([
            'name' => $name,
            'slug' => $slug,
            'email' => "{$slug}@example.com",
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
            'active' => true,
        ]);
    }
}
