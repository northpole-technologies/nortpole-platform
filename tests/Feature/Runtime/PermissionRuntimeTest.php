<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use Northpole\Runtime\Permissions\PermissionRegistry;
use Tests\TestCase;

final class PermissionRuntimeTest extends TestCase
{
    public function test_runtime_registers_permissions_from_enabled_modules(): void
    {
        $registry = $this->app->make(
            PermissionRegistry::class
        );

        $this->assertTrue(
            $registry->has('crm.customers.view')
        );

        $this->assertTrue(
            $registry->has('crm.customers.create')
        );

        $this->assertTrue(
            $registry->has('crm.customers.update')
        );

        $this->assertTrue(
            $registry->has('crm.customers.delete')
        );

        $crmPermissions = $registry->forModule('crm');

        $this->assertNotEmpty($crmPermissions);
    }

    public function test_permission_registry_is_a_singleton(): void
    {
        $first = $this->app->make(
            PermissionRegistry::class
        );

        $second = $this->app->make(
            PermissionRegistry::class
        );

        $this->assertSame($first, $second);
    }
}
