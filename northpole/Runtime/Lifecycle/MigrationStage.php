<?php

namespace Northpole\Runtime\Lifecycle;

use Northpole\Runtime\Contracts\BootStageContract;
use Northpole\Runtime\Support\ApplicationAdapter;

final class MigrationStage implements BootStageContract
{
    public function __construct(
        private readonly ApplicationAdapter $application,
    ) {}

    public function name(): string
    {
        return 'migrations';
    }

    public function priority(): int
    {
        return 400;
    }

    public function boot(BootContext $context): void
    {
        $resources = $context->resources();

        if (! $resources->hasMigrations()) {
            return;
        }

        $this->application->registerMigrations(
            $resources->migrationsPath()
        );
    }
}
