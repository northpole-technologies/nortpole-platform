<?php

declare(strict_types=1);

namespace Northpole\Runtime\Notifications;

use Closure;
use LogicException;
use Northpole\Runtime\Notifications\Contracts\ModuleNotificationContract;
use Northpole\Runtime\Notifications\Contracts\ModuleNotificationHandlerContract;

final class ModuleNotificationBus
{
    /**
     * @param  Closure(class-string): object  $handlerResolver
     */
    public function __construct(
        private readonly ModuleNotificationRegistry $registry,
        private readonly Closure $handlerResolver,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public function send(
        string $notificationName,
        string $sourceModule,
        array $payload = [],
        array $metadata = [],
    ): mixed {
        return $this->dispatch(
            new ModuleNotification(
                name: $notificationName,
                sourceModule: $sourceModule,
                payload: $payload,
                metadata: $metadata,
            ),
        );
    }

    public function dispatch(
        ModuleNotificationContract $notification,
    ): mixed {
        $definition = $this->registry->find(
            $notification->sourceModule(),
            $notification->name(),
        );

        if ($definition === null) {
            throw new LogicException(
                sprintf(
                    'No handler is registered for module notification [%s:%s].',
                    $notification->sourceModule(),
                    $notification->name(),
                ),
            );
        }

        $handler = ($this->handlerResolver)(
            $definition->class,
        );

        if (
            ! $handler
            instanceof ModuleNotificationHandlerContract
        ) {
            throw new LogicException(
                sprintf(
                    'Resolved module notification handler [%s] must implement [%s].',
                    $definition->class,
                    ModuleNotificationHandlerContract::class,
                ),
            );
        }

        return $handler->handle(
            $notification,
        );
    }

    public function registry(): ModuleNotificationRegistry
    {
        return $this->registry;
    }
}