<?php

declare(strict_types=1);

namespace Northpole\Runtime\Events;

use Closure;
use LogicException;
use Northpole\Runtime\Events\Contracts\ModuleEventContract;
use Northpole\Runtime\Events\Contracts\ModuleEventListenerContract;

final class ModuleEventBus
{
    /**
     * @param Closure(class-string): object $listenerResolver
     */
    public function __construct(
        private readonly ModuleEventRegistry $registry,
        private readonly Closure $listenerResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $metadata
     */
    public function publish(
        string $eventName,
        string $sourceModule,
        array $payload = [],
        array $metadata = [],
    ): ModuleEventContract {
        return $this->dispatch(
            new ModuleEvent(
                name: $eventName,
                sourceModule: $sourceModule,
                payload: $payload,
                metadata: $metadata,
            ),
        );
    }

    public function dispatch(
        ModuleEventContract $event,
    ): ModuleEventContract {
        foreach (
            $this->registry->listeners($event->name())
            as $listenerClass
        ) {
            $listener = ($this->listenerResolver)(
                $listenerClass,
            );

            if (
                ! $listener
                instanceof ModuleEventListenerContract
            ) {
                throw new LogicException(
                    sprintf(
                        'Resolved module event listener [%s] must implement [%s].',
                        $listenerClass,
                        ModuleEventListenerContract::class,
                    )
                );
            }

            $listener->handle($event);
        }

        return $event;
    }

    public function registry(): ModuleEventRegistry
    {
        return $this->registry;
    }
}