<?php

declare(strict_types=1);

namespace Northpole\Runtime\Agents;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\ModuleManifestContract;

final class ModuleAgentRegistrar
{
    public function __construct(
        private readonly ModuleAgentRegistry $registry,
    ) {}

    public function register(
        ModuleManifestContract $module,
    ): void {
        $moduleSlug = trim($module->slug());

        if ($moduleSlug === '') {
            throw new InvalidArgumentException(
                'A module agent handler owner cannot be empty.'
            );
        }

        foreach (
            $module->handledAgents() as $agentName => $handlerClass
        ) {
            $this->registerAgentHandler(
                moduleSlug: $moduleSlug,
                agentName: $agentName,
                handlerClass: $handlerClass,
            );
        }
    }

    private function registerAgentHandler(
        string $moduleSlug,
        string $agentName,
        string $handlerClass,
    ): void {
        $agentName = trim($agentName);
        $handlerClass = trim($handlerClass);

        if ($agentName === '') {
            throw new InvalidArgumentException(
                sprintf(
                    'Handled agent names for module [%s] must be non-empty strings.',
                    $moduleSlug,
                )
            );
        }

        if ($handlerClass === '') {
            throw new InvalidArgumentException(
                sprintf(
                    'Agent handlers for module [%s] and agent [%s] must be non-empty class names.',
                    $moduleSlug,
                    $agentName,
                )
            );
        }

        $this->registry->register(
            agentName: $agentName,
            handler: $handlerClass,
            module: $moduleSlug,
        );
    }

    public function registry(): ModuleAgentRegistry
    {
        return $this->registry;
    }
}