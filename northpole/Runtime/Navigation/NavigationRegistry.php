<?php

declare(strict_types=1);

namespace Northpole\Runtime\Navigation;

final class NavigationRegistry
{
    /**
     * @var array<string, NavigationItem>
     */
    private array $items = [];

    public function add(NavigationItem $item): self
    {
        $this->items[$item->key()] = $item;

        return $this;
    }

    /**
     * @param iterable<int, NavigationItem> $items
     */
    public function addMany(iterable $items): self
    {
        foreach ($items as $item) {
            $this->add($item);
        }

        return $this;
    }

    /**
     * @return array<int, NavigationItem>
     */
    public function all(): array
    {
        return $this->sort(
            array_values($this->items)
        );
    }

    /**
     * @return array<int, NavigationItem>
     */
    public function forModule(string $moduleSlug): array
    {
        $items = array_filter(
            $this->items,
            static fn (NavigationItem $item): bool =>
                $item->moduleSlug() === $moduleSlug
        );

        return $this->sort(
            array_values($items)
        );
    }

    /**
     * @return array<int, NavigationItem>
     */
    public function forGroup(?string $group): array
    {
        $items = array_filter(
            $this->items,
            static fn (NavigationItem $item): bool =>
                $item->group() === $group
        );

        return $this->sort(
            array_values($items)
        );
    }

    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function clear(): void
    {
        $this->items = [];
    }

    /**
     * @param array<int, NavigationItem> $items
     *
     * @return array<int, NavigationItem>
     */
    private function sort(array $items): array
    {
        usort(
            $items,
            static function (
                NavigationItem $first,
                NavigationItem $second,
            ): int {
                $orderComparison =
                    $first->order() <=> $second->order();

                if ($orderComparison !== 0) {
                    return $orderComparison;
                }

                return strcasecmp(
                    $first->label(),
                    $second->label()
                );
            }
        );

        return $items;
    }
}