<?php

declare(strict_types=1);

namespace Northpole\Runtime\Lifecycle;

use Northpole\Runtime\Agents\ModuleAgentRegistrar;
use Northpole\Runtime\Contracts\BootStageContract;

final class AgentHandlerStage implements BootStageContract
{
    public function __construct(
        private readonly ModuleAgentRegistrar $registrar,
    ) {}

    public function name(): string
    {
        return 'agent-handlers';
    }

    public function priority(): int
    {
        return 720;
    }

    public function boot(BootContext $context): void
    {
        $this->registrar->register(
            $context->module(),
        );
    }
}