<?php

declare(strict_types=1);

namespace Northpole\Runtime\Jobs;

use Northpole\Runtime\Contracts\ModuleManifestContract;

final class ModuleScheduledJobRegistrar
{
    public function __construct(
        private readonly ModuleScheduledJobRegistry $registry,
    ) {}

    public function register(
        ModuleManifestContract $module
    ): void {
        foreach (
            $module->scheduledJobs() as $scheduledJob
        ) {
            $this->registry->register(
                new ScheduledJobDefinition(
                    module: $module->slug(),
                    class: $scheduledJob['class'],
                    frequency: $scheduledJob['frequency'],
                    at: $scheduledJob['at'] ?? null,
                    queue: $scheduledJob['queue'] ?? null,
                    withoutOverlapping:
                        $scheduledJob['without_overlapping']
                        ?? false,
                    runInBackground:
                        $scheduledJob['run_in_background']
                        ?? false,
                )
            );
        }
    }

    public function registry(): ModuleScheduledJobRegistry
    {
        return $this->registry;
    }
}