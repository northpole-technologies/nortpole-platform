<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\Role;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleCrudApiTest extends TestCase
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

    public function test_a_role_can_be_viewed(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.view',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $role = $this->createRole(
            $organisation,
            [
                'key' => 'manager',
                'name' => 'Manager',
                'description' => 'Manages staff and projects.',
            ]
        );

        $response = $this
            ->withTenant($organisation)
            ->getJson("/api/v1/roles/{$role->id}");

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $role->id)
            ->assertJsonPath('data.key', 'manager')
            ->assertJsonPath('data.name', 'Manager')
            ->assertJsonPath(
                'data.description',
                'Manages staff and projects.'
            )
            ->assertJsonPath('data.is_system', false)
            ->assertJsonPath('data.is_active', true);
    }

    public function test_a_role_from_another_tenant_cannot_be_viewed(): void
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
                'roles.view',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $secondRole = $this->createRole(
            $secondOrganisation,
            [
                'key' => 'manager',
                'name' => 'Second Manager',
            ]
        );

        $response = $this
            ->withTenant($firstOrganisation)
            ->getJson("/api/v1/roles/{$secondRole->id}");

        $response->assertNotFound();
    }

    public function test_a_role_can_be_updated(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.update',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $role = $this->createRole(
            $organisation,
            [
                'key' => 'manager',
                'name' => 'Manager',
                'description' => 'Original description.',
            ]
        );

        $response = $this
            ->withTenant($organisation)
            ->patchJson(
                "/api/v1/roles/{$role->id}",
                [
                    'key' => 'project_manager',
                    'name' => 'Project Manager',
                    'description' => 'Updated description.',
                    'is_active' => false,
                ]
            );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                'Role updated successfully.'
            )
            ->assertJsonPath(
                'data.key',
                'project_manager'
            )
            ->assertJsonPath(
                'data.name',
                'Project Manager'
            )
            ->assertJsonPath(
                'data.description',
                'Updated description.'
            )
            ->assertJsonPath(
                'data.is_active',
                false
            );

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'organisation_id' => $organisation->id,
            'key' => 'project_manager',
            'name' => 'Project Manager',
            'description' => 'Updated description.',
            'is_active' => false,
        ]);
    }

    public function test_a_role_can_be_partially_updated(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.update',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $role = $this->createRole(
            $organisation,
            [
                'key' => 'manager',
                'name' => 'Manager',
                'description' => 'Original description.',
            ]
        );

        $response = $this
            ->withTenant($organisation)
            ->patchJson(
                "/api/v1/roles/{$role->id}",
                [
                    'name' => 'Senior Manager',
                ]
            );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.key',
                'manager'
            )
            ->assertJsonPath(
                'data.name',
                'Senior Manager'
            )
            ->assertJsonPath(
                'data.description',
                'Original description.'
            );

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'key' => 'manager',
            'name' => 'Senior Manager',
            'description' => 'Original description.',
        ]);
    }

    public function test_role_key_must_remain_unique_when_updating(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.update',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $managerRole = $this->createRole(
            $organisation,
            [
                'key' => 'manager',
                'name' => 'Manager',
            ]
        );

        $staffRole = $this->createRole(
            $organisation,
            [
                'key' => 'staff',
                'name' => 'Staff',
            ]
        );

        $response = $this
            ->withTenant($organisation)
            ->patchJson(
                "/api/v1/roles/{$staffRole->id}",
                [
                    'key' => $managerRole->key,
                ]
            );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'key',
            ]);

        $this->assertDatabaseHas('roles', [
            'id' => $staffRole->id,
            'key' => 'staff',
        ]);
    }

    public function test_a_role_from_another_tenant_cannot_be_updated(): void
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
                'roles.update',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $secondRole = $this->createRole(
            $secondOrganisation,
            [
                'key' => 'manager',
                'name' => 'Second Manager',
            ]
        );

        $response = $this
            ->withTenant($firstOrganisation)
            ->patchJson(
                "/api/v1/roles/{$secondRole->id}",
                [
                    'name' => 'Compromised Manager',
                ]
            );

        $response->assertNotFound();

        $this->assertDatabaseHas('roles', [
            'id' => $secondRole->id,
            'name' => 'Second Manager',
        ]);
    }

    public function test_a_system_role_key_cannot_be_changed(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.update',
            ],
            roleAttributes: [
                'key' => 'administrator',
                'name' => 'Administrator',
            ]
        );

        $role = $this->createRole(
            $organisation,
            [
                'key' => 'owner',
                'name' => 'Owner',
                'is_system' => true,
            ]
        );

        $response = $this
            ->withTenant($organisation)
            ->patchJson(
                "/api/v1/roles/{$role->id}",
                [
                    'key' => 'platform_owner',
                ]
            );

        $response
            ->assertConflict()
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'message',
                'System role keys cannot be changed.'
            );

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'key' => 'owner',
        ]);
    }

    public function test_a_custom_role_can_be_deleted(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.delete',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $role = $this->createRole(
            $organisation,
            [
                'key' => 'temporary',
                'name' => 'Temporary',
            ]
        );

        $response = $this
            ->withTenant($organisation)
            ->deleteJson("/api/v1/roles/{$role->id}");

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                'Role deleted successfully.'
            );

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_a_system_role_cannot_be_deleted(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.delete',
            ],
            roleAttributes: [
                'key' => 'administrator',
                'name' => 'Administrator',
            ]
        );

        $role = $this->createRole(
            $organisation,
            [
                'key' => 'owner',
                'name' => 'Owner',
                'is_system' => true,
            ]
        );

        $response = $this
            ->withTenant($organisation)
            ->deleteJson("/api/v1/roles/{$role->id}");

        $response
            ->assertConflict()
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'message',
                'System roles cannot be deleted.'
            );

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_a_role_assigned_to_a_user_cannot_be_deleted(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.delete',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $role = $this->createRole(
            $organisation,
            [
                'key' => 'manager',
                'name' => 'Manager',
            ]
        );

        $assignedUser = User::factory()->create();

        $organisation->users()->attach(
            $assignedUser->id,
            [
                'role' => $role->key,
                'role_id' => $role->id,
                'is_active' => true,
                'joined_at' => now(),
            ]
        );

        $response = $this
            ->withTenant($organisation)
            ->deleteJson("/api/v1/roles/{$role->id}");

        $response
            ->assertConflict()
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'message',
                'Roles assigned to users cannot be deleted.'
            );

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_a_role_from_another_tenant_cannot_be_deleted(): void
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
                'roles.delete',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $secondRole = $this->createRole(
            $secondOrganisation,
            [
                'key' => 'manager',
                'name' => 'Second Manager',
            ]
        );

        $response = $this
            ->withTenant($firstOrganisation)
            ->deleteJson("/api/v1/roles/{$secondRole->id}");

        $response->assertNotFound();

        $this->assertDatabaseHas('roles', [
            'id' => $secondRole->id,
        ]);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function createRole(
        Organisation $organisation,
        array $attributes = []
    ): Role {
        return $this->tenantContext->runFor(
            $organisation,
            fn (): Role => Role::create(array_merge([
                'key' => 'manager',
                'name' => 'Manager',
                'description' => null,
                'is_system' => false,
                'is_active' => true,
            ], $attributes))
        );
    }
}