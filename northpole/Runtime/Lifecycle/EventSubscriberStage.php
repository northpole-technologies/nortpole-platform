<?php

declare(strict_types=1);

namespace Northpole\Runtime\Lifecycle;

use Northpole\Runtime\Contracts\BootStageContract;
use Northpole\Runtime\Events\ModuleEventRegistrar;

final class EventSubscriberStage implements BootStageContract
{
    public function __construct(
        private readonly ModuleEventRegistrar $registrar,
    ) {
    }

    public function name(): string
    {
        return 'event-subscribers';
    }

    public function priority(): int
    {
        return 650;
    }

    public function boot(BootContext $context): void
    {
        $this->registrar->register(
            $context->module(),
        );
    }
}