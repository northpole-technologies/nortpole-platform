<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Capabilities\CapabilityRegistry;
use Northpole\Runtime\Contracts\ModuleManifestContract;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Lifecycle\BootContext;
use Northpole\Runtime\Lifecycle\CapabilityStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Runtime;
use Tests\TestCase;

final class CapabilityStageTest extends TestCase
{
    public function test_it_registers_module_capabilities(): void
    {
        $registry = new CapabilityRegistry();

        $stage = new CapabilityStage($registry);

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    'customers',
                    'contacts',
                    'notes',
                ])
            )
        );

        $this->assertSame(3, $registry->count());

        $this->assertTrue(
            $registry->has(
                'crm',
                'customers'
            )
        );

        $this->assertTrue(
            $registry->has(
                'crm',
                'contacts'
            )
        );

        $this->assertTrue(
            $registry->has(
                'crm',
                'notes'
            )
        );
    }

    public function test_it_skips_modules_without_capabilities(): void
    {
        $registry = new CapabilityRegistry();

        $stage = new CapabilityStage($registry);

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([])
            )
        );

        $this->assertSame(0, $registry->count());
        $this->assertSame([], $registry->all());
    }

    public function test_it_rejects_non_string_capabilities(): void
    {
        $registry = new CapabilityRegistry();

        $stage = new CapabilityStage($registry);

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Capabilities for module [crm] must be non-empty strings.'
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    ['name' => 'customers'],
                ])
            )
        );
    }

    public function test_it_rejects_empty_capability_names(): void
    {
        $registry = new CapabilityRegistry();

        $stage = new CapabilityStage($registry);

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Capabilities for module [crm] must be non-empty strings.'
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
     * @param array<int, mixed> $capabilities
     */
    private function createManifestMock(
        array $capabilities
    ): ModuleManifestContract {
        $manifest = $this->createMock(
            ModuleManifestContract::class
        );

        $manifest
            ->method('slug')
            ->willReturn('crm');

        $manifest
            ->expects($this->once())
            ->method('capabilities')
            ->willReturn($capabilities);

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
