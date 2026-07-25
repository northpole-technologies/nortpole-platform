<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Roles\RoleDefinition;
use Northpole\Runtime\Roles\RoleDefinitionRegistry;
use Tests\TestCase;

final class RoleDefinitionRegistryTest extends TestCase
{
    public function test_it_stores_role_definitions(): void
    {
        $registry = new RoleDefinitionRegistry;

        $role = RoleDefinition::fromArray(
            moduleSlug: 'crm',
            definition: [
                'key' => 'sales',
                'name' => 'Sales',
                'description' => 'Sales team members.',
                'permissions' => [
                    'crm.customers.view',
                    'crm.customers.create',
                ],
            ],
        );

        $registry->add($role);

        $this->assertSame(
            1,
            $registry->count()
        );

        $this->assertTrue(
            $registry->has('sales')
        );

        $this->assertSame(
            $role,
            $registry->get('sales')
        );
    }

    public function test_it_orders_roles_by_key(): void
    {
        $registry = new RoleDefinitionRegistry;

        $registry->addMany([
            RoleDefinition::fromArray(
                'crm',
                [
                    'key' => 'viewer',
                    'name' => 'Viewer',
                ],
            ),
            RoleDefinition::fromArray(
                'crm',
                [
                    'key' => 'administrator',
                    'name' => 'Administrator',
                ],
            ),
            RoleDefinition::fromArray(
                'crm',
                [
                    'key' => 'sales',
                    'name' => 'Sales',
                ],
            ),
        ]);

        $this->assertSame(
            [
                'administrator',
                'sales',
                'viewer',
            ],
            array_map(
                static fn (
                    RoleDefinition $role
                ): string => $role->key(),
                $registry->all(),
            )
        );
    }

    public function test_it_merges_contributions_for_the_same_role(): void
    {
        $registry = new RoleDefinitionRegistry;

        $registry->add(
            RoleDefinition::fromArray(
                'crm',
                [
                    'key' => 'sales',
                    'name' => 'Sales',
                    'description' => 'Sales team members.',
                    'permissions' => [
                        'crm.customers.view',
                        'crm.customers.create',
                    ],
                ],
            )
        );

        $registry->add(
            RoleDefinition::fromArray(
                'inventory',
                [
                    'key' => 'sales',
                    'name' => 'Sales Team',
                    'permissions' => [
                        'inventory.stock.view',
                    ],
                ],
            )
        );

        $role = $registry->get('sales');

        $this->assertInstanceOf(
            RoleDefinition::class,
            $role
        );

        $this->assertSame(
            'Sales',
            $role->name()
        );

        $this->assertSame(
            'Sales team members.',
            $role->description()
        );

        $this->assertSame(
            [
                'crm.customers.create',
                'crm.customers.view',
                'inventory.stock.view',
            ],
            $role->permissions()
        );

        $this->assertSame(
            [
                'crm',
                'inventory',
            ],
            $role->contributingModules()
        );

        $this->assertSame(
            1,
            $registry->count()
        );
    }

    public function test_it_filters_roles_by_contributing_module(): void
    {
        $registry = new RoleDefinitionRegistry;

        $registry->addMany([
            RoleDefinition::fromArray(
                'crm',
                [
                    'key' => 'sales',
                    'name' => 'Sales',
                ],
            ),
            RoleDefinition::fromArray(
                'inventory',
                [
                    'key' => 'warehouse',
                    'name' => 'Warehouse',
                ],
            ),
        ]);

        $roles = $registry->forModule(
            'crm'
        );

        $this->assertCount(
            1,
            $roles
        );

        $this->assertSame(
            'sales',
            $roles[0]->key()
        );
    }

