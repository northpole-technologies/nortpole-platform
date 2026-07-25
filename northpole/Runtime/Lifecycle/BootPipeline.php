<?php

declare(strict_types=1);

namespace Northpole\Runtime\Lifecycle;

use Northpole\Runtime\Contracts\BootStageContract;
use Northpole\Runtime\Runtime;

final class BootPipeline
{
    public function __construct(
        private readonly StageRegistry $registry = new StageRegistry,
    ) {}

    public function add(BootStageContract $stage): self
    {
        $this->registry->register($stage);

        return $this;
    }

    /**
     * @param  iterable<int, BootStageContract>  $stages
     */
    public function addMany(iterable $stages): self
    {
        $this->registry->registerMany($stages);

        return $this;
    }

    public function boot(Runtime $runtime): void
    {
        $modules = $runtime->enabledModules();

        foreach ($this->registry->sorted() as $stage) {
            foreach ($modules as $module) {
                $stage->boot(
                    new BootContext(
                        $runtime,
                        $module,
                    )
                );
            }
        }
    }

    /**
     * @return array<int, BootStageContract>
     */
    public function stages(): array
    {
        return $this->registry->sorted();
    }

    public function count(): int
    {
        return $this->registry->count();
    }

    public function registry(): StageRegistry
    {
        return $this->registry;
    }
}
