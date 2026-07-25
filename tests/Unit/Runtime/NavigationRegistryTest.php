<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Navigation\NavigationItem;
use Northpole\Runtime\Navigation\NavigationRegistry;
use Tests\TestCase;

final class NavigationRegistryTest extends TestCase
{
    public function test_it_stores_navigation_items(): void
    {
        $registry = new NavigationRegistry;

        $item = new NavigationItem(
            moduleSlug: 'crm',
            label: 'Customers',
            route: 'crm.customers.index',
            icon: 'users',
            permission: 'crm.customers.view',
            group: 'CRM',
            order: 10,
        );

        $registry->add($item);

        $this->assertSame(1, $registry->count());
        $this->assertTrue($registry->has($item->key()));
        $this->assertSame(
            [$item],
            $registry->all()
        );
    }

    public function test_it_orders_items_by_order_then_label(): void
    {
        $registry = new NavigationRegistry;

        $registry->addMany([
            new NavigationItem(
                moduleSlug: 'inventory',
                label: 'Stock',
                route: 'inventory.stock.index',
                order: 20,
            ),
            new NavigationItem(
                moduleSlug: 'crm',
                label: 'Contacts',
                route: 'crm.contacts.index',
                order: 10,
            ),
            new NavigationItem(
                moduleSlug: 'crm',
                label: 'Customers',
                route: 'crm.customers.index',
                order: 10,
            ),
        ]);

        $this->assertSame(
            [
                'Contacts',
                'Customers',
                'Stock',
            ],
            array_map(
                static fn (NavigationItem $item): string => $item->label(),
                $registry->all()
            )
        );
    }

    public function test_it_filters_items_by_module(): void
    {
        $registry = new NavigationRegistry;

        $registry->addMany([
            new NavigationItem(
                moduleSlug: 'crm',
                label: 'Customers',
                route: 'crm.customers.index',
            ),
            new NavigationItem(
                moduleSlug: 'inventory',
                label: 'Stock',
                route: 'inventory.stock.index',
            ),
        ]);

        $items = $registry->forModule('crm');

        $this->assertCount(1, $items);
        $this->assertSame(
            'Customers',
            $items[0]->label()
        );
    }

    public function test_adding_the_same_item_replaces_it(): void
    {
        $registry = new NavigationRegistry;

        $first = new NavigationItem(
            moduleSlug: 'crm',
            label: 'Customers',
            route: 'crm.customers.index',
            order: 20,
        );

        $replacement = new NavigationItem(
            moduleSlug: 'crm',
            label: 'Customers',
            route: 'crm.customers.index',
            order: 10,
        );

        $registry
            ->add($first)
            ->add($replacement);

        $this->assertSame(1, $registry->count());
        $this->assertSame(
            10,
            $registry->all()[0]->order()
        );
    }

    public function test_it_preserves_extra_manifest_metadata(): void
    {
        $item = NavigationItem::fromArray(
            'crm',
            [
                'label' => 'Customers',
                'route' => 'crm.customers.index',
                'badge' => 'new',
                'external' => false,
            ]
        );

        $this->assertSame(
            [
                'badge' => 'new',
                'external' => false,
            ],
            $item->metadata()
        );
    }

    public function test_it_rejects_a_missing_label(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Navigation field [label] must be a non-empty string.'
        );

        NavigationItem::fromArray(
            'crm',
            [
                'route' => 'crm.customers.index',
            ]
        );
    }

    public function test_it_rejects_a_missing_route(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Navigation field [route] must be a non-empty string.'
        );

        NavigationItem::fromArray(
            'crm',
            [
                'label' => 'Customers',
            ]
        );
    }
}
