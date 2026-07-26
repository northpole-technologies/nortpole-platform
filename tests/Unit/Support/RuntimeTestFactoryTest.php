<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Runtime;
use Tests\Support\RuntimeTestFactory;
use Tests\TestCase;

final class RuntimeTestFactoryTest extends TestCase
{
    public function test_it_creates_a_runtime_for_the_default_modules_directory(): void
    {
        $runtime = RuntimeTestFactory::runtime();

        self::assertInstanceOf(
            Runtime::class,
            $runtime,
        );

        self::assertSame(
            base_path('modules'),
            $runtime->modulesPath(),
        );
    }

    public function test_it_creates_a_runtime_for_a_custom_modules_directory(): void
    {
        $modulesPath = storage_path(
            'framework/testing/runtime-factory',
        );

        $runtime = RuntimeTestFactory::runtime(
            modulesPath: $modulesPath,
        );

        self::assertSame(
            $modulesPath,
            $runtime->modulesPath(),
        );
    }

    public function test_it_uses_a_supplied_module_repository(): void
    {
        $repository = new ModuleRepository;

        $runtime = RuntimeTestFactory::runtime(
            repository: $repository,
        );

        $repository->add(
            RuntimeTestFactory::manifest([
                'slug' => 'factory-module',
            ]),
        );

        self::assertTrue(
            $runtime->has('factory-module'),
        );

        self::assertSame(
            1,
            $runtime->count(),
        );
    }

    public function test_it_creates_a_default_module_manifest(): void
    {
        $manifest = RuntimeTestFactory::manifest();

        self::assertInstanceOf(
            ModuleManifest::class,
            $manifest,
        );

        self::assertSame(
            'Runtime Test',
            $manifest->name(),
        );

        self::assertSame(
            'runtime-test',
            $manifest->slug(),
        );

        self::assertSame(
            '1.0.0',
            $manifest->version(),
        );

        self::assertTrue(
            $manifest->enabled(),
        );

        self::assertSame(
            [],
            $manifest->dependencies(),
        );
    }

    public function test_it_applies_manifest_overrides(): void
    {
        $manifest = RuntimeTestFactory::manifest([
            'name' => 'Reports',
            'slug' => 'reports',
            'version' => '2.5.0',
            'enabled' => false,
            'dependencies' => [
                'crm',
            ],
        ]);

        self::assertSame(
            'Reports',
            $manifest->name(),
        );

        self::assertSame(
            'reports',
            $manifest->slug(),
        );

        self::assertSame(
            '2.5.0',
            $manifest->version(),
        );

        self::assertFalse(
            $manifest->enabled(),
        );

        self::assertSame(
            [
                'crm',
            ],
            $manifest->dependencies(),
        );

        self::assertSame(
            base_path('modules/Reports'),
            $manifest->path(),
        );
    }

    public function test_it_accepts_custom_manifest_paths(): void
    {
        $path = storage_path(
            'framework/testing/custom-module',
        );

        $manifestPath = $path
            .DIRECTORY_SEPARATOR
            .'custom-manifest.json';

        $manifest = RuntimeTestFactory::manifest(
            data: [
                'slug' => 'custom-module',
            ],
            path: $path,
            manifestPath: $manifestPath,
        );

        self::assertSame(
            $path,
            $manifest->path(),
        );

        self::assertSame(
            $manifestPath,
            $manifest->manifestPath(),
        );
    }
}