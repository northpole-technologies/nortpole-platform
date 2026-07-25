<?php

declare(strict_types=1);

namespace Northpole\Runtime\Commands;

use Closure;
use LogicException;
use Northpole\Runtime\Commands\Contracts\ModuleCommandContract;
use Northpole\Runtime\Commands\Contracts\ModuleCommandHandlerContract;

final class ModuleCommandBus
{
    /**
     * @param  Closure(class-string): object  $handlerResolver
     */
    public function __construct(
        private readonly ModuleCommandRegistry $registry,
        private readonly Closure $handlerResolver,
    ) {}

    public function execute(
        ModuleCommandContract $command,
    ): mixed {
        $handlerClass = $this->registry->handler(
            $command->name(),
        );

        if ($handlerClass === null) {
            throw new LogicException(
                sprintf(
                    'No handler is registered for module command [%s].',
                    $command->name(),
                )
            );
        }

        $handler = ($this->handlerResolver)(
            $handlerClass,
        );

        if (
            ! $handler
            instanceof ModuleCommandHandlerContract
        ) {
            throw new LogicException(
                sprintf(
                    'Resolved module command handler [%s] must implement [%s].',
                    $handlerClass,
                    ModuleCommandHandlerContract::class,
                )
            );
        }

        return $handler->handle($command);
    }

    public function registry(): ModuleCommandRegistry
    {
        return $this->registry;
    }
}
