<?php

declare(strict_types=1);

namespace Northpole\Lifecycle;

use InvalidArgumentException;
use Northpole\Lifecycle\Contracts\LifecycleStageContract;

final class LifecycleStageRegistry
{
    /**
     * @var array<string, LifecycleStageContract>
     */
    private array $stages = [];

    public function register(
        LifecycleStageContract $stage
    ): self {
        $name = trim($stage->name());

        if ($name === '') {
            throw new InvalidArgumentException(
                'Module lifecycle stage names cannot be empty.'
            );
        }

        if (isset($this->stages[$name])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module lifecycle stage [%s] is already registered.',
                    $name
                )
            );
        }

        $this->stages[$name] = $stage;

        return $this;
    }

    /**
     * @param  iterable<int, LifecycleStageContract>  $stages
     */
    public function registerMany(
        iterable $stages
    ): self {
        foreach ($stages as $stage) {
            $this->register($stage);
        }

        return $this;
    }

    public function has(string $name): bool
    {
        return isset(
            $this->stages[
                trim($name)
            ]
        );
    }

    public function get(
        string $name
    ): LifecycleStageContract {
        $name = trim($name);

        if (! isset($this->stages[$name])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module lifecycle stage [%s] is not registered.',
                    $name
                )
            );
        }

        return $this->stages[$name];
    }

    /**
     * @return array<int, LifecycleStageContract>
     */
    public function all(): array
    {
        return array_values(
            $this->stages
        );
    }

    /**
     * @return array<int, LifecycleStageContract>
     */
    public function sorted(): array
    {
        $stages = $this->all();

        usort(
            $stages,
            static fn (
                LifecycleStageContract $first,
                LifecycleStageContract $second
            ): int => $first->priority()
                <=> $second->priority()
        );

        return $stages;
    }

    public function count(): int
    {
        return count(
            $this->stages
        );
    }

    public function isEmpty(): bool
    {
        return $this->stages === [];
    }
}
