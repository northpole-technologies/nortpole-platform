<?php

declare(strict_types=1);

namespace Northpole\Runtime\Configuration;

use InvalidArgumentException;

final class ModuleConfigurationRegistry
{
    /**
     * @var array<string, ConfigurationDefinition>
     */
    private array $definitions = [];

    public function register(
        ConfigurationDefinition $definition,
    ): void {
        $key = $definition->registryKey();

        if (array_key_exists($key, $this->definitions)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Configuration [%s] is already registered for module [%s].',
                    $definition->key,
                    $definition->module,
                ),
            );
        }

        $this->definitions[$key] = $definition;
    }

    /**
     * @param  iterable<int, ConfigurationDefinition>  $definitions
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
        string $key,
    ): ?ConfigurationDefinition {
        $module = trim($module);
        $key = trim($key);

        if ($module === '' || $key === '') {
            return null;
        }

        return $this->definitions[
            sprintf(
                '%s:%s',
                $module,
                $key,
            )
        ] ?? null;
    }

    /**
     * @return array<int, ConfigurationDefinition>
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
                    ConfigurationDefinition $definition,
                ): bool => $definition->module === $module,
            ),
        );
    }

    /**
     * @return array<int, ConfigurationDefinition>
     */
    public function all(): array
    {
        $definitions = array_values(
            $this->definitions,
        );

        usort(
            $definitions,
            static fn (
                ConfigurationDefinition $left,
                ConfigurationDefinition $right,
            ): int => [
                $left->module,
                $left->key,
            ] <=> [
                $right->module,
                $right->key,
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