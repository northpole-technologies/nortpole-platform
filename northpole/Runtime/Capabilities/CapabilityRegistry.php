<?php

declare(strict_types=1);

namespace Northpole\Runtime\Capabilities;

final class CapabilityRegistry
{
    /**
     * @var array<string, Capability>
     */
    private array $capabilities = [];

    public function add(Capability $capability): self
    {
        $this->capabilities[$capability->key()] = $capability;

        return $this;
    }

    /**
     * @param iterable<int, Capability> $capabilities
     */
    public function addMany(iterable $capabilities): self
    {
        foreach ($capabilities as $capability) {
            $this->add($capability);
        }

        return $this;
    }

    /**
     * @return array<int, Capability>
     */
    public function all(): array
    {
        return $this->sort(
            array_values($this->capabilities)
        );
    }

    /**
     * @return array<int, Capability>
     */
    public function forModule(string $moduleSlug): array
    {
        $capabilities = array_filter(
            $this->capabilities,
            static fn (Capability $capability): bool =>
                $capability->moduleSlug() === $moduleSlug
        );

        return $this->sort(
            array_values($capabilities)
        );
    }

    /**
     * @return array<int, Capability>
     */
    public function named(string $name): array
    {
        $capabilities = array_filter(
            $this->capabilities,
            static fn (Capability $capability): bool =>
                $capability->name() === $name
        );

        return $this->sort(
            array_values($capabilities)
        );
    }

    public function has(
        string $moduleSlug,
        string $name,
    ): bool {
        return isset(
            $this->capabilities[
                $this->key($moduleSlug, $name)
            ]
        );
    }

    public function get(
        string $moduleSlug,
        string $name,
    ): ?Capability {
        return $this->capabilities[
            $this->key($moduleSlug, $name)
        ] ?? null;
    }

    public function count(): int
    {
        return count($this->capabilities);
    }

    public function clear(): void
    {
        $this->capabilities = [];
    }

    private function key(
        string $moduleSlug,
        string $name,
    ): string {
        return implode(
            ':',
            [
                $moduleSlug,
                $name,
            ]
        );
    }

    /**
     * @param array<int, Capability> $capabilities
     *
     * @return array<int, Capability>
     */
    private function sort(array $capabilities): array
    {
        usort(
            $capabilities,
            static function (
                Capability $first,
                Capability $second,
            ): int {
                $moduleComparison = strcmp(
                    $first->moduleSlug(),
                    $second->moduleSlug()
                );

                if ($moduleComparison !== 0) {
                    return $moduleComparison;
                }

                return strcmp(
                    $first->name(),
                    $second->name()
                );
            }
        );

        return $capabilities;
    }
}