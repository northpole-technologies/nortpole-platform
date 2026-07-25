<?php

declare(strict_types=1);

namespace Northpole\Runtime\Queries;

use Closure;
use LogicException;
use Northpole\Runtime\Queries\Contracts\ModuleQueryContract;
use Northpole\Runtime\Queries\Contracts\ModuleQueryHandlerContract;

final class ModuleQueryBus
{
    /**
     * @param  Closure(class-string): object  $handlerResolver
     */
    public function __construct(
        private readonly ModuleQueryRegistry $registry,
        private readonly Closure $handlerResolver,
    ) {}

    public function execute(
        ModuleQueryContract $query,
    ): mixed {
        $handlerClass = $this->registry->handler(
            $query->name(),
        );

        if ($handlerClass === null) {
            throw new LogicException(
                sprintf(
                    'No handler is registered for module query [%s].',
                    $query->name(),
                )
            );
        }

        $handler = ($this->handlerResolver)(
            $handlerClass,
        );

        if (
            ! $handler
            instanceof ModuleQueryHandlerContract
        ) {
            throw new LogicException(
                sprintf(
                    'Resolved module query handler [%s] must implement [%s].',
                    $handlerClass,
                    ModuleQueryHandlerContract::class,
                )
            );
        }

        return $handler->handle($query);
    }

    public function registry(): ModuleQueryRegistry
    {
        return $this->registry;
    }
}
