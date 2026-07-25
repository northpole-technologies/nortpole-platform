<?php

declare(strict_types=1);

namespace Northpole\Runtime\Roles;

use Northpole\Runtime\Support\AbstractRuntimeRegistry;

/**
 * @extends AbstractRuntimeRegistry<RoleDefinition>
 */
final class RoleDefinitionRegistry extends AbstractRuntimeRegistry
{
    public function add(
        object $item
    ): static {
        assert(
            $item instanceof RoleDefinition
        );

        $existing = $this->get(
            $item->key()
        );

        if ($existing instanceof RoleDefinition) {
            $item = $existing->merge(
                $item
            );
        }

        return parent::add(
            $item
        );
    }

    /**
     * @return array<int, RoleDefinition>
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

        $roles = array_filter(
            $this->items,
            static fn (
                RoleDefinition $role
            ): bool => $role->contributedBy(
                $normalisedModuleSlug
            )
        );

        $roles = array_values(
            $roles
        );

        usort(
            $roles,
            fn (
                RoleDefinition $first,
                RoleDefinition $second,
            ): int => $this->compare(
                $first,
                $second,
            )
        );

        return $roles;
    }

    /**
     * @return array<int, RoleDefinition>
     */
    public function withPermission(
        string $permission
    ): array {
        $normalisedPermission = trim(
            $permission
        );

        if ($normalisedPermission === '') {
            return [];
        }

        $roles = array_filter(
            $this->items,
            static fn (
                RoleDefinition $role
            ): bool => $role->contributesPermission(
                $normalisedPermission
            )
        );

        $roles = array_values(
            $roles
        );

        usort(
            $roles,
            fn (
                RoleDefinition $first,
                RoleDefinition $second,
            ): int => $this->compare(
                $first,
                $second,
            )
        );

        return $roles;
    }

    protected function keyFor(
        object $item
    ): string {
        assert(
            $item instanceof RoleDefinition
        );

        return $item->key();
    }

    protected function compare(
        object $first,
        object $second,
    ): int {
        assert(
            $first instanceof RoleDefinition
        );

        assert(
            $second instanceof RoleDefinition
        );

        return strcmp(
            $first->key(),
            $second->key(),
        );
    }
}
