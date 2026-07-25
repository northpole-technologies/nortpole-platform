<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\ModuleManifestContract;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Events\ModuleEventRegistrar;
use Northpole\Runtime\Events\ModuleEventRegistry;
use Northpole\Runtime\Lifecycle\BootContext;
use Northpole\Runtime\Lifecycle\EventSubscriberStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Runtime;
use Tests\TestCase;

final class EventSubscriberStageTest extends TestCase
{
    public function test_it_registers_module_event_subscribers(): void
    {
        $registry = new ModuleEventRegistry;

        $stage = new EventSubscriberStage(
            new ModuleEventRegistrar($registry),
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    'crm.customer.created' => [
                        CustomerCreatedAuditListener::class,
                        CustomerCreatedNotificationListener::class,
                    ],
                    'crm.customer.updated' => [
                        CustomerUpdatedAuditListener::class,
                    ],
                ]),
            ),
        );

        $this->assertSame(3, $registry->count());

        $this->assertSame(
            [
                CustomerCreatedAuditListener::class,
                CustomerCreatedNotificationListener::class,
            ],
            $registry->listeners(
                'crm.customer.created',
            ),
        );

        $this->assertSame(
            [
                CustomerUpdatedAuditListener::class,
            ],
            $registry->listeners(
                'crm.customer.updated',
            ),
        );
    }

    public function test_it_skips_modules_without_event_subscribers(): void
    {
        $registry = new ModuleEventRegistry;

        $stage = new EventSubscriberStage(
            new ModuleEventRegistrar($registry),
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([]),
            ),
        );

        $this->assertSame(0, $registry->count());
        $this->assertSame([], $registry->all());
    }

    public function test_it_rejects_empty_module_slugs(): void
    {
        $registry = new ModuleEventRegistry;

        $stage = new EventSubscriberStage(
            new ModuleEventRegistrar($registry),
        );

        $manifest = $this->createMock(
            ModuleManifestContract::class,
        );

        $manifest
            ->method('slug')
            ->willReturn('   ');

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A module event subscriber owner cannot be empty.',
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $manifest,
            ),
        );
    }

    public function test_it_rejects_empty_event_names(): void
    {
        $registry = new ModuleEventRegistry;

        $stage = new EventSubscriberStage(
            new ModuleEventRegistrar($registry),
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Subscribed event names for module [crm] must be non-empty strings.',
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    '   ' => [
                        CustomerCreatedAuditListener::class,
                    ],
                ]),
            ),
        );
    }

    public function test_it_rejects_empty_listener_class_names(): void
    {
        $registry = new ModuleEventRegistry;

        $stage = new EventSubscriberStage(
            new ModuleEventRegistrar($registry),
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Event listeners for module [crm] and event [crm.customer.created] must be non-empty class names.',
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    'crm.customer.created' => [
                        '   ',
                    ],
                ]),
            ),
        );
    }

    public function test_it_rejects_duplicate_module_listener_registrations(): void
    {
        $registry = new ModuleEventRegistry;

        $stage = new EventSubscriberStage(
            new ModuleEventRegistrar($registry),
        );

        $context = new BootContext(
            $this->createRuntime(),
            $this->createManifestMock([
                'crm.customer.created' => [
                    CustomerCreatedAuditListener::class,
                ],
            ]),
        );

        $stage->boot($context);

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            sprintf(
                'Listener [%s] from module [crm] is already registered for event [crm.customer.created].',
                CustomerCreatedAuditListener::class,
            ),
        );

        $stage->boot($context);
    }

    /**
     * @param  array<string, array<int, string>>  $subscribers
     */
    private function createManifestMock(
        array $subscribers,
    ): ModuleManifestContract {
        $manifest = $this->createMock(
            ModuleManifestContract::class,
        );

        $manifest
            ->method('slug')
            ->willReturn('crm');

        $manifest
            ->method('eventSubscribers')
            ->willReturn($subscribers);

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

final class CustomerCreatedAuditListener {}

final class CustomerCreatedNotificationListener {}

final class CustomerUpdatedAuditListener {}
