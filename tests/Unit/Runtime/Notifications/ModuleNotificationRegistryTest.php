<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Notifications;

use InvalidArgumentException;
use Northpole\Runtime\Notifications\ModuleNotificationRegistry;
use Northpole\Runtime\Notifications\NotificationDefinition;
use PHPUnit\Framework\TestCase;

final class ModuleNotificationRegistryTest extends TestCase
{
    public function test_it_registers_a_notification(): void
    {
        $registry = new ModuleNotificationRegistry;

        $definition = $this->definition(
            module: 'crm',
            name: 'customer-created',
        );

        $registry->register(
            $definition,
        );

        self::assertSame(
            1,
            $registry->count(),
        );

        self::assertSame(
            $definition,
            $registry->find(
                'crm',
                'customer-created',
            ),
        );
    }

    public function test_it_registers_multiple_notifications(): void
    {
        $registry = new ModuleNotificationRegistry;

        $registry->registerMany([
            $this->definition(
                module: 'crm',
                name: 'customer-created',
            ),
            $this->definition(
                module: 'crm',
                name: 'customer-updated',
            ),
        ]);

        self::assertSame(
            2,
            $registry->count(),
        );
    }

    public function test_different_modules_can_share_a_notification_name(): void
    {
        $registry = new ModuleNotificationRegistry;

        $registry->registerMany([
            $this->definition(
                module: 'crm',
                name: 'record-created',
            ),
            $this->definition(
                module: 'inventory',
                name: 'record-created',
            ),
        ]);

        self::assertSame(
            2,
            $registry->count(),
        );
    }

    public function test_it_rejects_duplicate_module_notification_names(): void
    {
        $registry = new ModuleNotificationRegistry;

        $registry->register(
            $this->definition(
                module: 'crm',
                name: 'customer-created',
            ),
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Notification [customer-created] is already registered for module [crm].',
        );

        $registry->register(
            $this->definition(
                module: 'crm',
                name: 'customer-created',
            ),
        );
    }

    public function test_it_filters_notifications_by_module(): void
    {
        $registry = new ModuleNotificationRegistry;

        $registry->registerMany([
            $this->definition(
                module: 'inventory',
                name: 'stock-low',
            ),
            $this->definition(
                module: 'crm',
                name: 'customer-created',
            ),
            $this->definition(
                module: 'crm',
                name: 'customer-updated',
            ),
        ]);

        self::assertSame(
            [
                'customer-created',
                'customer-updated',
            ],
            array_map(
                static fn (
                    NotificationDefinition $definition
                ): string => $definition->name,
                $registry->forModule('crm'),
            ),
        );
    }

    public function test_an_empty_module_filter_returns_no_notifications(): void
    {
        $registry = new ModuleNotificationRegistry;

        $registry->register(
            $this->definition(
                module: 'crm',
                name: 'customer-created',
            ),
        );

        self::assertSame(
            [],
            $registry->forModule(' '),
        );
    }

    public function test_it_orders_notifications_by_module_and_name(): void
    {
        $registry = new ModuleNotificationRegistry;

        $registry->registerMany([
            $this->definition(
                module: 'inventory',
                name: 'stock-low',
            ),
            $this->definition(
                module: 'crm',
                name: 'customer-updated',
            ),
            $this->definition(
                module: 'crm',
                name: 'customer-created',
            ),
        ]);

        self::assertSame(
            [
                'crm:customer-created',
                'crm:customer-updated',
                'inventory:stock-low',
            ],
            array_map(
                static fn (
                    NotificationDefinition $definition
                ): string => $definition->key(),
                $registry->all(),
            ),
        );
    }

    public function test_it_can_be_cleared(): void
    {
        $registry = new ModuleNotificationRegistry;

        $registry->register(
            $this->definition(
                module: 'crm',
                name: 'customer-created',
            ),
        );

        $registry->clear();

        self::assertTrue(
            $registry->isEmpty(),
        );

        self::assertSame(
            0,
            $registry->count(),
        );
    }

    private function definition(
        string $module,
        string $name,
    ): NotificationDefinition {
        return new NotificationDefinition(
            module: $module,
            name: $name,
            class: TestNotification::class,
            channels: [
                'mail',
            ],
        );
    }
}

final class TestNotification {}