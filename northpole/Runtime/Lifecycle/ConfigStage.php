<?php

namespace Northpole\Runtime\Lifecycle;

use Northpole\Runtime\Contracts\BootStageContract;
use Northpole\Runtime\Support\ApplicationAdapter;

final class ConfigStage implements BootStageContract
{
    public function __construct(
        private readonly ApplicationAdapter $application,
    ) {}

    public function name(): string
    {
        return 'config';
    }

    public function priority(): int
    {
        return 50;
    }

    public function boot(BootContext $context): void
    {
        $resources = $context->resources();

        if (! $resources->hasConfiguration()) {
            return;
        }

        foreach ($resources->configuration() as $key => $path) {
            $this->application->mergeConfiguration(
                $key,
                $path
            );
        }
    }
}
