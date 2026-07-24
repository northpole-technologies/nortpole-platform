<?php

declare(strict_types=1);

namespace Northpole\Runtime\Events;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\ModuleManifestContract;

final class ModuleEventRegistrar
{
    public function __construct(
        private readonly ModuleEventRegistry $registry,
    ) {
    }

    public function register(
        ModuleManifestContract $module,
    ): void {
        $moduleSlug = trim($module->slug());

        if ($moduleSlug === '') {
            throw new InvalidArgumentException(
                'A module event subscriber owner cannot be empty.'
            );
        }

        foreach (
            $module->eventSubscribers()
            as $eventName => $listenerClasses
        ) {
            $this->registerEventSubscribers(
                moduleSlug: $moduleSlug,
                eventName: $eventName,
                listenerClasses: $listenerClasses,
            );
        }
    }

    /**
     * @param array<int, string> $listenerClasses
     */
    private function registerEventSubscribers(
        string $moduleSlug,
        string $eventName,
        array $listenerClasses,
    ): void {
        $eventName = trim($eventName);

        if ($eventName === '') {
            throw new InvalidArgumentException(
                sprintf(
                    'Subscribed event names for module [%s] must be non-empty strings.',
                    $moduleSlug,
                )
            );
        }

        foreach ($listenerClasses as $listenerClass) {
            if (
                ! is_string($listenerClass)
                || trim($listenerClass) === ''
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Event listeners for module [%s] and event [%s] must be non-empty class names.',
                        $moduleSlug,
                        $eventName,
                    )
                );
            }

            $this->registry->listen(
                eventName: $eventName,
                listener: trim($listenerClass),
                module: $moduleSlug,
            );
        }
    }

    public function registry(): ModuleEventRegistry
    {
        return $this->registry;
    }
}