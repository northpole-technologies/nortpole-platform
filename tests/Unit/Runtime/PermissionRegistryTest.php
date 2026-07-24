<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Permissions\Permission;
use Northpole\Runtime\Permissions\PermissionRegistry;
use Tests\TestCase;

final class PermissionRegistryTest extends TestCase
{
    public function test_it_stores_permissions(): void
    {
        $registry = new PermissionRegistry();

        $permission = new Permission(
            moduleSlug: 'crm',
            name: 'crm.customers.view',
        );

        $registry->add($permission);

        $this->assertSame(1, $registry->count());
        $this->assertTrue(
            $registry->has('crm.customers.view')
        );
        $this->assertSame(
            $permission,
            $registry->get('crm.customers.view')
        );
    }

    public function test_it_orders_permissions_by_name(): void
    {
        $registry = new PermissionRegistry();

        $registry->addMany([
            new Permission(
                moduleSlug: 'crm',
                name: 'crm.customers.update',
            ),
            new Permission(
                moduleSlug: 'crm',
                name: 'crm.customers.create',
            ),
            new Permission(
                moduleSlug: 'crm',
                name: 'crm.customers.view',
            ),
        ]);

        $this->assertSame(
            [
                'crm.customers.create',
                'crm.customers.update',
                'crm.customers.view',
            ],
            array_map(
                static fn (Permission $permission): string =>
                    $permission->name(),
                $registry->all()
            )
        );
    }

    public function test_it_filters_permissions_by_module(): void
    {
        $registry = new PermissionRegistry();

        $registry->addMany([
            new Permission(
                moduleSlug: 'crm',
                name: 'crm.customers.view',
            ),
            new Permission(
                moduleSlug: 'inventory',
                name: 'inventory.stock.view',
            ),
        ]);

        $permissions = $registry->forModule('crm');

        $this->assertCount(1, $permissions);
        $this->assertSame(
            'crm.customers.view',
            $permissions[0]->name()
        );
    }

    public function test_adding_the_same_permission_replaces_it(): void
    {
        $registry = new PermissionRegistry();

        $first = new Permission(
            moduleSlug: 'crm',
            name: 'shared.view',
        );

        $replacement = new Permission(
            moduleSlug: 'inventory',
            name: 'shared.view',
        );

        $registry
            ->add($first)
            ->add($replacement);

        $this->assertSame(1, $registry->count());

        $stored = $registry->get('shared.view');

        $this->assertNotNull($stored);
        $this->assertSame(
            'inventory',
            $stored->moduleSlug()
        );
    }

    public function test_it_can_be_cleared(): void
    {
        $registry = new PermissionRegistry();

        $registry->add(
            new Permission(
                moduleSlug: 'crm',
                name: 'crm.customers.view',
            )
        );

        $registry->clear();

        $this->assertSame(0, $registry->count());
        $this->assertSame([], $registry->all());
    }

    public function test_it_rejects_an_empty_module_slug(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'A permission must have a module slug.'
        );

        new Permission(
            moduleSlug: '   ',
            name: 'crm.customers.view',
        );
    }

    public function test_it_rejects_an_empty_permission_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'A permission must have a name.'
        );

        new Permission(
            moduleSlug: 'crm',
            name: '   ',
        );
    }
}