<?php

declare(strict_types=1);

namespace Northpole\Runtime\Queries;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\ModuleManifestContract;

final class ModuleQueryRegistrar
{
    public function __construct(
        private readonly ModuleQueryRegistry $registry,
    ) {}

    public function register(
        ModuleManifestContract $module,
    ): void {
        $moduleSlug = trim($module->slug());

        if ($moduleSlug === '') {
            throw new InvalidArgumentException(
                'A module query handler owner cannot be empty.'
            );
        }

        foreach (
            $module->handledQueries() as $queryName => $handlerClass
        ) {
            $this->registerQueryHandler(
                moduleSlug: $moduleSlug,
                queryName: $queryName,
                handlerClass: $handlerClass,
            );
        }
    }

    private function registerQueryHandler(
        string $moduleSlug,
        string $queryName,
        string $handlerClass,
    ): void {
        $queryName = trim($queryName);
        $handlerClass = trim($handlerClass);

        if ($queryName === '') {
            throw new InvalidArgumentException(
                sprintf(
                    'Handled query names for module [%s] must be non-empty strings.',
                    $moduleSlug,
                )
            );
        }

        if ($handlerClass === '') {
            throw new InvalidArgumentException(
                sprintf(
                    'Query handlers for module [%s] and query [%s] must be non-empty class names.',
                    $moduleSlug,
                    $queryName,
                )
            );
        }

        $this->registry->register(
            queryName: $queryName,
            handler: $handlerClass,
            module: $moduleSlug,
        );
    }

    public function registry(): ModuleQueryRegistry
    {
        return $this->registry;
    }
}
