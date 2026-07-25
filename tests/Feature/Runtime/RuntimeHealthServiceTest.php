<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use Northpole\Runtime\Health\RuntimeHealthService;
use Northpole\Runtime\Manifest\ModuleManifest;
use Tests\TestCase;

final class RuntimeHealthServiceTest extends TestCase
{
    public function test_runtime_health_service_is_a_singleton(): void
    {
        $this->assertSame(
            app(RuntimeHealthService::class),
            app(RuntimeHealthService::class),
        );
    }

    public function test_an_enabled_module_with_available_dependencies_is_healthy(): void
    {
        $directory = $this->moduleDirectory(
            'healthy-module',
        );

        $dependencyDirectory = $this->moduleDirectory(
            'dependency-module',
        );

        $module = new ModuleManifest(
            data: [
                'name' => 'Healthy Module',
                'slug' => 'healthy-module',
                'version' => '1.0.0',
                'enabled' => true,
                'dependencies' => [
                    'dependency-module',
                ],
            ],
            path: $directory,
            manifestPath: $directory.DIRECTORY_SEPARATOR.'module.json',
        );

        $dependency = new ModuleManifest(
            data: [
                'name' => 'Dependency Module',
                'slug' => 'dependency-module',
                'version' => '1.0.0',
                'enabled' => true,
            ],
            path: $dependencyDirectory,
            manifestPath: $dependencyDirectory
                .DIRECTORY_SEPARATOR
                .'module.json',
        );

        $health = app(RuntimeHealthService::class)->assess([
            $module->slug() => $module,
            $dependency->slug() => $dependency,
        ]);

        $this->assertSame(
            'healthy',
            $health->status(),
        );

        $this->assertSame(
            100,
            $health->score(),
        );

        $this->assertCount(
            2,
            $health->modules(),
        );
    }

    public function test_a_missing_dependency_degrades_module_health(): void
    {
        $directory = $this->moduleDirectory(
            'dependent-module',
        );

        $module = new ModuleManifest(
            data: [
                'name' => 'Dependent Module',
                'slug' => 'dependent-module',
                'version' => '1.0.0',
                'enabled' => true,
                'dependencies' => [
                    'missing-module',
                ],
            ],
            path: $directory,
            manifestPath: $directory.DIRECTORY_SEPARATOR.'module.json',
        );

        $health = app(RuntimeHealthService::class)->assess([
            $module->slug() => $module,
        ]);

        $this->assertSame(
            'degraded',
            $health->status(),
        );

        $this->assertSame(
            75,
            $health->score(),
        );

        $moduleHealth = $health->modules()[0];

        $this->assertSame(
            'degraded',
            $moduleHealth->status(),
        );

        $dependencyCheck = collect(
            $moduleHealth->checks(),
        )->firstWhere(
            'key',
            'dependencies',
        );

        $this->assertFalse(
            $dependencyCheck['healthy'],
        );

        $this->assertSame(
            'Missing dependencies: missing-module.',
            $dependencyCheck['message'],
        );
    }

    private function moduleDirectory(
        string $slug,
    ): string {
        $directory = storage_path(
            'framework/testing/runtime-health/'.$slug,
        );

        if (! is_dir($directory)) {
            mkdir(
                $directory,
                0777,
                true,
            );
        }

        return $directory;
    }
}