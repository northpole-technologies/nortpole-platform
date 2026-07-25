<?php

declare(strict_types=1);

namespace Northpole\Runtime\Lifecycle;

use Northpole\Runtime\Contracts\BootStageContract;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistrar;

final class ScheduledJobStage implements BootStageContract
{
    public function __construct(
        private readonly ModuleScheduledJobRegistrar $registrar,
    ) {}

    public function name(): string
    {
        return 'scheduled-jobs';
    }

    public function priority(): int
    {
        return 675;
    }

    public function boot(BootContext $context): void
    {
        $this->registrar->register(
            $context->module(),
        );
    }
}