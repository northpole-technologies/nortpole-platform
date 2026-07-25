<?php

declare(strict_types=1);

namespace Northpole\Runtime\Validation\Rules;

use Closure;
use Northpole\Runtime\Contracts\ModuleManifestContract;
use Northpole\Runtime\Queries\Contracts\ModuleQueryHandlerContract;
use Northpole\Runtime\Queries\ModuleQueryRegistry;

final class QueryHandlerValidationRule extends
    AbstractRegistryValidationRule
{
    /**
     * @param  Closure(): array<string, ModuleManifestContract>  $modulesResolver
     */
    public function __construct(
        Closure $modulesResolver,
        private readonly ModuleQueryRegistry $registry,
    ) {
        parent::__construct(
            $modulesResolver,
        );
    }

    public function name(): string
    {
        return 'query-handlers';
    }

    /**
     * @return array<string, string>
     */
    protected function declarations(
        ModuleManifestContract $module,
    ): array {
        return $module->handledQueries();
    }

    protected function type(): string
    {
        return 'query';
    }

    protected function handlerContract(): string
    {
        return ModuleQueryHandlerContract::class;
    }

    protected function registeredHandler(
        string $name,
    ): ?string {
        return $this->registry->handler($name);
    }

    protected function registeredOwner(
        string $name,
    ): ?string {
        return $this->registry->owner($name);
    }

    /**
     * @return array<string, string>
     */
    protected function registeredHandlers(): array
    {
        return $this->registry->all();
    }
}