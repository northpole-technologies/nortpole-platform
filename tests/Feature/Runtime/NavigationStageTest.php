<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\ModuleManifestContract;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Lifecycle\BootContext;
use Northpole\Runtime\Lifecycle\NavigationStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Navigation\NavigationRegistry;
use Northpole\Runtime\Runtime;
use Tests\TestCase;

final class NavigationStageTest extends TestCase
{
    public function test_it_registers_module_navigation(): void
    {
        $registry = new NavigationRegistry();

        $stage = new NavigationStage($registry);

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    [
                        'label' => 'Customers',
                        'route' => 'crm.customers.index',
                        'icon' => 'users',
                        'permission' => 'crm.customers.view',
                        'group' => 'CRM',
                        'order' => 10,
                    ],
                ])
            )
        );

        $this->assertSame(1, $registry->count());

        $item = $registry->all()[0];

        $this->assertSame('crm', $item->moduleSlug());
        $this->assertSame('Customers', $item->label());
        $this->assertSame(
            'crm.customers.index',
            $item->route()
        );
        $this->assertSame('users', $item->icon());
        $this->assertSame(
            'crm.customers.view',
            $item->permission()
        );
        $this->assertSame('CRM', $item->group());
        $this->assertSame(10, $item->order());
    }

    public function test_it_registers_multiple_navigation_items(): void
    {
        $registry = new NavigationRegistry();

        $stage = new NavigationStage($registry);

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    [
                        'label' => 'Customers',
                        'route' => 'crm.customers.index',
                        'order' => 20,
                    ],
                    [
                        'label' => 'Contacts',
                        'route' => 'crm.contacts.index',
                        'order' => 10,
                    ],
                ])
            )
        );

        $this->assertSame(2, $registry->count());

        $this->assertSame(
            [
                'Contacts',
                'Customers',
            ],
            array_map(
                static fn ($item): string =>
                    $item->label(),
                $registry->all()
            )
        );
    }

    public function test_it_skips_modules_without_navigation(): void
    {
        $registry = new NavigationRegistry();

        $stage = new NavigationStage($registry);

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([])
            )
        );

        $this->assertSame(0, $registry->count());
        $this->assertSame([], $registry->all());
    }

    public function test_it_rejects_non_object_navigation_entries(): void
    {
        $registry = new NavigationRegistry();

        $stage = new NavigationStage($registry);

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Navigation entries for module [crm] must be objects.'
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    'crm.customers.index',
                ])
            )
        );
    }

    /**
     * @param array<int, mixed> $navigation
     */
    private function createManifestMock(
        array $navigation
    ): ModuleManifestContract {
        $manifest = $this->createMock(
            ModuleManifestContract::class
        );

        $manifest
            ->method('slug')
            ->willReturn('crm');

        $manifest
            ->expects($this->once())
            ->method('navigation')
            ->willReturn($navigation);

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
            base_path('modules')
        );
    }
}