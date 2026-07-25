<?php

declare(strict_types=1);

namespace Northpole\Runtime\Notifications;

use InvalidArgumentException;

final class ModuleNotificationRegistry
{
    /**
     * @var array<string, NotificationDefinition>
     */
    private array $definitions = [];

    public function register(
        NotificationDefinition $definition,
    ): void {
        $key = $definition->key();

        if (array_key_exists($key, $this->definitions)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Notification [%s] is already registered for module [%s].',
                    $definition->name,
                    $definition->module,
                ),
            );
        }

        $this->definitions[$key] = $definition;
    }

    /**
     * @param  iterable<int, NotificationDefinition>  $definitions
     */
    public function registerMany(
        iterable $definitions,
    ): void {
        foreach ($definitions as $definition) {
            $this->register(
                $definition,
            );
        }
    }

    public function find(
        string $module,
        string $name,
    ): ?NotificationDefinition {
        $module = trim($module);
        $name = trim($name);

        if ($module === '' || $name === '') {
            return null;
        }

        return $this->definitions[
            sprintf(
                '%s:%s',
                $module,
                $name,
            )
        ] ?? null;
    }

    /**
     * @return array<int, NotificationDefinition>
     */
    public function forModule(
        string $module,
    ): array {
        $module = trim($module);

        if ($module === '') {
            return [];
        }

        return array_values(
            array_filter(
                $this->all(),
                static fn (
                    NotificationDefinition $definition,
                ): bool => $definition->module === $module,
            ),
        );
    }

    /**
     * @return array<int, NotificationDefinition>
     */
    public function all(): array
    {
        $definitions = array_values(
            $this->definitions,
        );

        usort(
            $definitions,
            static fn (
                NotificationDefinition $left,
                NotificationDefinition $right,
            ): int => [
                $left->module,
                $left->name,
                $left->class,
            ] <=> [
                $right->module,
                $right->name,
                $right->class,
            ],
        );

        return $definitions;
    }

    public function count(): int
    {
        return count(
            $this->definitions,
        );
    }

    public function isEmpty(): bool
    {
        return $this->definitions === [];
    }

    public function clear(): void
    {
        $this->definitions = [];
    }
}