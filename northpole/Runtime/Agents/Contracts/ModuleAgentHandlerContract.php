<?php

declare(strict_types=1);

namespace Northpole\Runtime\Agents\Contracts;

interface ModuleAgentHandlerContract
{
    public function handle(
        ModuleAgentContract $agent,
    ): mixed;
}