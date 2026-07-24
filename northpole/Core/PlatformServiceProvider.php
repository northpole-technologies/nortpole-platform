<?php

declare(strict_types=1);

namespace Northpole\Core;

use App\Support\Tenancy\TenantContext;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\ServiceProvider;
use Northpole\Console\Support\ModuleScaffolder;
use Northpole\Console\Support\StubWriter;
use Northpole\Lifecycle\LifecyclePipeline;
use Northpole\Lifecycle\LifecycleStageRegistry;
use Northpole\Lifecycle\ModuleLifecycleManager;
use Northpole\Lifecycle\Stages\DisableStage;
use Northpole\Lifecycle\Stages\EnableStage;
use Northpole\Lifecycle\Stages\InstallStage;
use Northpole\Lifecycle\Stages\ResolveInstallationStage;
use Northpole\Lifecycle\Stages\ResolveManifestStage;
use Northpole\Lifecycle\Stages\UninstallStage;
use Northpole\Lifecycle\Stages\ValidateDependenciesStage;
use Northpole\Runtime\Capabilities\CapabilityRegistry;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Events\ModuleEventBus;
use Northpole\Runtime\Events\ModuleEventRegistry;
use Northpole\Runtime\Lifecycle\BootPipeline;
use Northpole\Runtime\Lifecycle\CapabilityStage;
use Northpole\Runtime\Lifecycle\ConfigStage;
use Northpole\Runtime\Lifecycle\MigrationStage;
use Northpole\Runtime\Lifecycle\NavigationStage;
use Northpole\Runtime\Lifecycle\PermissionStage;
use Northpole\Runtime\Lifecycle\ProviderStage;
use Northpole\Runtime\Lifecycle\RouteStage;
use Northpole\Runtime\Lifecycle\StageRegistry;
use Northpole\Runtime\Lifecycle\ViewStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Navigation\NavigationRegistry;
use Northpole\Runtime\Permissions\PermissionRegistry;
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
            ModuleDependencyResolver::class,
            function (): ModuleDependencyResolver {
                return new ModuleDependencyResolver();
            },
        );

        $this->app->singleton(
            CapabilityRegistry::class,
            function (): CapabilityRegistry {
                return new CapabilityRegistry();
            },
        );

        $this->app->singleton(
            PermissionRegistry::class,
            function (): PermissionRegistry {
                return new PermissionRegistry();
            },
        );

        $this->app->singleton(
            NavigationRegistry::class,
            function (): NavigationRegistry {
                return new NavigationRegistry();
            },
        );

        $this->app->singleton(
            ModuleEventRegistry::class,
            function (): ModuleEventRegistry {
                return new ModuleEventRegistry();
            },
        );

        $this->app->singleton(
            ModuleEventBus::class,
            function (Application $application): ModuleEventBus {
                return new ModuleEventBus(
                    registry: $application->make(
                        ModuleEventRegistry::class,
                    ),
                    listenerResolver: static function (
                        string $listener
                    ) use ($application): object {
                        return $application->make($listener);
                    },
                );
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
                    $application->make(ModuleDependencyResolver::class),
                    $application->basePath('modules'),
                );
            },
        );

        $this->app->singleton(
            StageRegistry::class,
            function (Application $application): StageRegistry {
                $adapter = $application->make(
                    ApplicationAdapter::class,
                );

                return (new StageRegistry())->registerMany([
                    new ConfigStage($adapter),
                    new ProviderStage($adapter),
                    new RouteStage($adapter),
                    new ViewStage($adapter),
                    new MigrationStage($adapter),
                    new CapabilityStage(
                        $application->make(
                            CapabilityRegistry::class,
                        ),
                    ),
                    new PermissionStage(
                        $application->make(
                            PermissionRegistry::class,
                        ),
                    ),
                    new NavigationStage(
                        $application->make(
                            NavigationRegistry::class,
                        ),
                    ),
                ]);
            },
        );

        $this->app->singleton(
            BootPipeline::class,
            function (Application $application): BootPipeline {
                return new BootPipeline(
                    $application->make(StageRegistry::class),
                );
            },
        );

        $this->app->singleton(
            LifecycleStageRegistry::class,
            function (
                Application $application
            ): LifecycleStageRegistry {
                return (new LifecycleStageRegistry())
                    ->registerMany([
                        new ResolveInstallationStage(),
                        new ResolveManifestStage(
                            $application->make(Runtime::class),
                        ),
                        new ValidateDependenciesStage(
                            $application->make(Runtime::class),
                            $application->make(TenantContext::class),
                        ),
                        new InstallStage(),
                        new EnableStage(),
                        new DisableStage(),
                        new UninstallStage(),
                    ]);
            },
        );

        $this->app->singleton(
            LifecyclePipeline::class,
            function (Application $application): LifecyclePipeline {
                return new LifecyclePipeline(
                    $application->make(
                        LifecycleStageRegistry::class,
                    ),
                    $application->make(
                        ConnectionInterface::class,
                    ),
                );
            },
        );

        $this->app->singleton(
            ModuleLifecycleManager::class,
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