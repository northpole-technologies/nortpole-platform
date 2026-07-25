<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use App\Models\Organisation;
use App\Models\Permission as PermissionModel;
use App\Models\Role;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Northpole\Runtime\Permissions\Permission;
use Northpole\Runtime\Permissions\PermissionRegistry;
use Northpole\Runtime\Roles\RoleDefinition;
use Northpole\Runtime\Roles\RoleDefinitionRegistry;
use Northpole\Runtime\Synchronisation\TenantAccessSynchroniser;
use Tests\TestCase;

final class TenantAccessSynchroniserTest extends TestCase
{
    use RefreshDatabase;

    private TenantContext $tenantContext;

    private PermissionRegistry $permissions;

    private RoleDefinitionRegistry $roles;

    private TenantAccessSynchroniser $synchroniser;

    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantContext = app(
            TenantContext::class
        );

        $this->tenantContext->clear();

        $this->permissions = new PermissionRegistry;
        $this->roles = new RoleDefinitionRegistry;

        $this->synchroniser = new TenantAccessSynchroniser(
            permissions: $this->permissions,
            roles: $this->roles,
        );

        $this->organisation = Organisation::query()
            ->create([
                'name' => 'NorthPole Test Organisation',
                'slug' => 'northpole-test-organisation',
                'email' => 'runtime@example.test',
                'phone' => null,
                'website' => null,
                'country' => 'IE',
                'timezone' => 'Europe/Dublin',
                'active' => true,
            ]);

