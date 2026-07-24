<?php

declare(strict_types=1);

namespace Northpole\Core;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Northpole\Console\Support\ModuleScaffolder;
use Northpole\Console\Support\StubWriter;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Lifecycle\BootPipeline;
use Northpole\Runtime\Lifecycle\ConfigStage;
use Northpole\Runtime\Lifecycle\MigrationStage;
use Northpole\Runtime\Lifecycle\NavigationStage;
use Northpole\Runtime\Lifecycle\ProviderStage;
use Northpole\Runtime\Lifecycle\RouteStage;
use Northpole\Runtime\Lifecycle\ViewStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Navigation\NavigationRegistry;
use Northpole\Runtime\Runtime;
use Northpole\Runtime\Support\ApplicationAdapter;

final class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ApplicationAdapter::class,
            function (Application $application): ApplicationAdapter {
                return new ApplicationAdapter($application);
            },
        );

        $this->app->singleton(
            ModuleRepository::class,
            function (): ModuleRepository {
                return new ModuleRepository();
            },
        );

        $this->app->singleton(
            ModuleFinder::class,
            function (): ModuleFinder {
                return new ModuleFinder();
            },
        );

        $this->app->singleton(
            ManifestLoader::class,
            function (): ManifestLoader {
                return new ManifestLoader();
            },
        );

        $this->app->singleton(
            NavigationRegistry::class,
            function (): NavigationRegistry {
                return new NavigationRegistry();
            },
        );

        $this->app->singleton(
            ModuleDiscovery::class,
            function (Application $application): ModuleDiscovery {
                return new ModuleDiscovery(
                    $application->make(ModuleFinder::class),
                    $application->make(ManifestLoader::class),
                    $application->make(ModuleRepository::class),
                );
            },
        );

        $this->app->singleton(
            Runtime::class,
            function (Application $application): Runtime {
                return new Runtime(
                    $application->make(ModuleDiscovery::class),
                    $application->make(ModuleRepository::class),
                    $application->basePath('modules'),
                );
            },
        );

        $this->app->singleton(
            BootPipeline::class,
            function (Application $application): BootPipeline {
                $adapter = $application->make(
                    ApplicationAdapter::class,
                );

                return (new BootPipeline())->addMany([
                    new ConfigStage($adapter),
                    new ProviderStage($adapter),
                    new RouteStage($adapter),
                    new ViewStage($adapter),
                    new MigrationStage($adapter),
                    new NavigationStage(
                        $application->make(
                            NavigationRegistry::class,
                        )
                    ),
                ]);
            },
        );

        $this->app->singleton(
            StubWriter::class,
            function (): StubWriter {
                return new StubWriter();
            },
        );

        $this->app->singleton(
            ModuleScaffolder::class,
            function (Application $application): ModuleScaffolder {
                return new ModuleScaffolder(
                    stubWriter: $application->make(StubWriter::class),
                    modulesPath: $application->basePath('modules'),
                    stubsPath: $application->basePath(
                        'northpole/Console/Stubs',
                    ),
                );
            },
        );
    }

    public function boot(
        Runtime $runtime,
        BootPipeline $pipeline,
    ): void {
        $runtime->discover();

        $pipeline->boot($runtime);
    }
}