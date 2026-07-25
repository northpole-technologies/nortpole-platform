<?php

declare(strict_types=1);

namespace Northpole\Runtime\Commands\Contracts;

interface ModuleCommandHandlerContract
{
    public function handle(
        ModuleCommandContract $command,
    ): mixed;
}