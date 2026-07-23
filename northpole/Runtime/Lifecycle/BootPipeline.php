<?php

namespace Northpole\Runtime\Lifecycle;

use Northpole\Runtime\Contracts\BootStageContract;
use Northpole\Runtime\Runtime;

final class BootPipeline
{
    /**
     * @var array<int, BootStageContract>
     */
    private array $stages = [];

    public function add(BootStageContract $stage): self
    {
        $this->stages[] = $stage;

        return $this;
    }

    /**
     * @param iterable<int, BootStageContract> $stages
     */
    public function addMany(iterable $stages): self
    {
        foreach ($stages as $stage) {
            $this->add($stage);
        }

        return $this;
    }

    public function boot(Runtime $runtime): void
    {
        $stages = $this->sortedStages();
        $modules = $runtime->enabledModules();

        foreach ($stages as $stage) {
            foreach ($modules as $module) {
                $stage->boot(
                    new BootContext(
                        $runtime,
                        $module
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
        return $this->sortedStages();
    }

    public function count(): int
    {
        return count($this->stages);
    }

    /**
     * @return array<int, BootStageContract>
     */
    private function sortedStages(): array
    {
        $stages = $this->stages;

        usort(
            $stages,
            static fn (
                BootStageContract $first,
                BootStageContract $second
            ): int => $first->priority() <=> $second->priority()
        );

        return $stages;
    }
}