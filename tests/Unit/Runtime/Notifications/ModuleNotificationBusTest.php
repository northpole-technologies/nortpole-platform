<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Notifications;

use DateTimeImmutable;
use InvalidArgumentException;
use LogicException;
use Northpole\Runtime\Notifications\Contracts\ModuleNotificationContract;
use Northpole\Runtime\Notifications\Contracts\ModuleNotificationHandlerContract;
use Northpole\Runtime\Notifications\ModuleNotification;
use Northpole\Runtime\Notifications\ModuleNotificationBus;
use Northpole\Runtime\Notifications\ModuleNotificationRegistry;
use Northpole\Runtime\Notifications\NotificationDefinition;
use PHPUnit\Framework\TestCase;

final class ModuleNotificationBusTest extends TestCase
{
    public function test_it_dispatches_a_registered_notification_handler(): void
    {
        RecordingNotificationHandler::reset();

        $registry = new ModuleNotificationRegistry;

        $registry->register(
            new NotificationDefinition(
                module: 'crm',
                name: 'customer-created',
                class: RecordingNotificationHandler::class,
                channels: [
                    'database',
                    'mail',
                ],
            ),
        );

        $bus = new ModuleNotificationBus(
            $registry,
            static fn (string $handler): object => new $handler,
        );

        $notification = new ModuleNotification(
            name: 'customer-created',
            sourceModule: 'crm',
            payload: [
                'customerId' => 501,
            ],
        );

        $result = $bus->dispatch(
            $notification,
        );

        self::assertSame(
            'sent',
            $result,
        );

        self::assertCount(
            1,
            RecordingNotificationHandler::$notifications,
        );

        self::assertSame(
            $notification,
            RecordingNotificationHandler::$notifications[0],
        );

        self::assertSame(
            501,
            RecordingNotificationHandler::$notifications[0]
                ->payload()['customerId'],
        );
    }

    public function test_send_creates_and_dispatches_a_notification(): void
    {
        RecordingNotificationHandler::reset();

        $registry = new ModuleNotificationRegistry;

        $registry->register(
            new NotificationDefinition(
                module: 'inventory',
                name: 'stock-low',
                class: RecordingNotificationHandler::class,
                channels: [
                    'database',
                ],
            ),
        );

        $bus = new ModuleNotificationBus(
            $registry,
            static fn (string $handler): object => new $handler,
        );

        $result = $bus->send(
            notificationName: 'stock-low',
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
            'sent',
            $result,
        );

        self::assertSame(
            'stock-low',
            RecordingNotificationHandler::$notifications[0]
                ->name(),
        );

        self::assertSame(
            'inventory',
            RecordingNotificationHandler::$notifications[0]
                ->sourceModule(),
        );

        self::assertSame(
            88,
            RecordingNotificationHandler::$notifications[0]
                ->payload()['productId'],
        );

        self::assertSame(
            10,
            RecordingNotificationHandler::$notifications[0]
                ->metadata()['tenantId'],
        );
    }

    public function test_it_rejects_an_unregistered_notification(): void
    {
        $bus = new ModuleNotificationBus(
            new ModuleNotificationRegistry,
            static fn (string $handler): object => new $handler,
        );

        $this->expectException(
            LogicException::class,
        );

        $this->expectExceptionMessage(
            'No handler is registered for module notification [crm:customer-deleted].',
        );

        $bus->send(
            notificationName: 'customer-deleted',
            sourceModule: 'crm',
        );
    }

    public function test_it_rejects_a_resolved_handler_with_the_wrong_contract(): void
    {
        $registry = new ModuleNotificationRegistry;

        $registry->register(
            new NotificationDefinition(
                module: 'crm',
                name: 'customer-created',
                class: InvalidNotificationHandler::class,
                channels: [
                    'mail',
                ],
            ),
        );

        $bus = new ModuleNotificationBus(
            $registry,
            static fn (): object => new \stdClass,
        );

        $this->expectException(
            LogicException::class,
        );

        $this->expectExceptionMessage(
            sprintf(
                'Resolved module notification handler [%s] must implement [%s].',
                InvalidNotificationHandler::class,
                ModuleNotificationHandlerContract::class,
            ),
        );

        $bus->send(
            notificationName: 'customer-created',
            sourceModule: 'crm',
        );
    }

    public function test_it_preserves_notification_data(): void
    {
        $sentAt = new DateTimeImmutable(
            '2026-07-26 08:30:00',
        );

        $notification = new ModuleNotification(
            name: 'customer-created',
            sourceModule: 'crm',
            payload: [
                'customerId' => 501,
            ],
            metadata: [
                'tenantId' => 10,
                'requestedBy' => 25,
            ],
            sentAt: $sentAt,
        );

        self::assertSame(
            'customer-created',
            $notification->name(),
        );

        self::assertSame(
            'crm',
            $notification->sourceModule(),
        );

        self::assertSame(
            501,
            $notification->payload()['customerId'],
        );

        self::assertSame(
            10,
            $notification->metadata()['tenantId'],
        );

        self::assertSame(
            25,
            $notification->metadata()['requestedBy'],
        );

        self::assertSame(
            $sentAt,
            $notification->sentAt(),
        );
    }

    public function test_it_assigns_one_immutable_sent_time_automatically(): void
    {
        $notification = new ModuleNotification(
            name: 'customer-created',
            sourceModule: 'crm',
        );

        $firstRead = $notification->sentAt();

        usleep(1000);

        $secondRead = $notification->sentAt();

        self::assertSame(
            $firstRead,
            $secondRead,
        );
    }

    public function test_it_rejects_an_empty_notification_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A module notification name cannot be empty.',
        );

        new ModuleNotification(
            name: '   ',
            sourceModule: 'crm',
        );
    }

    public function test_it_rejects_an_empty_source_module(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'A module notification source module cannot be empty.',
        );

        new ModuleNotification(
            name: 'customer-created',
            sourceModule: '   ',
        );
    }

    public function test_it_exposes_the_notification_registry(): void
    {
        $registry = new ModuleNotificationRegistry;

        $bus = new ModuleNotificationBus(
            $registry,
            static fn (string $handler): object => new $handler,
        );

        self::assertSame(
            $registry,
            $bus->registry(),
        );
    }
}

final class RecordingNotificationHandler implements ModuleNotificationHandlerContract
{
    /**
     * @var array<int, ModuleNotificationContract>
     */
    public static array $notifications = [];

    public static function reset(): void
    {
        self::$notifications = [];
    }

    public function handle(
        ModuleNotificationContract $notification,
    ): string {
        self::$notifications[] = $notification;

        return 'sent';
    }
}

/**
 * This class provides a valid registry class name while the test resolver
 * deliberately returns an object that violates the handler contract.
 */
final class InvalidNotificationHandler implements ModuleNotificationHandlerContract
{
    public function handle(
        ModuleNotificationContract $notification,
    ): mixed {
        return null;
    }
}