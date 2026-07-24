<?php

namespace Tests\Feature\Runtime;

use Illuminate\Database\Migrations\Migrator;
use Northpole\Runtime\Contracts\ModuleManifestContract;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Lifecycle\BootContext;
use Northpole\Runtime\Lifecycle\MigrationStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Runtime;
use Northpole\Runtime\Support\ApplicationAdapter;
use Tests\TestCase;

final class MigrationStageTest extends TestCase
{
    public function test_it_registers_a_module_migrations_directory(): void
    {
        $stage = new MigrationStage(
            new ApplicationAdapter($this->app)
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock(
                    'database/migrations'
                )
            )
        );

        $registeredPaths = array_map(
            fn (string $path): string => $this->normalisePath($path),
            $this->migrator()->paths()
        );

        $this->assertContains(
            $this->normalisePath(
                $this->fixtureMigrationsPath()
            ),
            $registeredPaths
        );
    }

    public function test_registered_module_migrations_are_discoverable(): void
    {
        $stage = new MigrationStage(
            new ApplicationAdapter($this->app)
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock(
                    'database/migrations'
                )
            )
        );

        $files = $this->migrator()->getMigrationFiles(
            $this->migrator()->paths()
        );

        $migrationName =
            '2026_01_01_000000_create_runtime_test_table';

        $this->assertArrayHasKey(
            $migrationName,
            $files
        );

        $expectedPath = $this->fixtureMigrationsPath()
            .DIRECTORY_SEPARATOR
            .$migrationName
            .'.php';

        $this->assertSame(
            $this->normalisePath($expectedPath),
            $this->normalisePath($files[$migrationName])
        );
    }

    public function test_it_skips_modules_without_migrations(): void
    {
        $pathsBeforeBoot = array_map(
            fn (string $path): string => $this->normalisePath($path),
            $this->migrator()->paths()
        );

        $stage = new MigrationStage(
            new ApplicationAdapter($this->app)
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock(null)
            )
        );

        $pathsAfterBoot = array_map(
            fn (string $path): string => $this->normalisePath($path),
            $this->migrator()->paths()
        );

        $this->assertSame(
            $pathsBeforeBoot,
            $pathsAfterBoot
        );
    }

    private function migrator(): Migrator
    {
        /** @var Migrator $migrator */
        $migrator = $this->app->make('migrator');

        return $migrator;
    }

    private function fixtureMigrationsPath(): string
    {
        return base_path(
            'tests/Fixtures/Runtime/database/migrations'
        );
    }

    private function normalisePath(string $path): string
    {
        return str_replace(
            '\\',
            '/',
            $path
        );
    }

    private function createManifestMock(
        ?string $migrationsPath
    ): ModuleManifestContract {
        $manifest = $this->createMock(
            ModuleManifestContract::class
        );

        $manifest
            ->method('slug')
            ->willReturn('runtime-test');

        $manifest
            ->method('path')
            ->willReturn(
                base_path('tests/Fixtures/Runtime')
            );

        $manifest
            ->expects($this->once())
            ->method('migrationsPath')
            ->willReturn($migrationsPath);

        return $manifest;
    }

    private function createRuntime(): Runtime
    {
        $repository = new ModuleRepository();

        return new Runtime(
            new ModuleDiscovery(
                new ModuleFinder(),
                new ManifestLoader(),
                $repository
            ),
            $repository,
            new ModuleDependencyResolver(),
            base_path('modules')
        );
    }
}
