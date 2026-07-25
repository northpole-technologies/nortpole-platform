<?php

declare(strict_types=1);

namespace Northpole\Runtime\Lifecycle;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\BootStageContract;
use Northpole\Runtime\Roles\RoleDefinition;
use Northpole\Runtime\Roles\RoleDefinitionRegistry;

final class RoleDefinitionStage implements BootStageContract
{
    public function __construct(
        private readonly RoleDefinitionRegistry $registry,
    ) {}

    public function name(): string
    {
        return 'role-definitions';
    }

    public function priority(): int
    {
        return 560;
    }

    public function boot(BootContext $context): void
    {
        $module = $context->module();

        foreach ($module->roles() as $role) {
            if (! is_array($role)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Roles for module [%s] must be structured definitions.',
                        $module->slug(),
                    )
                );
            }

            $this->registry->add(
                RoleDefinition::fromArray(
                    $module->slug(),
                    $role,
                )
            );
        }
    }
}
