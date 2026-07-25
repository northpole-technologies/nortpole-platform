<?php

declare(strict_types=1);

namespace Northpole\Runtime\Support;

/**
 * @template TItem of object
 */
abstract class AbstractRuntimeRegistry
{
    /**
     * @var array<string, TItem>
     */
    protected array $items = [];

    /**
     * @param  TItem  $item
     */
    public function add(object $item): static
    {
        $this->items[
            $this->keyFor($item)
        ] = $item;

        return $this;
    }

    /**
     * @param  iterable<int, TItem>  $items
     */
    public function addMany(iterable $items): static
    {
        foreach ($items as $item) {
            $this->add($item);
        }

        return $this;
    }

    /**
     * @return array<int, TItem>
     */
    public function all(): array
    {
        $items = array_values(
            $this->items
        );

        usort(
            $items,
            fn (
                object $first,
                object $second,
            ): int => $this->compare(
                $first,
                $second,
            )
        );

        return $items;
    }

    /**
     * @return TItem|null
     */
    public function get(string $key): ?object
    {
        $normalisedKey = trim($key);

        if ($normalisedKey === '') {
            return null;
        }

        return $this->items[
            $normalisedKey
        ] ?? null;
    }

    public function has(string $key): bool
    {
        $normalisedKey = trim($key);

        if ($normalisedKey === '') {
            return false;
        }

        return isset(
            $this->items[$normalisedKey]
        );
    }

    public function count(): int
    {
        return count(
            $this->items
        );
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function clear(): void
    {
        $this->items = [];
    }

    /**
     * @param  TItem  $item
     */
    abstract protected function keyFor(
        object $item
    ): string;

    /**
     * @param  TItem  $first
     * @param  TItem  $second
     */
    abstract protected function compare(
        object $first,
        object $second,
    ): int;
}
