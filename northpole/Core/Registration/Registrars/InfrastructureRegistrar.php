<?php

declare(strict_types=1);

namespace Northpole\Core\Registration\Registrars;

use Illuminate\Contracts\Foundation\Application;
use Northpole\Core\Registration\Contracts\ServiceRegistrar;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Graph\Contracts\RuntimeGraphBuilderContract;
use Northpole\Runtime\Graph\RuntimeGraphBuilder;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Runtime;
use Northpole\Runtime\Support\ApplicationAdapter;

final class InfrastructureRegistrar implements ServiceRegistrar
{
    public function register(
        Application $application
    ): void {
        $application->singleton(
            ApplicationAdapter::class,
            function (
                Application $application
            ): ApplicationAdapter {
                return new ApplicationAdapter(
                    $application
                );
            },
        );

        $application->singleton(
            ModuleRepository::class,
            function (): ModuleRepository {
                return new ModuleRepository;
            },
        );

        $application->singleton(
            ModuleFinder::class,
            function (): ModuleFinder {
                return new ModuleFinder;
            },
        );

        $application->singleton(
            ManifestLoader::class,
            function (): ManifestLoader {
                return new ManifestLoader;
            },
        );

        $application->singleton(
            ModuleDependencyResolver::class,
            function (): ModuleDependencyResolver {
                return new ModuleDependencyResolver;
            },
        );

        $application->singleton(
            ModuleDiscovery::class,
            function (
                Application $application
            ): ModuleDiscovery {
                return new ModuleDiscovery(
                    $application->make(
                        ModuleFinder::class
                    ),
                    $application->make(
                        ManifestLoader::class
                    ),
                    $application->make(
                        ModuleRepository::class
                    ),
                );
            },
        );

        $application->singleton(
            Runtime::class,
            function (
                Application $application
            ): Runtime {
                return new Runtime(
                    $application->make(
                        ModuleDiscovery::class
                    ),
                    $application->make(
                        ModuleRepository::class
                    ),
                    $application->make(
                        ModuleDependencyResolver::class
                    ),
                    $application->basePath(
                        'modules'
                    ),
                );
            },
        );

        $application->singleton(
            RuntimeGraphBuilderContract::class,
            RuntimeGraphBuilder::class,
        );
    }
}
