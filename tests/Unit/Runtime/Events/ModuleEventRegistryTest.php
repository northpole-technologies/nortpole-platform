<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Events;

use InvalidArgumentException;
use Northpole\Runtime\Events\Contracts\ModuleEventContract;
use Northpole\Runtime\Events\Contracts\ModuleEventListenerContract;
use Northpole\Runtime\Events\ModuleEventRegistry;
use PHPUnit\Framework\TestCase;

final class ModuleEventRegistryTest extends TestCase
{
    public function test_it_registers_a_listener(): void
    {
        $registry = new ModuleEventRegistry();

        $registry->listen(
            'customer.created',
            RegistryRecordingListener::class,
            'crm',
        );

        self::assertSame(
            [RegistryRecordingListener::class],
            $registry->listeners('customer.created'),
        );

        self::assertTrue(
            $registry->hasListeners('customer.created'),
        );

        self::assertSame(
            1,
            $registry->count(),
        );
    }

    public function test_it_registers_multiple_listeners(): void
    {
        $registry = new ModuleEventRegistry();

        $registry
            ->listen(
                'customer.created',
                RegistryRecordingListener::class,
                'crm',
            )
            ->listen(
                'customer.created',
                RegistrySecondaryListener::class,
                'inventory',
            );

        self::assertSame(
            [
                RegistryRecordingListener::class,
                RegistrySecondaryListener::class,
            ],
            $registry->listeners('customer.created'),
        );

        self::assertSame(
            2,
            $registry->count('customer.created'),
        );
    }

    public function test_it_keeps_events_separate(): void
    {
        $registry = new ModuleEventRegistry();

        $registry
            ->listen(
                'customer.created',
                RegistryRecordingListener::class,
                'crm',
            )
            ->listen(
                'inventory.low',
                RegistrySecondaryListener::class,
                'inventory',
            );

        self::assertSame(
            [RegistryRecordingListener::class],
            $registry->listeners('customer.created'),
        );

        self::assertSame(
            [RegistrySecondaryListener::class],
            $registry->listeners('inventory.low'),
        );
    }

    public function test_it_rejects_duplicate_listener_registration(): void
    {
        $registry = new ModuleEventRegistry();

        $registry->listen(
            'customer.created',
            RegistryRecordingListener::class,
            'crm',
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $registry->listen(
            'customer.created',
            RegistryRecordingListener::class,
            'crm',
        );
    }

    public function test_the_same_listener_can_be_owned_by_different_modules(): void
    {
        $registry = new ModuleEventRegistry();

        $registry
            ->listen(
                'customer.created',
                RegistryRecordingListener::class,
                'crm',
            )
            ->listen(
                'customer.created',
                RegistryRecordingListener::class,
                'inventory',
            );

        self::assertSame(
            [
                RegistryRecordingListener::class,
                RegistryRecordingListener::class,
            ],
            $registry->listeners('customer.created'),
        );
    }

    public function test_it_rejects_an_empty_event_name(): void
    {
        $registry = new ModuleEventRegistry();

        $this->expectException(
            InvalidArgumentException::class,
        );

        $registry->listen(
            ' ',
            RegistryRecordingListener::class,
            'crm',
        );
    }

    public function test_it_rejects_an_empty_module_owner(): void
    {
        $registry = new ModuleEventRegistry();

        $this->expectException(
            InvalidArgumentException::class,
        );

        $registry->listen(
            'customer.created',
            RegistryRecordingListener::class,
            ' ',
        );
    }

    public function test_it_rejects_an_empty_listener_class(): void
    {
        $registry = new ModuleEventRegistry();

        $this->expectException(
            InvalidArgumentException::class,
        );

        $registry->listen(
            'customer.created',
            '',
            'crm',
        );
    }

    public function test_it_returns_all_registered_listeners_sorted_by_event_name(): void
    {
        $registry = new ModuleEventRegistry();

        $registry
            ->listen(
                'inventory.low',
                RegistrySecondaryListener::class,
                'inventory',
            )
            ->listen(
                'customer.created',
                RegistryRecordingListener::class,
                'crm',
            );

        self::assertSame(
            [
                'customer.created' => [
                    RegistryRecordingListener::class,
                ],
                'inventory.low' => [
                    RegistrySecondaryListener::class,
                ],
            ],
            $registry->all(),
        );
    }

    public function test_it_can_be_cleared(): void
    {
        $registry = new ModuleEventRegistry();

        $registry->listen(
            'customer.created',
            RegistryRecordingListener::class,
            'crm',
        );

        $registry->clear();

        self::assertSame(
            [],
            $registry->all(),
        );

        self::assertSame(
            0,
            $registry->count(),
        );

        self::assertFalse(
            $registry->hasListeners('customer.created'),
        );
    }
}

final class RegistryRecordingListener implements ModuleEventListenerContract
{
    public function handle(
        ModuleEventContract $event,
    ): void {
    }
}

final class RegistrySecondaryListener implements ModuleEventListenerContract
{
    public function handle(
        ModuleEventContract $event,
    ): void {
    }
}