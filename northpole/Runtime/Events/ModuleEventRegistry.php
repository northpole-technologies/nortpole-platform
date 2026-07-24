<?php

declare(strict_types=1);

namespace Northpole\Runtime\Events;

use InvalidArgumentException;
use Northpole\Runtime\Events\Contracts\ModuleEventListenerContract;

final class ModuleEventRegistry
{
    /**
     * @var array<string, array<string, class-string<ModuleEventListenerContract>>>
     */
    private array $listeners = [];

    /**
     * @param class-string<ModuleEventListenerContract> $listener
     */
    public function listen(
        string $eventName,
        string $listener,
        string $module = 'platform',
    ): self {
        $eventName = trim($eventName);
        $module = trim($module);
        $listener = trim($listener);

        if ($eventName === '') {
            throw new InvalidArgumentException(
                'A module event name cannot be empty.'
            );
        }

        if ($module === '') {
            throw new InvalidArgumentException(
                'A module event listener owner cannot be empty.'
            );
        }

        if ($listener === '') {
            throw new InvalidArgumentException(
                'A module event listener class cannot be empty.'
            );
        }

        $key = $this->listenerKey(
            $module,
            $listener,
        );

        if (isset($this->listeners[$eventName][$key])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Listener [%s] from module [%s] is already registered for event [%s].',
                    $listener,
                    $module,
                    $eventName,
                )
            );
        }

        $this->listeners[$eventName][$key] = $listener;

        return $this;
    }

    /**
     * @return array<int, class-string<ModuleEventListenerContract>>
     */
    public function listeners(
        string $eventName,
    ): array {
        $eventName = trim($eventName);

        if ($eventName === '') {
            return [];
        }

        return array_values(
            $this->listeners[$eventName] ?? [],
        );
    }

    public function hasListeners(
        string $eventName,
    ): bool {
        return $this->listeners($eventName) !== [];
    }

    /**
     * @return array<string, array<int, class-string<ModuleEventListenerContract>>>
     */
    public function all(): array
    {
        $listeners = [];

        foreach ($this->listeners as $eventName => $registered) {
            $listeners[$eventName] = array_values(
                $registered,
            );
        }

        ksort($listeners);

        return $listeners;
    }

    public function count(
        ?string $eventName = null,
    ): int {
        if ($eventName !== null) {
            return count(
                $this->listeners($eventName),
            );
        }

        $count = 0;

        foreach ($this->listeners as $listeners) {
            $count += count($listeners);
        }

        return $count;
    }

    public function clear(): self
    {
        $this->listeners = [];

        return $this;
    }

    private function listenerKey(
        string $module,
        string $listener,
    ): string {
        return strtolower(
            $module . ':' . $listener,
        );
    }
}