    public function test_it_finds_roles_that_contribute_a_permission(): void
    {
        $registry = new RoleDefinitionRegistry;

        $registry->addMany([
            RoleDefinition::fromArray(
                'crm',
                [
                    'key' => 'sales',
                    'name' => 'Sales',
                    'permissions' => [
                        'crm.customers.view',
                    ],
                ],
            ),
            RoleDefinition::fromArray(
                'crm',
                [
                    'key' => 'viewer',
                    'name' => 'Viewer',
                    'permissions' => [
                        'crm.customers.view',
                    ],
                ],
            ),
            RoleDefinition::fromArray(
                'inventory',
                [
                    'key' => 'warehouse',
                    'name' => 'Warehouse',
                    'permissions' => [
                        'inventory.stock.view',
                    ],
                ],
            ),
        ]);

        $roles = $registry->withPermission(
            'crm.customers.view'
        );

        $this->assertSame(
            [
                'sales',
                'viewer',
            ],
            array_map(
                static fn (
                    RoleDefinition $role
                ): string => $role->key(),
                $roles,
            )
        );
    }

    public function test_it_normalises_and_deduplicates_permissions(): void
    {
        $role = RoleDefinition::fromArray(
            moduleSlug: 'crm',
            definition: [
                'key' => 'sales',
                'name' => 'Sales',
                'permissions' => [
                    ' crm.customers.view ',
                    'crm.customers.create',
                    'crm.customers.view',
                ],
            ],
        );

        $this->assertSame(
            [
                'crm.customers.create',
                'crm.customers.view',
            ],
            $role->permissions()
        );
    }

    public function test_it_serialises_role_metadata(): void
    {
        $role = RoleDefinition::fromArray(
            moduleSlug: 'crm',
            definition: [
                'key' => 'sales',
                'name' => 'Sales',
                'description' => 'Sales team members.',
                'permissions' => [
                    'crm.customers.view',
                ],
                'system' => true,
            ],
        );

        $this->assertSame(
            [
                'key' => 'sales',
                'name' => 'Sales',
                'description' => 'Sales team members.',
                'permissions' => [
                    'crm.customers.view',
                ],
                'contributing_modules' => [
                    'crm',
                ],
                'system' => true,
            ],
            $role->toArray()
        );
    }

    public function test_it_rejects_an_empty_role_key(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'A role definition must have a key.'
        );

        RoleDefinition::fromArray(
            moduleSlug: 'crm',
            definition: [
                'key' => '   ',
                'name' => 'Sales',
            ],
        );
    }

    public function test_it_rejects_an_empty_role_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'A role definition must have a name.'
        );

        RoleDefinition::fromArray(
            moduleSlug: 'crm',
            definition: [
                'key' => 'sales',
                'name' => '   ',
            ],
        );
    }

    public function test_it_rejects_invalid_permission_values(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Role definition permissions must contain non-empty strings.'
        );

        RoleDefinition::fromArray(
            moduleSlug: 'crm',
            definition: [
                'key' => 'sales',
                'name' => 'Sales',
                'permissions' => [
                    'crm.customers.view',
                    '',
                ],
            ],
        );
    }

    public function test_it_cannot_merge_different_role_keys(): void
    {
        $sales = RoleDefinition::fromArray(
            'crm',
            [
                'key' => 'sales',
                'name' => 'Sales',
            ],
        );

        $viewer = RoleDefinition::fromArray(
            'crm',
            [
                'key' => 'viewer',
                'name' => 'Viewer',
            ],
        );

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Cannot merge role definitions [sales] and [viewer].'
        );

        $sales->merge($viewer);
    }

    public function test_it_can_be_cleared(): void
    {
        $registry = new RoleDefinitionRegistry;

        $registry->add(
            RoleDefinition::fromArray(
                'crm',
                [
                    'key' => 'sales',
                    'name' => 'Sales',
                ],
            )
        );

        $registry->clear();

        $this->assertTrue(
            $registry->isEmpty()
        );

        $this->assertSame(
            0,
            $registry->count()
        );

        $this->assertSame(
            [],
            $registry->all()
        );
    }
}
