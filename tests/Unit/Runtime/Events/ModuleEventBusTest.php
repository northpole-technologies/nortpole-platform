<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Events;

use DateTimeImmutable;
use LogicException;
use Northpole\Runtime\Events\Contracts\ModuleEventContract;
use Northpole\Runtime\Events\Contracts\ModuleEventListenerContract;
use Northpole\Runtime\Events\ModuleEvent;
use Northpole\Runtime\Events\ModuleEventBus;
use Northpole\Runtime\Events\ModuleEventRegistry;
use PHPUnit\Framework\TestCase;

final class ModuleEventBusTest extends TestCase
{
    public function test_it_dispatches_an_event_to_registered_listeners(): void
    {
        RecordingModuleEventListener::reset();

        $registry = new ModuleEventRegistry;

        $registry->listen(
            'customer.created',
            RecordingModuleEventListener::class,
            'crm',
        );

        $bus = new ModuleEventBus(
            $registry,
            static fn (string $listener): object => new $listener,
        );

        $event = new ModuleEvent(
            name: 'customer.created',
            sourceModule: 'crm',
            payload: [
                'customerId' => 123,
            ],
        );

        $returned = $bus->dispatch($event);

        self::assertSame(
            $event,
            $returned,
        );

        self::assertCount(
            1,
            RecordingModuleEventListener::$events,
        );

        self::assertSame(
            123,
            RecordingModuleEventListener::$events[0]
                ->payload()['customerId'],
        );
    }

    public function test_it_dispatches_listeners_in_registration_order(): void
    {
        OrderedModuleEventListener::reset();

        $registry = new ModuleEventRegistry;

        $registry
            ->listen(
                'customer.created',
                FirstOrderedListener::class,
                'crm',
            )
            ->listen(
                'customer.created',
                SecondOrderedListener::class,
                'inventory',
            );

        $bus = new ModuleEventBus(
            $registry,
            static fn (string $listener): object => new $listener,
        );

        $bus->publish(
            eventName: 'customer.created',
            sourceModule: 'crm',
        );

        self::assertSame(
            ['first', 'second'],
            OrderedModuleEventListener::$order,
        );
    }

    public function test_publish_creates_and_dispatches_a_module_event(): void
    {
        RecordingModuleEventListener::reset();

        $registry = new ModuleEventRegistry;

        $registry->listen(
            'inventory.low',
            RecordingModuleEventListener::class,
            'inventory',
        );

        $bus = new ModuleEventBus(
            $registry,
            static fn (string $listener): object => new $listener,
        );

        $event = $bus->publish(
            eventName: 'inventory.low',
            sourceModule: 'inventory',
            payload: [
                'productId' => 88,
                'remaining' => 2,
            ],
            metadata: [
                'tenantId' => 10,
            ],
        );

        self::assertSame(
            'inventory.low',
            $event->name(),
        );

        self::assertSame(
            'inventory',
            $event->sourceModule(),
        );

        self::assertSame(
            88,
            $event->payload()['productId'],
        );

        self::assertSame(
            10,
            $event->metadata()['tenantId'],
        );
    }

    public function test_it_preserves_the_event_occurrence_time(): void
    {
        $occurredAt = new DateTimeImmutable(
            '2026-07-24 12:00:00',
        );

        $event = new ModuleEvent(
            name: 'customer.created',
            sourceModule: 'crm',
            occurredAt: $occurredAt,
        );

        self::assertSame(
            $occurredAt,
            $event->occurredAt(),
        );

        self::assertSame(
            $event->occurredAt(),
            $event->occurredAt(),
        );
    }

    public function test_it_assigns_one_immutable_occurrence_time_automatically(): void
    {
        $event = new ModuleEvent(
            name: 'customer.created',
            sourceModule: 'crm',
        );

        $firstRead = $event->occurredAt();

        usleep(1000);

        $secondRead = $event->occurredAt();

        self::assertSame(
            $firstRead,
            $secondRead,
        );
    }

    public function test_it_allows_events_without_listeners(): void
    {
        $bus = new ModuleEventBus(
            new ModuleEventRegistry,
            static fn (string $listener): object => new $listener,
        );

        $event = $bus->publish(
            eventName: 'customer.updated',
            sourceModule: 'crm',
        );

        self::assertSame(
            'customer.updated',
            $event->name(),
        );
    }

    public function test_it_rejects_a_resolved_listener_with_the_wrong_contract(): void
    {
        $registry = new ModuleEventRegistry;

        $registry->listen(
            'customer.created',
            InvalidModuleEventListener::class,
            'crm',
        );

        $bus = new ModuleEventBus(
            $registry,
            static fn (): object => new \stdClass,
        );

        $this->expectException(
            LogicException::class,
        );

        $bus->publish(
            eventName: 'customer.created',
            sourceModule: 'crm',
        );
    }
}

final class RecordingModuleEventListener implements ModuleEventListenerContract
{
    /**
     * @var array<int, ModuleEventContract>
     */
    public static array $events = [];

    public static function reset(): void
    {
        self::$events = [];
    }

    public function handle(
        ModuleEventContract $event,
    ): void {
        self::$events[] = $event;
    }
}

abstract class OrderedModuleEventListener implements ModuleEventListenerContract
{
    /**
     * @var array<int, string>
     */
    public static array $order = [];

    public static function reset(): void
    {
        self::$order = [];
    }
}

final class FirstOrderedListener extends OrderedModuleEventListener
{
    public function handle(
        ModuleEventContract $event,
    ): void {
        self::$order[] = 'first';
    }
}

final class SecondOrderedListener extends OrderedModuleEventListener
{
    public function handle(
        ModuleEventContract $event,
    ): void {
        self::$order[] = 'second';
    }
}

/**
 * This class intentionally provides a valid registry entry while the
 * test resolver returns an object that violates the listener contract.
 */
final class InvalidModuleEventListener implements ModuleEventListenerContract
{
    public function handle(
        ModuleEventContract $event,
    ): void {}
}
