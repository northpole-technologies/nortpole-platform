<?php

namespace Northpole\Runtime\Lifecycle;

use Northpole\Runtime\Contracts\BootStageContract;
use Northpole\Runtime\Support\ApplicationAdapter;

final class ViewStage implements BootStageContract
{
    public function __construct(
        private readonly ApplicationAdapter $application,
    ) {
    }

    public function name(): string
    {
        return 'views';
    }

    public function priority(): int
    {
        return 300;
    }

    public function boot(BootContext $context): void
    {
        $resources = $context->resources();

        if (! $resources->hasViews()) {
            return;
        }

        $this->application->registerViews(
            $resources->viewsNamespace(),
            $resources->viewsPath()
        );
    }
}