<?php

declare(strict_types=1);

namespace Northpole\Lifecycle;

use Illuminate\Database\ConnectionInterface;
use Northpole\Lifecycle\Contracts\LifecycleStageContract;

final class LifecyclePipeline
{
    public function __construct(
        private readonly LifecycleStageRegistry $registry,
        private readonly ConnectionInterface $database,
    ) {
    }

    public function add(
        LifecycleStageContract $stage
    ): self {
        $this->registry->register($stage);

        return $this;
    }

    /**
     * @param iterable<int, LifecycleStageContract> $stages
     */
    public function addMany(
        iterable $stages
    ): self {
        $this->registry->registerMany($stages);

        return $this;
    }

    public function run(
        LifecycleContext $context
    ): LifecycleContext {
        return $this->database->transaction(
            function () use (
                $context
            ): LifecycleContext {
                foreach (
                    $this->registry->sorted()
                    as $stage
                ) {
                    if (! $stage->supports($context)) {
                        continue;
                    }

                    $stage->handle($context);
                }

                return $context;
            }
        );
    }

    /**
     * @return array<int, LifecycleStageContract>
     */
    public function stages(): array
    {
        return $this->registry->sorted();
    }

    public function count(): int
    {
        return $this->registry->count();
    }

    public function registry(): LifecycleStageRegistry
    {
        return $this->registry;
    }
}