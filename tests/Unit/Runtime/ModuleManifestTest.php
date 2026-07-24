<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Manifest\ModuleManifest;
use PHPUnit\Framework\TestCase;

final class ModuleManifestTest extends TestCase
{
    public function test_it_returns_legacy_dependency_slugs(): void
    {
        $manifest = $this->manifest([
            'dependencies' => [
                'crm',
                'notifications',
            ],
        ]);

        self::assertSame(
            [
                'crm',
                'notifications',
            ],
            $manifest->dependencies()
        );
    }

    public function test_it_normalises_legacy_dependencies_to_wildcard_constraints(): void
    {
        $manifest = $this->manifest([
            'dependencies' => [
                'crm',
                'notifications',
            ],
        ]);

        self::assertSame(
            [
                'crm' => '*',
                'notifications' => '*',
            ],
            $manifest->dependencyConstraints()
        );
    }

    public function test_it_removes_duplicate_legacy_dependencies(): void
    {
        $manifest = $this->manifest([
            'dependencies' => [
                'crm',
                'crm',
                'notifications',
            ],
        ]);

        self::assertSame(
            [
                'crm',
                'notifications',
            ],
            $manifest->dependencies()
        );

        self::assertSame(
            [
                'crm' => '*',
                'notifications' => '*',
            ],
            $manifest->dependencyConstraints()
        );
    }

    public function test_it_returns_versioned_dependency_slugs(): void
    {
        $manifest = $this->manifest([
            'dependencies' => [
                'crm' => '^2.0',
                'notifications' => '>=1.5 <2.0',
            ],
        ]);

        self::assertSame(
            [
                'crm',
                'notifications',
            ],
            $manifest->dependencies()
        );
    }

    public function test_it_returns_versioned_dependency_constraints(): void
    {
        $manifest = $this->manifest([
            'dependencies' => [
                'crm' => '^2.0',
                'notifications' => '>=1.5 <2.0',
            ],
        ]);

        self::assertSame(
            [
                'crm' => '^2.0',
                'notifications' => '>=1.5 <2.0',
            ],
            $manifest->dependencyConstraints()
        );
    }

    public function test_it_trims_dependency_names_and_constraints(): void
    {
        $manifest = $this->manifest([
            'dependencies' => [
                ' crm ' => ' ^2.0 ',
                ' notifications ' => ' >=1.5 <2.0 ',
            ],
        ]);

        self::assertSame(
            [
                'crm' => '^2.0',
                'notifications' => '>=1.5 <2.0',
            ],
            $manifest->dependencyConstraints()
        );
    }

    public function test_it_returns_empty_dependencies_when_not_defined(): void
    {
        $manifest = $this->manifest();

        self::assertSame(
            [],
            $manifest->dependencies()
        );

        self::assertSame(
            [],
            $manifest->dependencyConstraints()
        );
    }

    public function test_it_rejects_a_non_array_dependencies_field(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest field [dependencies] must be an array'
        );

        $this->manifest([
            'dependencies' => 'crm',
        ]);
    }

    public function test_it_rejects_an_empty_legacy_dependency(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest dependencies must contain non-empty strings'
        );

        $this->manifest([
            'dependencies' => [
                'crm',
                '',
            ],
        ]);
    }

    public function test_it_rejects_a_non_string_legacy_dependency(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest dependencies must contain non-empty strings'
        );

        $this->manifest([
            'dependencies' => [
                'crm',
                123,
            ],
        ]);
    }

    public function test_it_rejects_an_empty_versioned_dependency_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest dependency names must be non-empty strings'
        );

        $this->manifest([
            'dependencies' => [
                '' => '^1.0',
            ],
        ]);
    }

    public function test_it_rejects_an_empty_version_constraint(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest dependency [crm] must have a non-empty version constraint'
        );

        $this->manifest([
            'dependencies' => [
                'crm' => '',
            ],
        ]);
    }

    public function test_it_rejects_a_non_string_version_constraint(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest dependency [crm] must have a non-empty version constraint'
        );

        $this->manifest([
            'dependencies' => [
                'crm' => 2,
            ],
        ]);
    }

    public function test_it_preserves_the_original_manifest_data(): void
    {
        $data = [
            'name' => 'Reports',
            'slug' => 'reports',
            'version' => '2.1.0',
            'enabled' => true,
            'dependencies' => [
                'crm' => '^2.0',
            ],
        ];

        $manifest = new ModuleManifest(
            data: $data,
            path: '/modules/reports',
            manifestPath: '/modules/reports/module.json',
        );

        self::assertSame(
            $data,
            $manifest->toArray()
        );
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function manifest(
        array $overrides = []
    ): ModuleManifest {
        return new ModuleManifest(
            data: array_replace(
                [
                    'name' => 'Reports',
                    'slug' => 'reports',
                    'version' => '1.0.0',
                    'enabled' => true,
                ],
                $overrides
            ),
            path: '/modules/reports',
            manifestPath: '/modules/reports/module.json',
        );
    }
}