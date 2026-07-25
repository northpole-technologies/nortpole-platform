<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleApiTest extends TestCase
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

    public function test_roles_can_be_listed_for_the_active_tenant(): void
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

        $this->tenantContext->runFor(
            $firstOrganisation,
            fn (): Role => Role::create([
                'key' => 'manager',
                'name' => 'Manager',
                'description' => 'Manages the organisation.',
                'is_system' => false,
                'is_active' => true,
            ])
        );

        $this->tenantContext->runFor(
            $secondOrganisation,
            fn (): Role => Role::create([
                'key' => 'administrator',
                'name' => 'Administrator',
                'description' => 'Administers another organisation.',
                'is_system' => false,
                'is_active' => true,
            ])
        );

        $response = $this
            ->withTenant($firstOrganisation)
            ->getJson('/api/v1/roles');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'key' => 'owner',
                'name' => 'Owner',
            ])
            ->assertJsonFragment([
                'key' => 'manager',
                'name' => 'Manager',
            ])
            ->assertJsonMissing([
                'key' => 'administrator',
            ]);
    }

    public function test_a_role_can_be_created_for_the_active_tenant(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.create',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $response = $this
            ->withTenant($organisation)
            ->postJson('/api/v1/roles', [
                'key' => 'project_manager',
                'name' => 'Project Manager',
                'description' => 'Manages projects and project teams.',
                'is_active' => true,
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                'Role created successfully.'
            )
            ->assertJsonPath(
                'data.organisation_id',
                $organisation->id
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
                'Manages projects and project teams.'
            )
            ->assertJsonPath(
                'data.is_system',
                false
            )
            ->assertJsonPath(
                'data.is_active',
                true
            );

        $this->assertDatabaseHas('roles', [
            'organisation_id' => $organisation->id,
            'key' => 'project_manager',
            'name' => 'Project Manager',
            'description' => 'Manages projects and project teams.',
            'is_system' => false,
            'is_active' => true,
        ]);
    }

    public function test_role_key_and_name_are_required(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.create',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $response = $this
            ->withTenant($organisation)
            ->postJson('/api/v1/roles', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'key',
                'name',
            ]);

        $this->assertDatabaseCount('roles', 1);

        $this->assertDatabaseMissing('roles', [
            'organisation_id' => $organisation->id,
            'key' => '',
        ]);
    }

    public function test_role_key_must_be_unique_within_an_organisation(): void
    {
        $organisation = $this->createTenantOrganisation();

        $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: [
                'roles.create',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $this->tenantContext->runFor(
            $organisation,
            fn (): Role => Role::create([
                'key' => 'manager',
                'name' => 'Manager',
                'description' => null,
                'is_system' => false,
                'is_active' => true,
            ])
        );

        $response = $this
            ->withTenant($organisation)
            ->postJson('/api/v1/roles', [
                'key' => 'manager',
                'name' => 'Second Manager',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'key',
            ]);

        $this->assertDatabaseCount('roles', 2);

        $this->assertDatabaseMissing('roles', [
            'organisation_id' => $organisation->id,
            'key' => 'manager',
            'name' => 'Second Manager',
        ]);
    }

    public function test_the_same_role_key_can_exist_in_different_organisations(): void
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
            organisation: $secondOrganisation,
            permissions: [
                'roles.create',
            ],
            roleAttributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $this->tenantContext->runFor(
            $firstOrganisation,
            fn (): Role => Role::create([
                'key' => 'manager',
                'name' => 'First Manager',
                'description' => null,
                'is_system' => false,
                'is_active' => true,
            ])
        );

        $response = $this
            ->withTenant($secondOrganisation)
            ->postJson('/api/v1/roles', [
                'key' => 'manager',
                'name' => 'Second Manager',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.key', 'manager')
            ->assertJsonPath(
                'data.organisation_id',
                $secondOrganisation->id
            );

        $this->assertDatabaseHas('roles', [
            'organisation_id' => $firstOrganisation->id,
            'key' => 'manager',
            'name' => 'First Manager',
        ]);

        $this->assertDatabaseHas('roles', [
            'organisation_id' => $secondOrganisation->id,
            'key' => 'manager',
            'name' => 'Second Manager',
        ]);
    }

    public function test_role_creation_requires_authentication(): void
    {
        $organisation = $this->createTenantOrganisation();

        $response = $this
            ->withTenant($organisation)
            ->postJson('/api/v1/roles', [
                'key' => 'manager',
                'name' => 'Manager',
            ]);

        $response->assertUnauthorized();

        $this->assertDatabaseCount('roles', 0);
    }

    public function test_role_creation_requires_a_tenant_header(): void
    {
        $organisation = $this->createTenantOrganisation();

        $user = User::factory()->create();

        $role = $this->createTenantRole(
            organisation: $organisation,
            permissions: [
                'roles.create',
            ],
            attributes: [
                'key' => 'owner',
                'name' => 'Owner',
            ]
        );

        $organisation->users()->attach(
            $user->id,
            [
                'role' => $role->key,
                'role_id' => $role->id,
                'is_active' => true,
                'joined_at' => now(),
            ]
        );

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/roles', [
            'key' => 'manager',
            'name' => 'Manager',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'The X-Organisation-Id header is required.'
            );

        $this->assertDatabaseCount('roles', 1);

        $this->assertDatabaseMissing('roles', [
            'organisation_id' => $organisation->id,
            'key' => 'manager',
        ]);
    }
}
