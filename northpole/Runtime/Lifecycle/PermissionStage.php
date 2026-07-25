<?php

declare(strict_types=1);

namespace Northpole\Runtime\Lifecycle;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\BootStageContract;
use Northpole\Runtime\Permissions\Permission;
use Northpole\Runtime\Permissions\PermissionRegistry;

final class PermissionStage implements BootStageContract
{
    public function __construct(
        private readonly PermissionRegistry $registry,
    ) {}

    public function name(): string
    {
        return 'permissions';
    }

    public function priority(): int
    {
        return 550;
    }

    public function boot(BootContext $context): void
    {
        $module = $context->module();

        foreach ($module->permissions() as $permission) {
            if (is_string($permission)) {
                if (trim($permission) === '') {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Permissions for module [%s] must be non-empty strings or structured definitions.',
                            $module->slug()
                        )
                    );
                }

                $this->registry->add(
                    Permission::fromString(
                        $module->slug(),
                        $permission,
                    )
                );

                continue;
            }

            if (is_array($permission)) {
                $this->registry->add(
                    Permission::fromArray(
                        $module->slug(),
                        $permission,
                    )
                );

                continue;
            }

            throw new InvalidArgumentException(
                sprintf(
                    'Permissions for module [%s] must be non-empty strings or structured definitions.',
                    $module->slug()
                )
            );
        }
    }
}