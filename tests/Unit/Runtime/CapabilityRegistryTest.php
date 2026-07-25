<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Capabilities\Capability;
use Northpole\Runtime\Capabilities\CapabilityRegistry;
use Tests\TestCase;

final class CapabilityRegistryTest extends TestCase
{
    public function test_it_stores_capabilities(): void
    {
        $registry = new CapabilityRegistry;

        $capability = new Capability(
            moduleSlug: 'crm',
            name: 'customers',
        );

        $registry->add($capability);

        $this->assertSame(1, $registry->count());

        $this->assertTrue(
            $registry->has(
                'crm',
                'customers'
            )
        );

        $this->assertSame(
            $capability,
            $registry->get(
                'crm',
                'customers'
            )
        );
    }

    public function test_it_orders_capabilities_by_module_and_name(): void
    {
        $registry = new CapabilityRegistry;

        $registry->addMany([
            new Capability(
                moduleSlug: 'inventory',
                name: 'stock',
            ),
            new Capability(
                moduleSlug: 'crm',
                name: 'customers',
            ),
            new Capability(
                moduleSlug: 'crm',
                name: 'contacts',
            ),
        ]);

        $this->assertSame(
            [
                'crm:contacts',
                'crm:customers',
                'inventory:stock',
            ],
            array_map(
                static fn (Capability $capability): string => $capability->key(),
                $registry->all()
            )
        );
    }

    public function test_it_filters_capabilities_by_module(): void
    {
        $registry = new CapabilityRegistry;

        $registry->addMany([
            new Capability(
                moduleSlug: 'crm',
                name: 'customers',
            ),
            new Capability(
                moduleSlug: 'crm',
                name: 'contacts',
            ),
            new Capability(
                moduleSlug: 'inventory',
                name: 'stock',
            ),
        ]);

        $capabilities = $registry->forModule('crm');

        $this->assertCount(2, $capabilities);

        $this->assertSame(
            [
                'contacts',
                'customers',
            ],
            array_map(
                static fn (Capability $capability): string => $capability->name(),
                $capabilities
            )
        );
    }

    public function test_multiple_modules_can_share_a_capability_name(): void
    {
        $registry = new CapabilityRegistry;

        $registry->addMany([
            new Capability(
                moduleSlug: 'crm',
                name: 'reporting',
            ),
            new Capability(
                moduleSlug: 'inventory',
                name: 'reporting',
            ),
        ]);

        $this->assertSame(2, $registry->count());

        $this->assertCount(
            2,
            $registry->named('reporting')
        );

        $this->assertTrue(
            $registry->has(
                'crm',
                'reporting'
            )
        );

        $this->assertTrue(
            $registry->has(
                'inventory',
                'reporting'
            )
        );
    }

    public function test_adding_the_same_capability_replaces_it(): void
    {
        $registry = new CapabilityRegistry;

        $first = new Capability(
            moduleSlug: 'crm',
            name: 'customers',
        );

        $replacement = new Capability(
            moduleSlug: 'crm',
            name: 'customers',
        );

        $registry
            ->add($first)
            ->add($replacement);

        $this->assertSame(1, $registry->count());

        $this->assertSame(
            $replacement,
            $registry->get(
                'crm',
                'customers'
            )
        );
    }

    public function test_it_can_be_cleared(): void
    {
        $registry = new CapabilityRegistry;

        $registry->add(
            new Capability(
                moduleSlug: 'crm',
                name: 'customers',
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
            'A capability must have a module slug.'
        );

        new Capability(
            moduleSlug: '   ',
            name: 'customers',
        );
    }

    public function test_it_rejects_an_empty_capability_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'A capability must have a name.'
        );

        new Capability(
            moduleSlug: 'crm',
            name: '   ',
        );
    }
}
