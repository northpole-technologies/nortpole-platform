<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Notifications;

use InvalidArgumentException;
use Northpole\Runtime\Notifications\NotificationDefinition;
use PHPUnit\Framework\TestCase;

final class NotificationDefinitionTest extends TestCase
{
    public function test_it_preserves_a_notification_definition(): void
    {
        $definition = new NotificationDefinition(
            module: 'crm',
            name: 'customer-created',
            class: DefinitionCustomerCreatedNotification::class,
            channels: [
                'mail',
                'database',
            ],
            queue: 'notifications',
        );

        self::assertSame(
            [
                'module' => 'crm',
                'name' => 'customer-created',
                'class' => DefinitionCustomerCreatedNotification::class,
                'channels' => [
                    'database',
                    'mail',
                ],
                'queue' => 'notifications',
            ],
            $definition->toArray(),
        );
    }

    public function test_it_normalises_string_values_and_channels(): void
    {
        $definition = new NotificationDefinition(
            module: ' crm ',
            name: ' customer-created ',
            class: ' '.DefinitionCustomerCreatedNotification::class.' ',
            channels: [
                ' mail ',
                'database',
                'mail',
            ],
            queue: ' notifications ',
        );

        self::assertSame(
            'crm',
            $definition->module,
        );

        self::assertSame(
            'customer-created',
            $definition->name,
        );

        self::assertSame(
            DefinitionCustomerCreatedNotification::class,
            $definition->class,
        );

        self::assertSame(
            [
                'database',
                'mail',
            ],
            $definition->channels,
        );

        self::assertSame(
            'notifications',
            $definition->queue,
        );
    }

    public function test_it_creates_a_stable_key(): void
    {
        $definition = new NotificationDefinition(
            module: 'crm',
            name: 'customer-created',
            class: DefinitionCustomerCreatedNotification::class,
            channels: [
                'mail',
            ],
        );

        self::assertSame(
            'crm:customer-created',
            $definition->key(),
        );
    }

    public function test_it_rejects_an_empty_module_owner(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A notification module owner cannot be empty.',
        );

        new NotificationDefinition(
            module: ' ',
            name: 'customer-created',
            class: DefinitionCustomerCreatedNotification::class,
            channels: [
                'mail',
            ],
        );
    }

    public function test_it_rejects_an_empty_notification_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A notification name cannot be empty.',
        );

        new NotificationDefinition(
            module: 'crm',
            name: ' ',
            class: DefinitionCustomerCreatedNotification::class,
            channels: [
                'mail',
            ],
        );
    }

    public function test_it_rejects_an_empty_notification_class(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A notification class cannot be empty.',
        );

        new NotificationDefinition(
            module: 'crm',
            name: 'customer-created',
            class: ' ',
            channels: [
                'mail',
            ],
        );
    }

    public function test_it_rejects_notifications_without_channels(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A notification must define at least one channel.',
        );

        new NotificationDefinition(
            module: 'crm',
            name: 'customer-created',
            class: DefinitionCustomerCreatedNotification::class,
            channels: [],
        );
    }

    public function test_it_rejects_non_string_channels(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Notification channels must be strings.',
        );

        new NotificationDefinition(
            module: 'crm',
            name: 'customer-created',
            class: DefinitionCustomerCreatedNotification::class,
            channels: [
                'mail',
                123,
            ],
        );
    }

    public function test_it_rejects_an_empty_queue(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A notification queue cannot be empty.',
        );

        new NotificationDefinition(
            module: 'crm',
            name: 'customer-created',
            class: DefinitionCustomerCreatedNotification::class,
            channels: [
                'mail',
            ],
            queue: ' ',
        );
    }
}

final class DefinitionCustomerCreatedNotification {}