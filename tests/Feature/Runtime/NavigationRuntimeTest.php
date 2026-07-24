<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use Northpole\Runtime\Navigation\NavigationRegistry;
use Tests\TestCase;

final class NavigationRuntimeTest extends TestCase
{
    public function test_runtime_registers_navigation_from_enabled_modules(): void
    {
        $registry = $this->app->make(
            NavigationRegistry::class
        );

        $crmItems = $registry->forModule('crm');

        $this->assertNotEmpty($crmItems);

        $customers = collect($crmItems)->first(
            static fn ($item): bool =>
                $item->route() === 'crm.customers.index'
        );

        $this->assertNotNull($customers);
        $this->assertSame(
            'Customers',
            $customers->label()
        );
        $this->assertSame(
            'crm.customers.view',
            $customers->permission()
        );
    }

    public function test_navigation_registry_is_a_singleton(): void
    {
        $first = $this->app->make(
            NavigationRegistry::class
        );

        $second = $this->app->make(
            NavigationRegistry::class
        );

        $this->assertSame($first, $second);
    }
}