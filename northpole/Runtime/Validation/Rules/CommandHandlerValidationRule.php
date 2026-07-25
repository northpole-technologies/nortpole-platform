<?php

declare(strict_types=1);

namespace Northpole\Runtime\Validation\Rules;

use Closure;
use Northpole\Runtime\Commands\Contracts\ModuleCommandHandlerContract;
use Northpole\Runtime\Commands\ModuleCommandRegistry;
use Northpole\Runtime\Contracts\ModuleManifestContract;

final class CommandHandlerValidationRule extends
    AbstractRegistryValidationRule
{
    /**
     * @param  Closure(): array<string, ModuleManifestContract>  $modulesResolver
     */
    public function __construct(
        Closure $modulesResolver,
        private readonly ModuleCommandRegistry $registry,
    ) {
        parent::__construct(
            $modulesResolver,
        );
    }

    public function name(): string
    {
        return 'command-handlers';
    }

    /**
     * @return array<string, string>
     */
    protected function declarations(
        ModuleManifestContract $module,
    ): array {
        return $module->handledCommands();
    }

    protected function type(): string
    {
        return 'command';
    }

    protected function handlerContract(): string
    {
        return ModuleCommandHandlerContract::class;
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