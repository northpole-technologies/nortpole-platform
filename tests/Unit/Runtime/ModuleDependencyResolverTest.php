<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime;

use Northpole\Runtime\Exceptions\ModuleDependencyException;
use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use PHPUnit\Framework\TestCase;

final class ModuleDependencyResolverTest extends TestCase
{
    public function test_it_returns_enabled_modules_without_dependencies(): void
    {
        $resolver = new ModuleDependencyResolver();

        $resolved = $resolver->resolve([
            'crm' => $this->module('crm'),
            'finance' => $this->module('finance'),
        ]);

        self::assertSame(
            ['crm', 'finance'],
            array_keys($resolved),
        );
    }

    public function test_it_excludes_disabled_modules(): void
    {
        $resolver = new ModuleDependencyResolver();

        $resolved = $resolver->resolve([
            'crm' => $this->module('crm'),
            'finance' => $this->module(
                slug: 'finance',
                enabled: false,
            ),
        ]);

        self::assertSame(
            ['crm'],
            array_keys($resolved),
        );
    }

    public function test_it_places_dependencies_before_dependants(): void
    {
        $resolver = new ModuleDependencyResolver();

        $resolved = $resolver->resolve([
            'accounting' => $this->module(
                slug: 'accounting',
                dependencies: ['crm'],
            ),
            'crm' => $this->module(
                slug: 'crm',
                dependencies: ['core'],
            ),
            'core' => $this->module('core'),
        ]);

        self::assertSame(
            ['core', 'crm', 'accounting'],
            array_keys($resolved),
        );
    }

    public function test_it_resolves_shared_dependencies_once(): void
    {
        $resolver = new ModuleDependencyResolver();

        $resolved = $resolver->resolve([
            'sales' => $this->module(
                slug: 'sales',
                dependencies: ['crm'],
            ),
            'support' => $this->module(
                slug: 'support',
                dependencies: ['crm'],
            ),
            'crm' => $this->module('crm'),
        ]);

        self::assertSame(
            ['crm', 'sales', 'support'],
            array_keys($resolved),
        );

        self::assertCount(3, $resolved);
    }

    public function test_it_rejects_a_missing_dependency(): void
    {
        $resolver = new ModuleDependencyResolver();

        $this->expectException(
            ModuleDependencyException::class,
        );

        $this->expectExceptionMessage(
            'Module [crm] requires missing dependency [core].',
        );

        $resolver->resolve([
            'crm' => $this->module(
                slug: 'crm',
                dependencies: ['core'],
            ),
        ]);
    }

    public function test_it_rejects_a_disabled_dependency(): void
    {
        $resolver = new ModuleDependencyResolver();

        $this->expectException(
            ModuleDependencyException::class,
        );

        $this->expectExceptionMessage(
            'Module [crm] requires disabled dependency [core].',
        );

        $resolver->resolve([
            'crm' => $this->module(
                slug: 'crm',
                dependencies: ['core'],
            ),
            'core' => $this->module(
                slug: 'core',
                enabled: false,
            ),
        ]);
    }

    public function test_it_rejects_a_direct_circular_dependency(): void
    {
        $resolver = new ModuleDependencyResolver();

        $this->expectException(
            ModuleDependencyException::class,
        );

        $this->expectExceptionMessage(
            'Circular module dependency detected: crm -> crm.',
        );

        $resolver->resolve([
            'crm' => $this->module(
                slug: 'crm',
                dependencies: ['crm'],
            ),
        ]);
    }

    public function test_it_rejects_an_indirect_circular_dependency(): void
    {
        $resolver = new ModuleDependencyResolver();

        $this->expectException(
            ModuleDependencyException::class,
        );

        $this->expectExceptionMessage(
            'Circular module dependency detected: crm -> sales -> reports -> crm.',
        );

        $resolver->resolve([
            'crm' => $this->module(
                slug: 'crm',
                dependencies: ['sales'],
            ),
            'sales' => $this->module(
                slug: 'sales',
                dependencies: ['reports'],
            ),
            'reports' => $this->module(
                slug: 'reports',
                dependencies: ['crm'],
            ),
        ]);
    }

    /**
     * @param array<int, string> $dependencies
     */
    private function module(
        string $slug,
        array $dependencies = [],
        bool $enabled = true,
    ): ModuleManifest {
        return new ModuleManifest(
            data: [
                'name' => ucfirst($slug),
                'slug' => $slug,
                'version' => '1.0.0',
                'enabled' => $enabled,
                'dependencies' => $dependencies,
            ],
            path: "/modules/{$slug}",
            manifestPath: "/modules/{$slug}/module.json",
        );
    }
}