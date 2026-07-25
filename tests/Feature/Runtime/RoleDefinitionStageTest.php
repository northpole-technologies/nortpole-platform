<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\ModuleManifestContract;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Lifecycle\BootContext;
use Northpole\Runtime\Lifecycle\RoleDefinitionStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Roles\RoleDefinition;
use Northpole\Runtime\Roles\RoleDefinitionRegistry;
use Northpole\Runtime\Runtime;
use Tests\TestCase;

final class RoleDefinitionStageTest extends TestCase
{
    public function test_it_registers_module_role_definitions(): void
    {
        $registry = new RoleDefinitionRegistry;

        $stage = new RoleDefinitionStage(
            $registry
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock(
                    slug: 'crm',
                    roles: [
                        [
                            'key' => 'sales',
                            'name' => 'Sales',
                            'description' => 'Sales team members.',
                            'permissions' => [
                                'crm.customers.view',
                                'crm.customers.create',
                            ],
                        ],
                    ],
                )
            )
        );

        $role = $registry->get('sales');

        $this->assertInstanceOf(
            RoleDefinition::class,
            $role
        );

        $this->assertSame(
            'Sales',
            $role->name()
        );

        $this->assertSame(
            [
                'crm.customers.create',
                'crm.customers.view',
            ],
            $role->permissions()
        );

        $this->assertSame(
            [
                'crm',
            ],
            $role->contributingModules()
        );
    }

    public function test_it_merges_role_contributions_from_multiple_modules(): void
    {
        $registry = new RoleDefinitionRegistry;

        $stage = new RoleDefinitionStage(
            $registry
        );

        $runtime = $this->createRuntime();

        $stage->boot(
            new BootContext(
                $runtime,
                $this->createManifestMock(
                    slug: 'crm',
                    roles: [
                        [
                            'key' => 'sales',
                            'name' => 'Sales',
                            'permissions' => [
                                'crm.customers.view',
                            ],
                        ],
                    ],
                )
            )
        );

        $stage->boot(
            new BootContext(
                $runtime,
                $this->createManifestMock(
                    slug: 'inventory',
                    roles: [
                        [
                            'key' => 'sales',
                            'name' => 'Sales',
                            'permissions' => [
                                'inventory.stock.view',
                            ],
                        ],
                    ],
                )
            )
        );

        $role = $registry->get('sales');

        $this->assertInstanceOf(
            RoleDefinition::class,
            $role
        );

        $this->assertSame(
            [
                'crm.customers.view',
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
            $registry->count()
        );
    }

    public function test_it_skips_modules_without_role_definitions(): void
    {
        $registry = new RoleDefinitionRegistry;

        $stage = new RoleDefinitionStage(
            $registry
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock(
                    slug: 'crm',
                    roles: [],
                )
            )
        );

        $this->assertTrue(
            $registry->isEmpty()
        );
    }

    public function test_it_rejects_non_structured_role_definitions(): void
    {
        $registry = new RoleDefinitionRegistry;

        $stage = new RoleDefinitionStage(
            $registry
        );

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Roles for module [crm] must be structured definitions.'
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock(
                    slug: 'crm',
                    roles: [
                        'sales',
                    ],
                )
            )
        );
    }

    public function test_it_rejects_role_definitions_without_a_key(): void
    {
        $registry = new RoleDefinitionRegistry;

        $stage = new RoleDefinitionStage(
            $registry
        );

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'A role definition must have a key.'
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock(
                    slug: 'crm',
                    roles: [
                        [
                            'name' => 'Sales',
                        ],
                    ],
                )
            )
        );
    }

    public function test_it_rejects_role_definitions_without_a_name(): void
    {
        $registry = new RoleDefinitionRegistry;

        $stage = new RoleDefinitionStage(
            $registry
        );

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'A role definition must have a name.'
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock(
                    slug: 'crm',
                    roles: [
                        [
                            'key' => 'sales',
                        ],
                    ],
                )
            )
        );
    }

    /**
     * @param  array<int, mixed>  $roles
     */
    private function createManifestMock(
        string $slug,
        array $roles,
    ): ModuleManifestContract {
        $manifest = $this->createMock(
            ModuleManifestContract::class
        );

        $manifest
            ->method('slug')
            ->willReturn($slug);

        $manifest
            ->expects($this->once())
            ->method('roles')
            ->willReturn($roles);

        return $manifest;
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
