<?php

declare(strict_types=1);

namespace Northpole\Runtime\Synchronisation;

use App\Models\Organisation;
use App\Models\Permission as PermissionModel;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use LogicException;
use Northpole\Runtime\Permissions\Permission;
use Northpole\Runtime\Permissions\PermissionRegistry;
use Northpole\Runtime\Roles\RoleDefinition;
use Northpole\Runtime\Roles\RoleDefinitionRegistry;

final class TenantAccessSynchroniser
{
    public function __construct(
        private readonly PermissionRegistry $permissions,
        private readonly RoleDefinitionRegistry $roles,
    ) {
    }

    public function synchronise(
        Organisation $organisation
    ): void {
        DB::transaction(function () use (
            $organisation
        ): void {
            $permissionModels = $this->synchronisePermissions();

            $this->synchroniseRoles(
                organisation: $organisation,
                permissionModels: $permissionModels,
            );
        });
    }

    /**
     * @return array<string, PermissionModel>
     */
    private function synchronisePermissions(): array
    {
        $models = [];

        foreach ($this->permissions->all() as $permission) {
            if (! $permission instanceof Permission) {
                throw new LogicException(
                    'The permission registry contains an invalid item.'
                );
            }

            $model = PermissionModel::query()
                ->updateOrCreate(
                    [
                        'key' => $permission->key(),
                    ],
                    [
                        'name' => $permission->title(),
                        'description' => $permission->description(),
                        'module_key' => $permission->moduleSlug(),
                        'is_active' => true,
                    ],
                );

            $models[
                $permission->key()
            ] = $model;
        }

        return $models;
    }

    /**
     * @param  array<string, PermissionModel>  $permissionModels
     */
    private function synchroniseRoles(
        Organisation $organisation,
        array $permissionModels,
    ): void {
        foreach ($this->roles->all() as $definition) {
            if (! $definition instanceof RoleDefinition) {
                throw new LogicException(
                    'The role definition registry contains an invalid item.'
                );
            }

            $role = Role::query()
                ->where(
                    'organisation_id',
                    $organisation->getKey(),
                )
                ->where(
                    'key',
                    $definition->key(),
                )
                ->first();

            if (
                $role !== null
                && ! $role->is_system
            ) {
                throw new LogicException(
                    sprintf(
                        'Tenant role [%s] conflicts with a runtime role definition.',
                        $definition->key(),
                    )
                );
            }

            if ($role === null) {
                $role = new Role;
            }

            $role->forceFill([
                'organisation_id' => $organisation->getKey(),
                'key' => $definition->key(),
                'name' => $definition->name(),
                'description' => $definition->description(),
                'is_system' => $definition->system(),
                'is_active' => true,
            ]);

            $role->save();

            $permissionIds = [];

            foreach (
                $definition->permissions()
                as $permissionKey
            ) {
                $permission = $permissionModels[
                    $permissionKey
                ] ?? PermissionModel::query()
                    ->where(
                        'key',
                        $permissionKey,
                    )
                    ->first();

                if ($permission === null) {
                    throw new LogicException(
                        sprintf(
                            'Runtime role [%s] references unknown permission [%s].',
                            $definition->key(),
                            $permissionKey,
                        )
                    );
                }

                $permissionIds[] = $permission->getKey();
            }

            $role->permissions()->sync(
                $permissionIds
            );
        }
    }
}