<?php

declare(strict_types=1);

namespace Northpole\Runtime\Lifecycle;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\BootStageContract;

final class StageRegistry
{
    /**
     * @var array<string, BootStageContract>
     */
    private array $stages = [];

    public function register(BootStageContract $stage): self
    {
        $name = trim($stage->name());

        if ($name === '') {
            throw new InvalidArgumentException(
                'Runtime boot stage names cannot be empty.'
            );
        }

        if (isset($this->stages[$name])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Runtime boot stage [%s] is already registered.',
                    $name
                )
            );
        }

        $this->stages[$name] = $stage;

        return $this;
    }

    /**
     * @param  iterable<int, BootStageContract>  $stages
     */
    public function registerMany(iterable $stages): self
    {
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

    public function get(string $name): BootStageContract
    {
        $name = trim($name);

        if (! isset($this->stages[$name])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Runtime boot stage [%s] is not registered.',
                    $name
                )
            );
        }

        return $this->stages[$name];
    }

    /**
     * @return array<int, BootStageContract>
     */
    public function all(): array
    {
        return array_values($this->stages);
    }

    /**
     * @return array<int, BootStageContract>
     */
    public function sorted(): array
    {
        $stages = $this->all();

        usort(
            $stages,
            static fn (
                BootStageContract $first,
                BootStageContract $second
            ): int => $first->priority() <=> $second->priority()
        );

        return $stages;
    }

    public function count(): int
    {
        return count($this->stages);
    }

    public function isEmpty(): bool
    {
        return $this->stages === [];
    }
}
