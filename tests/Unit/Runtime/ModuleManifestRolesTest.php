<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Manifest\ModuleManifest;
use Tests\TestCase;

final class ModuleManifestRolesTest extends TestCase
{
    public function test_it_returns_role_definitions(): void
    {
        $manifest = $this->createManifest([
            'roles' => [
                [
                    'key' => 'sales',
                    'name' => 'Sales',
                    'description' => 'Sales team members.',
                    'permissions' => [
                        'crm.customers.view',
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            [
                [
                    'key' => 'sales',
                    'name' => 'Sales',
                    'description' => 'Sales team members.',
                    'permissions' => [
                        'crm.customers.view',
                    ],
                ],
            ],
            $manifest->roles()
        );
    }

    public function test_roles_default_to_an_empty_list(): void
    {
        $manifest = $this->createManifest();

        $this->assertSame(
            [],
            $manifest->roles()
        );
    }

    public function test_it_rejects_roles_that_are_not_a_list(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest field [roles] must be a list'
        );

        $this->createManifest([
            'roles' => [
                'sales' => [
                    'key' => 'sales',
                    'name' => 'Sales',
                ],
            ],
        ]);
    }

    public function test_it_rejects_non_structured_roles(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest field [roles] must contain structured role definitions'
        );

        $this->createManifest([
            'roles' => [
                'sales',
            ],
        ]);
    }

    public function test_it_rejects_roles_without_a_key(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module role definitions must contain a non-empty key'
        );

        $this->createManifest([
            'roles' => [
                [
                    'name' => 'Sales',
                ],
            ],
        ]);
    }

    public function test_it_rejects_roles_without_a_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module role definitions must contain a non-empty name'
        );

        $this->createManifest([
            'roles' => [
                [
                    'key' => 'sales',
                ],
            ],
        ]);
    }

    public function test_it_rejects_invalid_role_permissions(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module role definition permissions must contain non-empty strings'
        );

        $this->createManifest([
            'roles' => [
                [
                    'key' => 'sales',
                    'name' => 'Sales',
                    'permissions' => [
                        '',
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_invalid_system_flags(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module role definition system flags must be booleans'
        );

        $this->createManifest([
            'roles' => [
                [
                    'key' => 'sales',
                    'name' => 'Sales',
                    'system' => 'yes',
                ],
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createManifest(
        array $overrides = []
    ): ModuleManifest {
        return new ModuleManifest(
            data: array_merge(
                [
                    'name' => 'CRM',
                    'slug' => 'crm',
                    'version' => '1.0.0',
                ],
                $overrides,
            ),
            path: base_path('modules/CRM'),
            manifestPath: base_path(
                'modules/CRM/module.json'
            ),
        );
    }
}
