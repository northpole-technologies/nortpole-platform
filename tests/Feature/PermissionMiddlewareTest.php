<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserHasPermission;
use App\Models\Organisation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private TenantContext $tenantContext;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantContext = app(TenantContext::class);

        Route::middleware([
            'auth:sanctum',
            'tenant',
            EnsureUserHasPermission::class . ':roles.view',
        ])->get(
            '/testing/permission-protected-route',
            fn () => response()->json([
                'success' => true,
                'message' => 'Permission granted.',
            ])
        );
    }

    protected function tearDown(): void
    {
        $this->tenantContext->clear();

        parent::tearDown();
    }

    public function test_a_user_with_the_required_permission_can_access_the_route(): void
    {
        $organisation = $this->createOrganisation();

        $permission = $this->createPermission(
            'roles.view',
            'View roles'
        );

        $role = $this->createRole(
            $organisation
        );

        $role->permissions()->attach(
            $permission->id
        );

        $user = User::factory()->create();

        $this->attachUserToOrganisation(
            $user,
            $organisation,
            $role
        );

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $organisation->id
            )
            ->getJson(
                '/testing/permission-protected-route'
            );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                'Permission granted.'
            );
    }

    public function test_a_user_without_the_required_permission_is_forbidden(): void
    {
        $organisation = $this->createOrganisation();

        $role = $this->createRole(
            $organisation
        );

        $user = User::factory()->create();

        $this->attachUserToOrganisation(
            $user,
            $organisation,
            $role
        );

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $organisation->id
            )
            ->getJson(
                '/testing/permission-protected-route'
            );

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'message',
                'You do not have permission to perform this action.'
            )
            ->assertJsonPath(
                'required_permission',
                'roles.view'
            );
    }

    public function test_an_inactive_permission_does_not_grant_access(): void
    {
        $organisation = $this->createOrganisation();

        $permission = Permission::create([
            'key' => 'roles.view',
            'name' => 'View roles',
            'description' => 'Allows roles to be viewed.',
            'module_key' => 'core',
            'is_active' => false,
        ]);

        $role = $this->createRole(
            $organisation
        );

        $role->permissions()->attach(
            $permission->id
        );

        $user = User::factory()->create();

        $this->attachUserToOrganisation(
            $user,
            $organisation,
            $role
        );

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $organisation->id
            )
            ->getJson(
                '/testing/permission-protected-route'
            );

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'required_permission',
                'roles.view'
            );
    }

    public function test_an_inactive_role_does_not_grant_access(): void
    {
        $organisation = $this->createOrganisation();

        $permission = $this->createPermission(
            'roles.view',
            'View roles'
        );

        $role = $this->createRole(
            $organisation,
            [
                'is_active' => false,
            ]
        );

        $role->permissions()->attach(
            $permission->id
        );

        $user = User::factory()->create();

        $this->attachUserToOrganisation(
            $user,
            $organisation,
            $role
        );

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $organisation->id
            )
            ->getJson(
                '/testing/permission-protected-route'
            );

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'required_permission',
                'roles.view'
            );
    }

    public function test_an_inactive_membership_does_not_grant_access(): void
    {
        $organisation = $this->createOrganisation();

        $permission = $this->createPermission(
            'roles.view',
            'View roles'
        );

        $role = $this->createRole(
            $organisation
        );

        $role->permissions()->attach(
            $permission->id
        );

        $user = User::factory()->create();

        $organisation->users()->attach(
            $user->id,
            [
                'role' => $role->key,
                'role_id' => $role->id,
                'is_active' => false,
                'joined_at' => now(),
            ]
        );

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $organisation->id
            )
            ->getJson(
                '/testing/permission-protected-route'
            );

        $response->assertNotFound();
    }

    public function test_a_permission_from_another_tenant_role_does_not_grant_access(): void
    {
        $firstOrganisation = $this->createOrganisation(
            'First Organisation',
            'first-organisation'
        );

        $secondOrganisation = $this->createOrganisation(
            'Second Organisation',
            'second-organisation'
        );

        $permission = $this->createPermission(
            'roles.view',
            'View roles'
        );

        $firstRole = $this->createRole(
            $firstOrganisation,
            [
                'key' => 'member',
                'name' => 'Member',
            ]
        );

        $secondRole = $this->createRole(
            $secondOrganisation,
            [
                'key' => 'manager',
                'name' => 'Manager',
            ]
        );

        $secondRole->permissions()->attach(
            $permission->id
        );

        $user = User::factory()->create();

        $this->attachUserToOrganisation(
            $user,
            $firstOrganisation,
            $firstRole
        );

        $this->attachUserToOrganisation(
            $user,
            $secondOrganisation,
            $secondRole
        );

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $firstOrganisation->id
            )
            ->getJson(
                '/testing/permission-protected-route'
            );

        $response
            ->assertForbidden()
            ->assertJsonPath(
                'required_permission',
                'roles.view'
            );
    }

    public function test_the_route_requires_authentication(): void
    {
        $organisation = $this->createOrganisation();

        $response = $this
            ->withHeader(
                'X-Organisation-Id',
                $organisation->id
            )
            ->getJson(
                '/testing/permission-protected-route'
            );

        $response
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'message',
                'Unauthenticated.'
            );
    }

    public function test_the_route_requires_a_tenant_header(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson(
            '/testing/permission-protected-route'
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'The X-Organisation-Id header is required.'
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
                'description' => 'Manages organisation resources.',
                'is_system' => false,
                'is_active' => true,
            ], $attributes))
        );
    }

    private function attachUserToOrganisation(
        User $user,
        Organisation $organisation,
        Role $role
    ): void {
        $organisation->users()->attach(
            $user->id,
            [
                'role' => $role->key,
                'role_id' => $role->id,
                'is_active' => true,
                'joined_at' => now(),
            ]
        );
    }
}