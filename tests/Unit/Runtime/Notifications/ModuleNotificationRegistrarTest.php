<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Notifications;

use InvalidArgumentException;
use Northpole\Runtime\Notifications\ModuleNotificationRegistrar;
use Northpole\Runtime\Notifications\ModuleNotificationRegistry;
use PHPUnit\Framework\TestCase;

final class ModuleNotificationRegistrarTest extends TestCase
{
    public function test_it_registers_module_notifications(): void
    {
        $registry = new ModuleNotificationRegistry;

        $registrar = new ModuleNotificationRegistrar(
            $registry,
        );

        $registrar->register(
            'crm',
            [
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
            ],
        );

        self::assertSame(
            2,
            $registry->count(),
        );

        self::assertSame(
            CustomerCreatedNotification::class,
            $registry
                ->find(
                    'crm',
                    'customer-created',
                )
                ?->class,
        );
    }

    public function test_it_skips_an_empty_notification_list(): void
    {
        $registry = new ModuleNotificationRegistry;

        $registrar = new ModuleNotificationRegistrar(
            $registry,
        );

        $registrar->register(
            'crm',
            [],
        );

        self::assertTrue(
            $registry->isEmpty(),
        );
    }

    public function test_it_exposes_the_registry(): void
    {
        $registry = new ModuleNotificationRegistry;

        $registrar = new ModuleNotificationRegistrar(
            $registry,
        );

        self::assertSame(
            $registry,
            $registrar->registry(),
        );
    }

    public function test_it_rejects_an_empty_module_owner(): void
    {
        $registrar = new ModuleNotificationRegistrar(
            new ModuleNotificationRegistry,
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A notification module owner cannot be empty.',
        );

        $registrar->register(
            ' ',
            [],
        );
    }

    public function test_it_rejects_a_non_array_notification_definition(): void
    {
        $registrar = new ModuleNotificationRegistrar(
            new ModuleNotificationRegistry,
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A notification definition must be an array.',
        );

        $registrar->register(
            'crm',
            [
                'invalid',
            ],
        );
    }

    public function test_it_rejects_missing_notification_names(): void
    {
        $registrar = new ModuleNotificationRegistrar(
            new ModuleNotificationRegistry,
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Notification field [name] must be a string.',
        );

        $registrar->register(
            'crm',
            [
                [
                    'class' => CustomerCreatedNotification::class,
                    'channels' => [
                        'mail',
                    ],
                ],
            ],
        );
    }

    public function test_it_rejects_missing_notification_classes(): void
    {
        $registrar = new ModuleNotificationRegistrar(
            new ModuleNotificationRegistry,
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Notification field [class] must be a string.',
        );

        $registrar->register(
            'crm',
            [
                [
                    'name' => 'customer-created',
                    'channels' => [
                        'mail',
                    ],
                ],
            ],
        );
    }

    public function test_it_rejects_non_list_channels(): void
    {
        $registrar = new ModuleNotificationRegistrar(
            new ModuleNotificationRegistry,
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Notification field [channels] must be a list.',
        );

        $registrar->register(
            'crm',
            [
                [
                    'name' => 'customer-created',
                    'class' => CustomerCreatedNotification::class,
                    'channels' => [
                        'mail' => true,
                    ],
                ],
            ],
        );
    }

    public function test_it_rejects_duplicate_notifications(): void
    {
        $registry = new ModuleNotificationRegistry;

        $registrar = new ModuleNotificationRegistrar(
            $registry,
        );

        $notification = [
            'name' => 'customer-created',
            'class' => CustomerCreatedNotification::class,
            'channels' => [
                'mail',
            ],
        ];

        $registrar->register(
            'crm',
            [
                $notification,
            ],
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Notification [customer-created] is already registered for module [crm].',
        );

        $registrar->register(
            'crm',
            [
                $notification,
            ],
        );
    }
}

final class CustomerCreatedNotification {}

final class CustomerUpdatedNotification {}