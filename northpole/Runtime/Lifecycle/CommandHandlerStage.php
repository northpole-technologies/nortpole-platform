<?php

declare(strict_types=1);

namespace Northpole\Runtime\Lifecycle;

use Northpole\Runtime\Commands\ModuleCommandRegistrar;
use Northpole\Runtime\Contracts\BootStageContract;

final class CommandHandlerStage implements BootStageContract
{
    public function __construct(
        private readonly ModuleCommandRegistrar $registrar,
    ) {
    }

    public function name(): string
    {
        return 'command-handlers';
    }

    public function priority(): int
    {
        return 700;
    }

    public function boot(BootContext $context): void
    {
        $this->registrar->register(
            $context->module(),
        );
    }
}