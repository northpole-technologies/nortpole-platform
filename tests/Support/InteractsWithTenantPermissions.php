<?php

namespace Tests\Support;

use App\Models\Organisation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

trait InteractsWithTenantPermissions
{
    protected function createTenantOrganisation(
        array $attributes = []
    ): Organisation {
        $identifier = Str::lower(Str::random(10));

        return Organisation::create(array_merge([
            'name' => 'NorthPole Test Organisation',
            'slug' => "northpole-test-{$identifier}",
            'email' => "northpole-test-{$identifier}@example.com",
            'country' => 'IE',
            'timezone' => 'Europe/Dublin',
            'active' => true,
        ], $attributes));
    }

    protected function createTenantPermission(
        string $key,
        array $attributes = []
    ): Permission {
        return Permission::query()->updateOrCreate(
            [
                'key' => $key,
            ],
            array_merge([
                'name' => Str::headline($key),
                'description' => "Allows {$key}.",
                'module_key' => 'core',
                'is_active' => true,
            ], $attributes)
        );
    }

    /**
     * @param array<int, string> $permissions
     * @param array<string, mixed> $attributes
     */
    protected function createTenantRole(
        Organisation $organisation,
        array $permissions = [],
        array $attributes = []
    ): Role {
        $identifier = Str::lower(Str::random(10));

        $role = app(TenantContext::class)->runFor(
            $organisation,
            fn (): Role => Role::create(array_merge([
                'key' => "test-role-{$identifier}",
                'name' => 'Test Role',
                'description' => 'Role created by the NorthPole test foundation.',
                'is_system' => false,
                'is_active' => true,
            ], $attributes))
        );

        $permissionIds = collect($permissions)
            ->map(
                fn (string $permissionKey): int => $this
                    ->createTenantPermission($permissionKey)
                    ->id
            )
            ->all();

        if ($permissionIds !== []) {
            $role->permissions()->sync($permissionIds);
        }

        return $role;
    }

    /**
     * @param array<int, string> $permissions
     * @param array<string, mixed> $roleAttributes
     */
    protected function actingAsTenantUser(
        Organisation $organisation,
        array $permissions = [],
        ?User $user = null,
        array $roleAttributes = []
    ): User {
        $user ??= User::factory()->create();

        $role = $this->createTenantRole(
            organisation: $organisation,
            permissions: $permissions,
            attributes: $roleAttributes
        );

        $organisation->users()->syncWithoutDetaching([
            $user->id => [
                'role' => $role->key,
                'role_id' => $role->id,
                'is_active' => true,
                'joined_at' => now(),
            ],
        ]);

        Sanctum::actingAs($user);

        return $user;
    }

    /**
     * Authenticate a tenant user with every active permission currently
     * registered in the database.
     *
     * @param array<string, mixed> $roleAttributes
     */
    protected function actingAsTenantAdmin(
        Organisation $organisation,
        ?User $user = null,
        array $roleAttributes = []
    ): User {
        $permissions = Permission::query()
            ->where('is_active', true)
            ->pluck('key')
            ->all();

        return $this->actingAsTenantUser(
            organisation: $organisation,
            permissions: $permissions,
            user: $user,
            roleAttributes: array_merge([
                'name' => 'Tenant Administrator',
            ], $roleAttributes)
        );
    }

    protected function withTenant(
        Organisation $organisation
    ): static {
        return $this->withHeader(
            'X-Organisation-Id',
            (string) $organisation->id
        );
    }
}