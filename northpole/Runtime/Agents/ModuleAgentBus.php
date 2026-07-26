<?php

declare(strict_types=1);

namespace Northpole\Runtime\Agents;

use Closure;
use LogicException;
use Northpole\Runtime\Agents\Contracts\ModuleAgentContract;
use Northpole\Runtime\Agents\Contracts\ModuleAgentHandlerContract;

final class ModuleAgentBus
{
    /**
     * @param  Closure(class-string): object  $handlerResolver
     */
    public function __construct(
        private readonly ModuleAgentRegistry $registry,
        private readonly Closure $handlerResolver,
    ) {}

    public function execute(
        ModuleAgentContract $agent,
    ): mixed {
        $handlerClass = $this->registry->handler(
            $agent->name(),
        );

        if ($handlerClass === null) {
            throw new LogicException(
                sprintf(
                    'No handler is registered for module agent [%s].',
                    $agent->name(),
                )
            );
        }

        $handler = ($this->handlerResolver)(
            $handlerClass,
        );

        if (
            ! $handler
            instanceof ModuleAgentHandlerContract
        ) {
            throw new LogicException(
                sprintf(
                    'Resolved module agent handler [%s] must implement [%s].',
                    $handlerClass,
                    ModuleAgentHandlerContract::class,
                )
            );
        }

        return $handler->handle($agent);
    }

    public function registry(): ModuleAgentRegistry
    {
        return $this->registry;
    }
}