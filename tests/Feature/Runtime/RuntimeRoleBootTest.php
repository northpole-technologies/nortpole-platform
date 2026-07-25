<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Lifecycle\BootPipeline;
use Northpole\Runtime\Lifecycle\PermissionStage;
use Northpole\Runtime\Lifecycle\RoleDefinitionStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Permissions\PermissionRegistry;
use Northpole\Runtime\Roles\RoleDefinitionRegistry;
use Northpole\Runtime\Runtime;
use Tests\TestCase;

final class RuntimeRoleBootTest extends TestCase
{
    public function test_runtime_boot_merges_role_contributions_from_real_modules(): void
    {
        $permissionRegistry = new PermissionRegistry;
        $roleRegistry = new RoleDefinitionRegistry;

        $runtime = $this->createRuntime();

        $runtime->discover();

        $pipeline = (new BootPipeline)
            ->add(
                new PermissionStage(
                    $permissionRegistry
                )
            )
            ->add(
                new RoleDefinitionStage(
                    $roleRegistry
                )
            );

        $pipeline->boot($runtime);

        $this->assertTrue(
            $runtime->has('crm')
        );

        $this->assertTrue(
            $runtime->has('inventory')
        );

        $this->assertTrue(
            $permissionRegistry->has(
                'crm.customers.view'
            )
        );

        $this->assertTrue(
            $permissionRegistry->has(
                'inventory.stock.view'
            )
        );

        $this->assertTrue(
            $permissionRegistry->has(
                'inventory.stock.update'
            )
        );

        $role = $roleRegistry->get('sales');

        $this->assertNotNull($role);

        $this->assertSame(
            'Sales',
            $role->name()
        );

        $this->assertSame(
            'Sales team members with access to customer and stock operations.',
            $role->description()
        );

        $this->assertSame(
            [
                'crm.customers.create',
                'crm.customers.update',
                'crm.customers.view',
                'inventory.stock.update',
                'inventory.stock.view',
            ],
            $role->permissions()
        );

        $this->assertSame(
            [
                'crm',
                'inventory',
            ],
            $role->contributingModules()
        );

        $this->assertSame(
            1,
            $roleRegistry->count()
        );

        $this->assertSame(
            [
                'sales',
            ],
            array_map(
                static fn ($role): string => $role->key(),
                $roleRegistry->forModule('crm')
            )
        );

        $this->assertSame(
            [
                'sales',
            ],
            array_map(
                static fn ($role): string => $role->key(),
                $roleRegistry->forModule('inventory')
            )
        );

        $this->assertSame(
            [
                'sales',
            ],
            array_map(
                static fn ($role): string => $role->key(),
                $roleRegistry->withPermission(
                    'crm.customers.view'
                )
            )
        );

        $this->assertSame(
            [
                'sales',
            ],
            array_map(
                static fn ($role): string => $role->key(),
                $roleRegistry->withPermission(
                    'inventory.stock.view'
                )
            )
        );
    }

    public function test_runtime_boot_registers_all_permissions_declared_by_the_two_modules(): void
    {
        $permissionRegistry = new PermissionRegistry;
        $roleRegistry = new RoleDefinitionRegistry;

        $runtime = $this->createRuntime();

        $runtime->discover();

        $pipeline = (new BootPipeline)
            ->add(
                new PermissionStage(
                    $permissionRegistry
                )
            )
            ->add(
                new RoleDefinitionStage(
                    $roleRegistry
                )
            );

        $pipeline->boot($runtime);

        $this->assertSame(
            [
                'crm.customers.create',
                'crm.customers.delete',
                'crm.customers.update',
                'crm.customers.view',
                'inventory.stock.create',
                'inventory.stock.delete',
                'inventory.stock.update',
                'inventory.stock.view',
            ],
            array_map(
                static fn ($permission): string => $permission->key(),
                array_filter(
                    $permissionRegistry->all(),
                    static fn ($permission): bool => in_array(
                        $permission->moduleSlug(),
                        [
                            'crm',
                            'inventory',
                        ],
                        true,
                    )
                )
            )
        );
    }

    private function createRuntime(): Runtime
    {
        $repository = new ModuleRepository;

        return new Runtime(
            new ModuleDiscovery(
                new ModuleFinder,
                new ManifestLoader,
                $repository,
            ),
            $repository,
            new ModuleDependencyResolver,
            base_path('modules'),
        );
    }
}
