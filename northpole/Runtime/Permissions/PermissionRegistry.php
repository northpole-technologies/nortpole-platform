<?php

declare(strict_types=1);

namespace Northpole\Runtime\Permissions;

use Northpole\Runtime\Support\AbstractRuntimeRegistry;

/**
 * @extends AbstractRuntimeRegistry<Permission>
 */
final class PermissionRegistry extends AbstractRuntimeRegistry
{
    /**
     * @return array<int, Permission>
     */
    public function forModule(
        string $moduleSlug
    ): array {
        $normalisedModuleSlug = trim(
            $moduleSlug
        );

        if ($normalisedModuleSlug === '') {
            return [];
        }

        $permissions = array_filter(
            $this->items,
            static fn (
                Permission $permission
            ): bool => $permission->moduleSlug()
                === $normalisedModuleSlug
        );

        $permissions = array_values(
            $permissions
        );

        usort(
            $permissions,
            fn (
                Permission $first,
                Permission $second,
            ): int => $this->compare(
                $first,
                $second,
            )
        );

        return $permissions;
    }

    protected function keyFor(
        object $item
    ): string {
        assert(
            $item instanceof Permission
        );

        return $item->key();
    }

    protected function compare(
        object $first,
        object $second,
    ): int {
        assert(
            $first instanceof Permission
        );

        assert(
            $second instanceof Permission
        );

        return strcmp(
            $first->key(),
            $second->key(),
        );
    }
}
