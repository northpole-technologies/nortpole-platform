<?php

declare(strict_types=1);

namespace Northpole\Runtime\Manifest;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\ModuleManifestContract;

final class ModuleManifest implements ModuleManifestContract
{
    public function __construct(
        private readonly array $data,
        private readonly string $path,
        private readonly string $manifestPath,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        foreach (['name', 'slug', 'version'] as $required) {
            if (! isset($this->data[$required])) {
                throw new InvalidArgumentException(
                    "Module manifest missing required field [{$required}]"
                );
            }
        }

        $this->validateDependencies();
        $this->validateEvents();
        $this->validateCommands();
    }

    private function validateDependencies(): void
    {
        if (
            isset($this->data['dependencies'])
            && ! is_array($this->data['dependencies'])
        ) {
            throw new InvalidArgumentException(
                'Module manifest field [dependencies] must be an array'
            );
        }

        $dependencies = $this->data['dependencies'] ?? [];

        if (array_is_list($dependencies)) {
            $this->validateLegacyDependencies(
                $dependencies
            );

            return;
        }

        $this->validateVersionedDependencies(
            $dependencies
        );
    }

    /**
     * @param array<int, mixed> $dependencies
     */
    private function validateLegacyDependencies(
        array $dependencies
    ): void {
        foreach ($dependencies as $dependency) {
            if (
                ! is_string($dependency)
                || trim($dependency) === ''
            ) {
                throw new InvalidArgumentException(
                    'Module manifest dependencies must contain non-empty strings'
                );
            }
        }
    }