        $this->tenantContext->set(
            $this->organisation
        );
    }

    protected function tearDown(): void
    {
        $this->tenantContext->clear();

        parent::tearDown();
    }

    public function test_it_creates_runtime_permissions(): void
    {
        $this->permissions->add(
            new Permission(
                moduleSlug: 'crm',
                name: 'crm.customer.view',
                title: 'View customers',
                description: 'View CRM customers.',
                group: 'Customers',
            )
        );

        $this->synchroniser->synchronise(
            $this->organisation
        );

        $this->assertDatabaseHas(
            'permissions',
            [
                'key' => 'crm.customer.view',
                'name' => 'View customers',
                'description' => 'View CRM customers.',
                'module_key' => 'crm',
                'is_active' => true,
            ]
        );
    }

    public function test_it_creates_tenant_system_roles(): void
    {
        $this->roles->add(
            new RoleDefinition(
                key: 'crm-manager',
                name: 'CRM Manager',
                description: 'Manages CRM customers.',
                contributingModules: [
                    'crm',
                ],
                system: true,
            )
        );

        $this->synchroniser->synchronise(
            $this->organisation
        );

        $this->assertDatabaseHas(
            'roles',
            [
                'organisation_id' => $this->organisation->getKey(),
                'key' => 'crm-manager',
                'name' => 'CRM Manager',
                'description' => 'Manages CRM customers.',
                'is_system' => true,
                'is_active' => true,
            ]
        );
    }

    public function test_it_synchronises_role_permissions(): void
    {
        $this->permissions
            ->add(
                new Permission(
                    moduleSlug: 'crm',
                    name: 'crm.customer.view',
                    title: 'View customers',
                )
            )
            ->add(
                new Permission(
                    moduleSlug: 'crm',
                    name: 'crm.customer.create',
                    title: 'Create customers',
                )
            );

        $this->roles->add(
            new RoleDefinition(
                key: 'crm-manager',
                name: 'CRM Manager',
                permissions: [
                    'crm.customer.view',
                    'crm.customer.create',
                ],
                contributingModules: [
                    'crm',
                ],
            )
        );

        $this->synchroniser->synchronise(
            $this->organisation
        );

        $role = Role::query()
            ->where(
                'organisation_id',
                $this->organisation->getKey()
            )
            ->where(
                'key',
                'crm-manager'
            )
            ->firstOrFail();

        $permissionKeys = $role->permissions()
            ->orderBy('permissions.key')
            ->pluck('permissions.key')
            ->all();

        $this->assertSame(
            [
                'crm.customer.create',
                'crm.customer.view',
            ],
            $permissionKeys
        );
    }

    public function test_it_updates_existing_runtime_records(): void
    {
        PermissionModel::query()
            ->create([
                'key' => 'crm.customer.view',
                'name' => 'Old permission name',
                'description' => null,
                'module_key' => 'legacy',
                'is_active' => false,
            ]);

        Role::query()
            ->create([
                'organisation_id' => $this->organisation->getKey(),
                'key' => 'crm-manager',
                'name' => 'Old role name',
                'description' => null,
                'is_system' => true,
                'is_active' => false,
            ]);

        $this->permissions->add(
            new Permission(
                moduleSlug: 'crm',
                name: 'crm.customer.view',
                title: 'View customers',
                description: 'View CRM customers.',
            )
        );

        $this->roles->add(
            new RoleDefinition(
                key: 'crm-manager',
                name: 'CRM Manager',
                description: 'Manages CRM customers.',
                permissions: [
                    'crm.customer.view',
                ],
                contributingModules: [
                    'crm',
                ],
            )
        );

        $this->synchroniser->synchronise(
            $this->organisation
        );

        $this->assertDatabaseCount(
            'permissions',
            1
        );

        $this->assertDatabaseHas(
            'permissions',
            [
                'key' => 'crm.customer.view',
                'name' => 'View customers',
                'description' => 'View CRM customers.',
                'module_key' => 'crm',
                'is_active' => true,
            ]
        );

        $this->assertDatabaseCount(
            'roles',
            1
        );

        $this->assertDatabaseHas(
            'roles',
            [
                'organisation_id' => $this->organisation->getKey(),
                'key' => 'crm-manager',
                'name' => 'CRM Manager',
                'description' => 'Manages CRM customers.',
                'is_system' => true,
                'is_active' => true,
            ]
        );
    }

    public function test_it_preserves_unrelated_custom_roles(): void
    {
        Role::query()
            ->create([
                'organisation_id' => $this->organisation->getKey(),
                'key' => 'warehouse-manager',
                'name' => 'Warehouse Manager',
                'description' => 'A tenant-created role.',
                'is_system' => false,
                'is_active' => true,
            ]);

        $this->roles->add(
            new RoleDefinition(
                key: 'crm-manager',
                name: 'CRM Manager',
                contributingModules: [
                    'crm',
                ],
            )
        );

        $this->synchroniser->synchronise(
            $this->organisation
        );

        $this->assertDatabaseHas(
            'roles',
            [
                'organisation_id' => $this->organisation->getKey(),
                'key' => 'warehouse-manager',
                'name' => 'Warehouse Manager',
                'description' => 'A tenant-created role.',
                'is_system' => false,
                'is_active' => true,
            ]
        );

        $this->assertDatabaseHas(
            'roles',
            [
                'organisation_id' => $this->organisation->getKey(),
                'key' => 'crm-manager',
                'name' => 'CRM Manager',
                'is_system' => true,
                'is_active' => true,
            ]
        );
    }

    public function test_it_rejects_a_custom_role_that_conflicts_with_a_runtime_role(): void
    {
        Role::query()
            ->create([
                'organisation_id' => $this->organisation->getKey(),
                'key' => 'crm-manager',
                'name' => 'Custom CRM Manager',
                'description' => 'A tenant-created role.',
                'is_system' => false,
                'is_active' => true,
            ]);

        $this->permissions->add(
            new Permission(
                moduleSlug: 'crm',
                name: 'crm.customer.view',
                title: 'View customers',
            )
        );

        $this->roles->add(
            new RoleDefinition(
                key: 'crm-manager',
                name: 'CRM Manager',
                permissions: [
                    'crm.customer.view',
                ],
                contributingModules: [
                    'crm',
                ],
            )
        );

        try {
            $this->synchroniser->synchronise(
                $this->organisation
            );

            $this->fail(
                'Expected a conflicting tenant role to reject synchronisation.'
            );
        } catch (LogicException $exception) {
            $this->assertSame(
                'Tenant role [crm-manager] conflicts with a runtime role definition.',
                $exception->getMessage()
            );
        }

        $this->assertDatabaseHas(
            'roles',
            [
                'organisation_id' => $this->organisation->getKey(),
                'key' => 'crm-manager',
                'name' => 'Custom CRM Manager',
                'is_system' => false,
            ]
        );

        $this->assertDatabaseMissing(
            'permissions',
            [
                'key' => 'crm.customer.view',
            ]
        );
    }

    public function test_it_rolls_back_when_a_role_references_an_unknown_permission(): void
    {
        $this->permissions->add(
            new Permission(
                moduleSlug: 'crm',
                name: 'crm.customer.view',
                title: 'View customers',
            )
        );

        $this->roles->add(
            new RoleDefinition(
                key: 'crm-manager',
                name: 'CRM Manager',
                permissions: [
                    'crm.customer.view',
                    'crm.customer.missing',
                ],
                contributingModules: [
                    'crm',
                ],
            )
        );

        try {
            $this->synchroniser->synchronise(
                $this->organisation
            );

            $this->fail(
                'Expected an unknown permission to reject synchronisation.'
            );
        } catch (LogicException $exception) {
            $this->assertSame(
                'Runtime role [crm-manager] references unknown permission [crm.customer.missing].',
                $exception->getMessage()
            );
        }

        $this->assertDatabaseMissing(
            'permissions',
            [
                'key' => 'crm.customer.view',
            ]
        );

        $this->assertDatabaseMissing(
            'roles',
            [
                'organisation_id' => $this->organisation->getKey(),
                'key' => 'crm-manager',
            ]
        );

        $this->assertDatabaseCount(
            'permission_role',
            0
        );
    }
}