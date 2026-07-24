<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use Northpole\Runtime\Capabilities\CapabilityRegistry;
use Tests\TestCase;

final class CapabilityRuntimeTest extends TestCase
{
    public function test_runtime_registers_capabilities_from_enabled_modules(): void
    {
        $registry = $this->app->make(
            CapabilityRegistry::class
        );

        $this->assertTrue(
            $registry->has(
                'crm',
                'customers'
            )
        );

        $this->assertTrue(
            $registry->has(
                'crm',
                'contacts'
            )
        );

        $this->assertTrue(
            $registry->has(
                'crm',
                'notes'
            )
        );

        $this->assertTrue(
            $registry->has(
                'crm',
                'tasks'
            )
        );

        $this->assertTrue(
            $registry->has(
                'crm',
                'activities'
            )
        );

        $crmCapabilities = $registry->forModule('crm');

        $this->assertCount(
            5,
            $crmCapabilities
        );
    }

    public function test_capability_registry_is_a_singleton(): void
    {
        $first = $this->app->make(
            CapabilityRegistry::class
        );

        $second = $this->app->make(
            CapabilityRegistry::class
        );

        $this->assertSame($first, $second);
    }
}