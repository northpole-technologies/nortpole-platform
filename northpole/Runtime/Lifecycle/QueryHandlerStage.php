<?php

declare(strict_types=1);

namespace Northpole\Runtime\Lifecycle;

use Northpole\Runtime\Contracts\BootStageContract;
use Northpole\Runtime\Queries\ModuleQueryRegistrar;

final class QueryHandlerStage implements BootStageContract
{
    public function __construct(
        private readonly ModuleQueryRegistrar $registrar,
    ) {
    }

    public function name(): string
    {
        return 'query-handlers';
    }

    public function priority(): int
    {
        return 710;
    }

    public function boot(BootContext $context): void
    {
        $this->registrar->register(
            $context->module(),
        );
    }
}
