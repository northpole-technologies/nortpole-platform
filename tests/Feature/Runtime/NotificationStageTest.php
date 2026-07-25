<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\ModuleManifestContract;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Lifecycle\BootContext;
use Northpole\Runtime\Lifecycle\NotificationStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Notifications\ModuleNotificationRegistrar;
use Northpole\Runtime\Notifications\ModuleNotificationRegistry;
use Northpole\Runtime\Runtime;
use Tests\TestCase;

final class NotificationStageTest extends TestCase
{
    public function test_it_registers_module_notifications(): void
    {
        $registry = new ModuleNotificationRegistry;

        $stage = new NotificationStage(
            new ModuleNotificationRegistrar(
                $registry
            ),
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    [
                        'name' => 'customer-created',
                        'class' => CustomerCreatedNotification::class,
                        'channels' => [
                            'mail',
                            'database',
                        ],
                        'queue' => 'notifications',
                    ],
                    [
                        'name' => 'customer-updated',
                        'class' => CustomerUpdatedNotification::class,
                        'channels' => [
                            'database',
                        ],
                    ],
                ]),
            ),
        );

        $this->assertSame(
            2,
            $registry->count(),
        );

        $this->assertSame(
            [
                [
                    'module' => 'crm',
                    'name' => 'customer-created',
                    'class' => CustomerCreatedNotification::class,
                    'channels' => [
                        'database',
                        'mail',
                    ],
                    'queue' => 'notifications',
                ],
                [
                    'module' => 'crm',
                    'name' => 'customer-updated',
                    'class' => CustomerUpdatedNotification::class,
                    'channels' => [
                        'database',
                    ],
                    'queue' => null,
                ],
            ],
            array_map(
                static fn ($notification): array =>
                    $notification->toArray(),
                $registry->all(),
            ),
        );
    }

    public function test_it_skips_modules_without_notifications(): void
    {
        $registry = new ModuleNotificationRegistry;

        $stage = new NotificationStage(
            new ModuleNotificationRegistrar(
                $registry
            ),
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([]),
            ),
        );

        $this->assertTrue(
            $registry->isEmpty(),
        );

        $this->assertSame(
            0,
            $registry->count(),
        );
    }

    public function test_it_rejects_empty_module_slugs(): void
    {
        $registry = new ModuleNotificationRegistry;

        $stage = new NotificationStage(
            new ModuleNotificationRegistrar(
                $registry
            ),
        );

        $manifest = $this->createMock(
            ModuleManifestContract::class,
        );

        $manifest
            ->method('slug')
            ->willReturn('   ');

        $manifest
            ->method('notifications')
            ->willReturn([
                [
                    'name' => 'customer-created',
                    'class' => CustomerCreatedNotification::class,
                    'channels' => ['mail'],
                ],
            ]);

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A notification module owner cannot be empty.',
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $manifest,
            ),
        );
    }

    public function test_it_rejects_duplicate_notifications(): void
    {
        $registry = new ModuleNotificationRegistry;

        $stage = new NotificationStage(
            new ModuleNotificationRegistrar(
                $registry
            ),
        );

        $context = new BootContext(
            $this->createRuntime(),
            $this->createManifestMock([
                [
                    'name' => 'customer-created',
                    'class' => CustomerCreatedNotification::class,
                    'channels' => ['mail'],
                ],
            ]),
        );

        $stage->boot(
            $context
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Notification [customer-created] is already registered for module [crm].',
        );

        $stage->boot(
            $context
        );
    }

    public function test_stage_has_the_expected_name_and_priority(): void
    {
        $stage = new NotificationStage(
            new ModuleNotificationRegistrar(
                new ModuleNotificationRegistry
            ),
        );

        $this->assertSame(
            'notifications',
            $stage->name(),
        );

        $this->assertSame(
            660,
            $stage->priority(),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $notifications
     */
    private function createManifestMock(
        array $notifications,
    ): ModuleManifestContract {
        $manifest = $this->createMock(
            ModuleManifestContract::class,
        );

        $manifest
            ->method('slug')
            ->willReturn('crm');

        $manifest
            ->method('notifications')
            ->willReturn($notifications);

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

final class CustomerCreatedNotification {}

final class CustomerUpdatedNotification {}