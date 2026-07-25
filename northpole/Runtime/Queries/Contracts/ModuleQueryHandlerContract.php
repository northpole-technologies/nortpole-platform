<?php

declare(strict_types=1);

namespace Northpole\Runtime\Queries\Contracts;

interface ModuleQueryHandlerContract
{
    public function handle(
        ModuleQueryContract $query,
    ): mixed;
}
