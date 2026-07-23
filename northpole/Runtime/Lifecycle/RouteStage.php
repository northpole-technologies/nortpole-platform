<?php

namespace Northpole\Runtime\Lifecycle;

use Northpole\Runtime\Contracts\BootStageContract;
use Northpole\Runtime\Support\ApplicationAdapter;

final class RouteStage implements BootStageContract
{
    public function __construct(
        private readonly ApplicationAdapter $application,
    ) {
    }

    public function name(): string
    {
        return 'routes';
    }

    public function priority(): int
    {
        return 200;
    }

    public function boot(BootContext $context): void
    {
        $resources = $context->resources();

        if (! $resources->hasRoutes()) {
            return;
        }

        if ($resources->hasWebRoutes()) {
            $this->application->registerWebRoutes(
                $resources->webRoutesPath()
            );
        }

        if ($resources->hasApiRoutes()) {
            $this->application->registerApiRoutes(
                $resources->apiRoutesPath()
            );
        }
    }
}