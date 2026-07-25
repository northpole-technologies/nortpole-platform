<?php

namespace Northpole\Runtime\Lifecycle;

use Northpole\Runtime\Contracts\BootStageContract;
use Northpole\Runtime\Support\ApplicationAdapter;

final class ProviderStage implements BootStageContract
{
    public function __construct(
        private readonly ApplicationAdapter $application,
    ) {}

    public function name(): string
    {
        return 'providers';
    }

    public function priority(): int
    {
        return 100;
    }

    public function boot(BootContext $context): void
    {
        $resources = $context->resources();

        if (! $resources->hasProvider()) {
            return;
        }

        $this->application->registerProvider(
            $resources->provider()
        );
    }
}
