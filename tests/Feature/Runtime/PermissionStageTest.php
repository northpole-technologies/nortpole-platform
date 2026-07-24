<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\ModuleManifestContract;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Lifecycle\BootContext;
use Northpole\Runtime\Lifecycle\PermissionStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Permissions\PermissionRegistry;
use Northpole\Runtime\Runtime;
use Tests\TestCase;

final class PermissionStageTest extends TestCase
{
    public function test_it_registers_module_permissions(): void
    {
        $registry = new PermissionRegistry();

        $stage = new PermissionStage($registry);

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    'crm.customers.view',
                    'crm.customers.create',
                ])
            )
        );

        $this->assertSame(2, $registry->count());
        $this->assertTrue(
            $registry->has('crm.customers.view')
        );
        $this->assertTrue(
            $registry->has('crm.customers.create')
        );

        $permission = $registry->get(
            'crm.customers.view'
        );

        $this->assertNotNull($permission);
        $this->assertSame(
            'crm',
            $permission->moduleSlug()
        );
    }

    public function test_it_skips_modules_without_permissions(): void
    {
        $registry = new PermissionRegistry();

        $stage = new PermissionStage($registry);

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([])
            )
        );

        $this->assertSame(0, $registry->count());
        $this->assertSame([], $registry->all());
    }

    public function test_it_rejects_non_string_permissions(): void
    {
        $registry = new PermissionRegistry();

        $stage = new PermissionStage($registry);

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Permissions for module [crm] must be non-empty strings.'
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    ['name' => 'crm.customers.view'],
                ])
            )
        );
    }

    public function test_it_rejects_empty_permission_names(): void
    {
        $registry = new PermissionRegistry();

        $stage = new PermissionStage($registry);

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Permissions for module [crm] must be non-empty strings.'
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    '   ',
                ])
            )
        );
    }

    /**
     * @param array<int, mixed> $permissions
     */
    private function createManifestMock(
        array $permissions
    ): ModuleManifestContract {
        $manifest = $this->createMock(
            ModuleManifestContract::class
        );

        $manifest
            ->method('slug')
            ->willReturn('crm');

        $manifest
            ->expects($this->once())
            ->method('permissions')
            ->willReturn($permissions);

        return $manifest;
    }

    private function createRuntime(): Runtime
    {
        $repository = new ModuleRepository();

        return new Runtime(
            new ModuleDiscovery(
                new ModuleFinder(),
                new ManifestLoader(),
                $repository
            ),
            $repository,
            new ModuleDependencyResolver(),
            base_path('modules')
        );
    }
}
