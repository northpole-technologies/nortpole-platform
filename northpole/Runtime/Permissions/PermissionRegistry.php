<?php

declare(strict_types=1);

namespace Northpole\Runtime\Permissions;

final class PermissionRegistry
{
    /**
     * @var array<string, Permission>
     */
    private array $permissions = [];

    public function add(Permission $permission): self
    {
        $this->permissions[$permission->key()] = $permission;

        return $this;
    }

    /**
     * @param  iterable<int, Permission>  $permissions
     */
    public function addMany(iterable $permissions): self
    {
        foreach ($permissions as $permission) {
            $this->add($permission);
        }

        return $this;
    }

    /**
     * @return array<int, Permission>
     */
    public function all(): array
    {
        $permissions = array_values(
            $this->permissions
        );

        usort(
            $permissions,
            static fn (
                Permission $first,
                Permission $second,
            ): int => strcmp(
                $first->name(),
                $second->name()
            )
        );

        return $permissions;
    }

    /**
     * @return array<int, Permission>
     */
    public function forModule(string $moduleSlug): array
    {
        $permissions = array_filter(
            $this->permissions,
            static fn (Permission $permission): bool => $permission->moduleSlug() === $moduleSlug
        );

        $permissions = array_values($permissions);

        usort(
            $permissions,
            static fn (
                Permission $first,
                Permission $second,
            ): int => strcmp(
                $first->name(),
                $second->name()
            )
        );

        return $permissions;
    }

    public function has(string $permission): bool
    {
        return isset(
            $this->permissions[$permission]
        );
    }

    public function get(string $permission): ?Permission
    {
        return $this->permissions[$permission] ?? null;
    }

    public function count(): int
    {
        return count($this->permissions);
    }

    public function clear(): void
    {
        $this->permissions = [];
    }
}
