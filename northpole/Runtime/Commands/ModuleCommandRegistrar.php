<?php

declare(strict_types=1);

namespace Northpole\Runtime\Commands;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\ModuleManifestContract;

final class ModuleCommandRegistrar
{
    public function __construct(
        private readonly ModuleCommandRegistry $registry,
    ) {}

    public function register(
        ModuleManifestContract $module,
    ): void {
        $moduleSlug = trim($module->slug());

        if ($moduleSlug === '') {
            throw new InvalidArgumentException(
                'A module command handler owner cannot be empty.'
            );
        }

        foreach (
            $module->handledCommands() as $commandName => $handlerClass
        ) {
            $this->registerCommandHandler(
                moduleSlug: $moduleSlug,
                commandName: $commandName,
                handlerClass: $handlerClass,
            );
        }
    }

    private function registerCommandHandler(
        string $moduleSlug,
        string $commandName,
        string $handlerClass,
    ): void {
        $commandName = trim($commandName);
        $handlerClass = trim($handlerClass);

        if ($commandName === '') {
            throw new InvalidArgumentException(
                sprintf(
                    'Handled command names for module [%s] must be non-empty strings.',
                    $moduleSlug,
                )
            );
        }

        if ($handlerClass === '') {
            throw new InvalidArgumentException(
                sprintf(
                    'Command handlers for module [%s] and command [%s] must be non-empty class names.',
                    $moduleSlug,
                    $commandName,
                )
            );
        }

        $this->registry->register(
            commandName: $commandName,
            handler: $handlerClass,
            module: $moduleSlug,
        );
    }

    public function registry(): ModuleCommandRegistry
    {
        return $this->registry;
    }
}