    /**
     * @param array<array-key, mixed> $dependencies
     */
    private function validateVersionedDependencies(
        array $dependencies
    ): void {
        foreach (
            $dependencies as $dependency => $constraint
        ) {
            if (
                ! is_string($dependency)
                || trim($dependency) === ''
            ) {
                throw new InvalidArgumentException(
                    'Module manifest dependency names must be non-empty strings'
                );
            }

            if (
                ! is_string($constraint)
                || trim($constraint) === ''
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Module manifest dependency [%s] must have a non-empty version constraint',
                        $dependency
                    )
                );
            }
        }
    }

    private function validateEvents(): void
    {
        if (! isset($this->data['events'])) {
            return;
        }

        if (! is_array($this->data['events'])) {
            throw new InvalidArgumentException(
                'Module manifest field [events] must be an object'
            );
        }

        $events = $this->data['events'];

        $this->validatePublishedEvents(
            $events['publishes'] ?? []
        );

        $this->validateEventSubscribers(
            $events['subscribes'] ?? []
        );
    }

    private function validatePublishedEvents(
        mixed $publishedEvents
    ): void {
        if (
            ! is_array($publishedEvents)
            || ! array_is_list($publishedEvents)
        ) {
            throw new InvalidArgumentException(
                'Module manifest field [events.publishes] must be a list'
            );
        }

        foreach ($publishedEvents as $eventName) {
            if (
                ! is_string($eventName)
                || trim($eventName) === ''
            ) {
                throw new InvalidArgumentException(
                    'Module manifest field [events.publishes] must contain non-empty strings'
                );
            }
        }
    }

    private function validateEventSubscribers(
        mixed $subscribers
    ): void {
        if (! is_array($subscribers)) {
            throw new InvalidArgumentException(
                'Module manifest field [events.subscribes] must be an object'
            );
        }

        foreach ($subscribers as $eventName => $listeners) {
            if (
                ! is_string($eventName)
                || trim($eventName) === ''
            ) {
                throw new InvalidArgumentException(
                    'Module manifest subscribed event names must be non-empty strings'
                );
            }

            if (
                ! is_array($listeners)
                || ! array_is_list($listeners)
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Module manifest event subscription [%s] must contain a list of listener classes',
                        $eventName
                    )
                );
            }

            foreach ($listeners as $listenerClass) {
                if (
                    ! is_string($listenerClass)
                    || trim($listenerClass) === ''
                ) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Module manifest event subscription [%s] must contain non-empty listener class names',
                            $eventName
                        )
                    );
                }
            }
        }
    }

    private function validateCommands(): void
    {
        if (! isset($this->data['commands'])) {
            return;
        }

        if (! is_array($this->data['commands'])) {
            throw new InvalidArgumentException(
                'Module manifest field [commands] must be an object'
            );
        }

        $this->validateHandledCommands(
            $this->data['commands']['handles'] ?? []
        );
    }

    private function validateHandledCommands(
        mixed $handledCommands
    ): void {
        if (! is_array($handledCommands)) {
            throw new InvalidArgumentException(
                'Module manifest field [commands.handles] must be an object'
            );
        }

        foreach (
            $handledCommands as $commandName => $handlerClass
        ) {
            if (
                ! is_string($commandName)
                || trim($commandName) === ''
            ) {
                throw new InvalidArgumentException(
                    'Module manifest handled command names must be non-empty strings'
                );
            }

            if (
                ! is_string($handlerClass)
                || trim($handlerClass) === ''
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Module manifest command [%s] must have a non-empty handler class',
                        $commandName
                    )
                );
            }
        }
    }
    public function name(): string
    {
        return (string) $this->data['name'];
    }

    public function slug(): string
    {
        return (string) $this->data['slug'];
    }

    public function version(): string
    {
        return (string) $this->data['version'];
    }

    public function description(): ?string
    {
        return $this->data['description'] ?? null;
    }

    public function provider(): ?string
    {
        return $this->data['provider'] ?? null;
    }

    public function enabled(): bool
    {
        return (bool) ($this->data['enabled'] ?? false);
    }

    public function path(): string
    {
        return $this->path;
    }

    public function manifestPath(): string
    {
        return $this->manifestPath;
    }

    /**
     * @return array<int, string>
     */
    public function dependencies(): array
    {
        return array_keys(
            $this->dependencyConstraints()
        );
    }

    /**
     * @return array<string, string>
     */
    public function dependencyConstraints(): array
    {
        $dependencies = $this->data['dependencies'] ?? [];

        if (array_is_list($dependencies)) {
            $constraints = [];

            foreach ($dependencies as $dependency) {
                $constraints[trim($dependency)] = '*';
            }

            return $constraints;
        }

        $constraints = [];

        foreach (
            $dependencies as $dependency => $constraint
        ) {
            $constraints[trim($dependency)] = trim($constraint);
        }

        return $constraints;
    }

    /**
     * @return array<string, string>
     */
    public function routes(): array
    {
        return $this->data['routes'] ?? [];
    }

    public function viewsPath(): ?string
    {
        return $this->data['views'] ?? null;
    }

    public function migrationsPath(): ?string
    {
        return $this->data['migrations'] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function configuration(): array
    {
        return $this->data['config'] ?? [];
    }

    /**
     * @return array<int, string>
     */
    public function permissions(): array
    {
        return $this->data['permissions'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function navigation(): array
    {
        return $this->data['navigation'] ?? [];
    }

    /**
     * @return array<int, string>
     */
    public function capabilities(): array
    {
        return $this->data['capabilities'] ?? [];
    }

    /**
     * @return array<int, string>
     */
    public function publishedEvents(): array
    {
        $publishedEvents = $this->data['events']['publishes'] ?? [];

        $normalisedEvents = [];

        foreach ($publishedEvents as $eventName) {
            $normalisedEvents[trim($eventName)] = true;
        }

        return array_keys($normalisedEvents);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function eventSubscribers(): array
    {
        $subscribers = $this->data['events']['subscribes'] ?? [];

        $normalisedSubscribers = [];

        foreach ($subscribers as $eventName => $listeners) {
            $normalisedEventName = trim($eventName);
            $normalisedListeners = [];

            foreach ($listeners as $listenerClass) {
                $normalisedListeners[trim($listenerClass)] = true;
            }

            $normalisedSubscribers[$normalisedEventName] = array_keys(
                $normalisedListeners
            );
        }

        return $normalisedSubscribers;
    }

    /**
     * @return array<string, string>
     */
    public function handledCommands(): array
    {
        $handledCommands = $this->data['commands']['handles'] ?? [];

        $normalisedCommands = [];

        foreach (
            $handledCommands as $commandName => $handlerClass
        ) {
            $normalisedCommands[trim($commandName)] = trim(
                $handlerClass
            );
        }

        return $normalisedCommands;
    }
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
