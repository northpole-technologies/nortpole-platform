<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\Permission;
use App\Models\Role;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionAssignmentApiTest extends TestCase
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

    public function test_permissions_can_be_listed(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'permissions.view',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        Permission::create([
            'key' => 'users.view',
            'name' => 'View users',
            'description' => 'Allows users to be viewed.',
            'module_key' => 'core',
            'is_active' => true,
        ]);

        Permission::create([
            'key' => 'jobs.create',
            'name' => 'Create jobs',
            'description' => 'Allows jobs to be created.',
            'module_key' => 'jobs',
            'is_active' => true,
        ]);

        $response = $this
            ->withTenant($organisation)
            ->getJson('/api/v1/permissions');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data')
            ->assertJsonFragment([
                'key' => 'permissions.view',
            ])
            ->assertJsonFragment([
                'key' => 'users.view',
            ])
            ->assertJsonFragment([
                'key' => 'jobs.create',
            ]);
    }

    public function test_permissions_can_be_assigned_to_a_role(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.permissions.sync',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $role = $this->createRole($organisation);

        $viewPermission = $this->createPermission(
            'users.view',
            'View users'
        );

        $createPermission = $this->createPermission(
            'users.create',
            'Create users'
        );

        $response = $this
            ->withTenant($organisation)
            ->postJson(
                "/api/v1/roles/{$role->id}/permissions",
                [
                    'permissions' => [
                        $viewPermission->id,
                        $createPermission->id,
                    ],
                ]
            );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                'Permissions updated successfully.'
            )
            ->assertJsonPath('data.id', $role->id)
            ->assertJsonCount(2, 'data.permissions');

        $this->assertDatabaseHas('permission_role', [
            'role_id' => $role->id,
            'permission_id' => $viewPermission->id,
        ]);

        $this->assertDatabaseHas('permission_role', [
            'role_id' => $role->id,
            'permission_id' => $createPermission->id,
        ]);
    }

    public function test_permission_assignment_replaces_existing_permissions(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.permissions.sync',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $role = $this->createRole($organisation);

        $oldPermission = $this->createPermission(
            'users.view',
            'View users'
        );

        $newPermission = $this->createPermission(
            'users.create',
            'Create users'
        );

        $role->permissions()->attach($oldPermission->id);

        $response = $this
            ->withTenant($organisation)
            ->postJson(
                "/api/v1/roles/{$role->id}/permissions",
                [
                    'permissions' => [
                        $newPermission->id,
                    ],
                ]
            );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data.permissions')
            ->assertJsonPath(
                'data.permissions.0.id',
                $newPermission->id
            );

        $this->assertDatabaseMissing('permission_role', [
            'role_id' => $role->id,
            'permission_id' => $oldPermission->id,
        ]);

        $this->assertDatabaseHas('permission_role', [
            'role_id' => $role->id,
            'permission_id' => $newPermission->id,
        ]);
    }

    public function test_all_permissions_can_be_removed_from_a_role(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.permissions.sync',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $role = $this->createRole($organisation);

        $permission = $this->createPermission(
            'users.view',
            'View users'
        );

        $role->permissions()->attach($permission->id);

        $response = $this
            ->withTenant($organisation)
            ->postJson(
                "/api/v1/roles/{$role->id}/permissions",
                [
                    'permissions' => [],
                ]
            );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(0, 'data.permissions');

        $this->assertDatabaseMissing('permission_role', [
            'role_id' => $role->id,
            'permission_id' => $permission->id,
        ]);
    }

    public function test_inactive_permissions_cannot_be_assigned(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.permissions.sync',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $role = $this->createRole($organisation);

        $permission = Permission::create([
            'key' => 'users.delete',
            'name' => 'Delete users',
            'description' => 'Allows users to be deleted.',
            'module_key' => 'core',
            'is_active' => false,
        ]);

        $response = $this
            ->withTenant($organisation)
            ->postJson(
                "/api/v1/roles/{$role->id}/permissions",
                [
                    'permissions' => [
                        $permission->id,
                    ],
                ]
            );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'permissions.0',
            ]);

        $this->assertDatabaseMissing('permission_role', [
            'role_id' => $role->id,
            'permission_id' => $permission->id,
        ]);
    }

    public function test_unknown_permissions_cannot_be_assigned(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.permissions.sync',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $role = $this->createRole($organisation);

        $response = $this
            ->withTenant($organisation)
            ->postJson(
                "/api/v1/roles/{$role->id}/permissions",
                [
                    'permissions' => [
                        999999,
                    ],
                ]
            );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'permissions.0',
            ]);

        $this->assertDatabaseCount(
            'permission_role',
            1
        );
    }

    public function test_duplicate_permissions_are_rejected(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.permissions.sync',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $role = $this->createRole($organisation);

        $permission = $this->createPermission(
            'users.view',
            'View users'
        );

        $response = $this
            ->withTenant($organisation)
            ->postJson(
                "/api/v1/roles/{$role->id}/permissions",
                [
                    'permissions' => [
                        $permission->id,
                        $permission->id,
                    ],
                ]
            );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'permissions.0',
                'permissions.1',
            ]);

        $this->assertDatabaseMissing('permission_role', [
            'role_id' => $role->id,
            'permission_id' => $permission->id,
        ]);
    }

    public function test_permissions_field_is_required(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.permissions.sync',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $role = $this->createRole($organisation);

        $response = $this
            ->withTenant($organisation)
            ->postJson(
                "/api/v1/roles/{$role->id}/permissions",
                []
            );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'permissions',
            ]);
    }

    public function test_permissions_cannot_be_assigned_to_another_tenants_role(): void
    {
        $firstOrganisation = $this->createTenantOrganisation([
            'name' => 'First Organisation',
            'slug' => 'first-organisation',
            'email' => 'first-organisation@example.com',
        ]);

        $secondOrganisation = $this->createTenantOrganisation([
            'name' => 'Second Organisation',
            'slug' => 'second-organisation',
            'email' => 'second-organisation@example.com',
        ]);

        $this->actingAsTenantUser(
            organisation: $firstOrganisation,
            permissions: [
                'roles.permissions.sync',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $secondRole = $this->createRole($secondOrganisation);

        $permission = $this->createPermission(
            'users.view',
            'View users'
        );

        $response = $this
            ->withTenant($firstOrganisation)
            ->postJson(
                "/api/v1/roles/{$secondRole->id}/permissions",
                [
                    'permissions' => [
                        $permission->id,
                    ],
                ]
            );

        $response->assertNotFound();

        $this->assertDatabaseMissing('permission_role', [
            'role_id' => $secondRole->id,
            'permission_id' => $permission->id,
        ]);
    }

    public function test_permission_assignment_requires_authentication(): void
    {
        $organisation = $this->createTenantOrganisation();

        $role = $this->createRole($organisation);

        $permission = $this->createPermission(
            'users.view',
            'View users'
        );

        $response = $this
            ->withTenant($organisation)
            ->postJson(
                "/api/v1/roles/{$role->id}/permissions",
                [
                    'permissions' => [
                        $permission->id,
                    ],
                ]
            );

        $response->assertUnauthorized();

        $this->assertDatabaseMissing('permission_role', [
            'role_id' => $role->id,
            'permission_id' => $permission->id,
        ]);
    }

    private function createRole(
        Organisation $organisation
    ): Role {
        return $this->tenantContext->runFor(
            $organisation,
            fn (): Role => Role::create([
                'key' => 'manager',
                'name' => 'Manager',
                'description' => 'Manages users and operations.',
                'is_system' => false,
                'is_active' => true,
            ])
        );
    }

    private function createPermission(
        string $key,
        string $name
    ): Permission {
        return Permission::create([
            'key' => $key,
            'name' => $name,
            'description' => "{$name} permission.",
            'module_key' => 'core',
            'is_active' => true,
        ]);
    }
}
