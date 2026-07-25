<?php

declare(strict_types=1);

namespace Northpole\Core\Registration;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Northpole\Runtime\Capabilities\CapabilityRegistry;
use Northpole\Runtime\Commands\ModuleCommandBus;
use Northpole\Runtime\Commands\ModuleCommandRegistrar;
use Northpole\Runtime\Commands\ModuleCommandRegistry;
use Northpole\Runtime\Configuration\ModuleConfigurationRegistrar;
use Northpole\Runtime\Configuration\ModuleConfigurationRegistry;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Events\ModuleEventBus;
use Northpole\Runtime\Events\ModuleEventRegistrar;
use Northpole\Runtime\Events\ModuleEventRegistry;
use Northpole\Runtime\Events\Subscribers\ModuleInstalledSubscriber;
use Northpole\Runtime\Health\RuntimeHealthService;
use Northpole\Runtime\Jobs\Laravel\LaravelScheduledJobAdapter;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistrar;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistry;
use Northpole\Runtime\Lifecycle\BootPipeline;
use Northpole\Runtime\Lifecycle\CapabilityStage;
use Northpole\Runtime\Lifecycle\CommandHandlerStage;
use Northpole\Runtime\Lifecycle\ConfigStage;
use Northpole\Runtime\Lifecycle\ConfigurationStage;
use Northpole\Runtime\Lifecycle\EventSubscriberStage;
use Northpole\Runtime\Lifecycle\MigrationStage;
use Northpole\Runtime\Lifecycle\NavigationStage;
use Northpole\Runtime\Lifecycle\NotificationStage;
use Northpole\Runtime\Lifecycle\PermissionStage;
use Northpole\Runtime\Lifecycle\ProviderStage;
use Northpole\Runtime\Lifecycle\QueryHandlerStage;
use Northpole\Runtime\Lifecycle\RoleDefinitionStage;
use Northpole\Runtime\Lifecycle\RouteStage;
use Northpole\Runtime\Lifecycle\ScheduledJobStage;
use Northpole\Runtime\Lifecycle\StageRegistry;
use Northpole\Runtime\Lifecycle\ViewStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Navigation\NavigationRegistry;
use Northpole\Runtime\Notifications\ModuleNotificationRegistrar;
use Northpole\Runtime\Notifications\ModuleNotificationRegistry;
use Northpole\Runtime\Permissions\PermissionRegistry;
use Northpole\Runtime\Queries\ModuleQueryBus;
use Northpole\Runtime\Queries\ModuleQueryRegistrar;
use Northpole\Runtime\Queries\ModuleQueryRegistry;
use Northpole\Runtime\Repair\Providers\CommandRepairProvider;
use Northpole\Runtime\Repair\Providers\QueryRepairProvider;
use Northpole\Runtime\Repair\RuntimeRepairEngine;
use Northpole\Runtime\Roles\RoleDefinitionRegistry;
use Northpole\Runtime\Runtime;
use Northpole\Runtime\Support\ApplicationAdapter;
use Northpole\Runtime\Synchronisation\TenantAccessSynchroniser;
use Northpole\Runtime\Validation\Rules\CommandHandlerValidationRule;
use Northpole\Runtime\Validation\Rules\QueryHandlerValidationRule;
use Northpole\Runtime\Validation\RuntimeValidationEngine;

final class RuntimeServiceRegistrar
{
    public function register(
        Application $application
    ): void {
        $this->registerApplicationAdapter(
            $application
        );

        $this->registerModuleInfrastructure(
            $application
        );

        $this->registerFeatureRegistries(
            $application
        );

        $this->registerEvents(
            $application
        );

        $this->registerCommands(
            $application
        );

        $this->registerQueries(
            $application
        );

        $this->registerConfiguration(
            $application
        );

        $this->registerNotifications(
            $application
        );

        $this->registerScheduledJobs(
            $application
        );

        $this->registerRuntime(
            $application
        );

        $this->registerRuntimeValidation(
            $application
        );

        $this->registerRuntimeRepair(
            $application
        );

        $this->registerRuntimeHealth(
            $application
        );

        $this->registerBootPipeline(
            $application
        );
    }

    private function registerApplicationAdapter(
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
    }

    private function registerModuleInfrastructure(
        Application $application
    ): void {
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
    }

    private function registerFeatureRegistries(
        Application $application
    ): void {
        $application->singleton(
            CapabilityRegistry::class,
            function (): CapabilityRegistry {
                return new CapabilityRegistry;
            },
        );

        $application->singleton(
            PermissionRegistry::class,
            function (): PermissionRegistry {
                return new PermissionRegistry;
            },
        );

        $application->singleton(
            RoleDefinitionRegistry::class,
            function (): RoleDefinitionRegistry {
                return new RoleDefinitionRegistry;
            },
        );

        $application->singleton(
            TenantAccessSynchroniser::class,
            function (
                Application $application
            ): TenantAccessSynchroniser {
                return new TenantAccessSynchroniser(
                    permissions: $application->make(
                        PermissionRegistry::class
                    ),
                    roles: $application->make(
                        RoleDefinitionRegistry::class
                    ),
                );
            },
        );

        $application->singleton(
            NavigationRegistry::class,
            function (): NavigationRegistry {
                return new NavigationRegistry;
            },
        );
    }

    private function registerEvents(
        Application $application
    ): void {
        $application->singleton(
            ModuleEventRegistry::class,
            function (): ModuleEventRegistry {
                return new ModuleEventRegistry;
            },
        );

        $application->afterResolving(
            ModuleEventRegistry::class,
            function (
                ModuleEventRegistry $registry
            ): void {
                $registry->listen(
                    eventName: 'module.installed',
                    listener: ModuleInstalledSubscriber::class,
                    module: 'platform',
                );
            },
        );

        $application->singleton(
            ModuleEventRegistrar::class,
            function (
                Application $application
            ): ModuleEventRegistrar {
                return new ModuleEventRegistrar(
                    $application->make(
                        ModuleEventRegistry::class
                    ),
                );
            },
        );

        $application->singleton(
            ModuleEventBus::class,
            function (
                Application $application
            ): ModuleEventBus {
                return new ModuleEventBus(
                    registry: $application->make(
                        ModuleEventRegistry::class
                    ),
                    listenerResolver: static function (
                        string $listener
                    ) use ($application): object {
                        return $application->make(
                            $listener
                        );
                    },
                );
            },
        );
    }

    private function registerCommands(
        Application $application
    ): void {
        $application->singleton(
            ModuleCommandRegistry::class,
            function (): ModuleCommandRegistry {
                return new ModuleCommandRegistry;
            },
        );

        $application->singleton(
            ModuleCommandRegistrar::class,
            function (
                Application $application
            ): ModuleCommandRegistrar {
                return new ModuleCommandRegistrar(
                    $application->make(
                        ModuleCommandRegistry::class
                    ),
                );
            },
        );

        $application->singleton(
            ModuleCommandBus::class,
            function (
                Application $application
            ): ModuleCommandBus {
                return new ModuleCommandBus(
                    registry: $application->make(
                        ModuleCommandRegistry::class
                    ),
                    handlerResolver: static function (
                        string $handler
                    ) use ($application): object {
                        return $application->make(
                            $handler
                        );
                    },
                );
            },
        );
    }

    private function registerQueries(
        Application $application
    ): void {
        $application->singleton(
            ModuleQueryRegistry::class,
            function (): ModuleQueryRegistry {
                return new ModuleQueryRegistry;
            },
        );

        $application->singleton(
            ModuleQueryRegistrar::class,
            function (
                Application $application
            ): ModuleQueryRegistrar {
                return new ModuleQueryRegistrar(
                    $application->make(
                        ModuleQueryRegistry::class
                    ),
                );
            },
        );

        $application->singleton(
            ModuleQueryBus::class,
            function (
                Application $application
            ): ModuleQueryBus {
                return new ModuleQueryBus(
                    registry: $application->make(
                        ModuleQueryRegistry::class
                    ),
                    handlerResolver: static function (
                        string $handler
                    ) use ($application): object {
                        return $application->make(
                            $handler
                        );
                    },
                );
            },
        );
    }

    private function registerConfiguration(
        Application $application
    ): void {
        $application->singleton(
            ModuleConfigurationRegistry::class,
            function (): ModuleConfigurationRegistry {
                return new ModuleConfigurationRegistry;
            },
        );

        $application->singleton(
            ModuleConfigurationRegistrar::class,
            function (
                Application $application
            ): ModuleConfigurationRegistrar {
                return new ModuleConfigurationRegistrar(
                    $application->make(
                        ModuleConfigurationRegistry::class
                    ),
                );
            },
        );
    }

    private function registerNotifications(
        Application $application
    ): void {
        $application->singleton(
            ModuleNotificationRegistry::class,
            function (): ModuleNotificationRegistry {
                return new ModuleNotificationRegistry;
            },
        );

        $application->singleton(
            ModuleNotificationRegistrar::class,
            function (
                Application $application
            ): ModuleNotificationRegistrar {
                return new ModuleNotificationRegistrar(
                    $application->make(
                        ModuleNotificationRegistry::class
                    ),
                );
            },
        );
    }

    private function registerScheduledJobs(
        Application $application
    ): void {
        $application->singleton(
            ModuleScheduledJobRegistry::class,
            function (): ModuleScheduledJobRegistry {
                return new ModuleScheduledJobRegistry;
            },
        );

        $application->singleton(
            ModuleScheduledJobRegistrar::class,
            function (
                Application $application
            ): ModuleScheduledJobRegistrar {
                return new ModuleScheduledJobRegistrar(
                    $application->make(
                        ModuleScheduledJobRegistry::class
                    ),
                );
            },
        );

        $application->singleton(
            LaravelScheduledJobAdapter::class,
            function (
                Application $application
            ): LaravelScheduledJobAdapter {
                return new LaravelScheduledJobAdapter(
                    application: $application,
                    schedule: $application->make(
                        Schedule::class
                    ),
                );
            },
        );
    }

    private function registerRuntime(
        Application $application
    ): void {
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
    }

    private function registerRuntimeValidation(
        Application $application
    ): void {
        $application->singleton(
            RuntimeValidationEngine::class,
            function (
                Application $application
            ): RuntimeValidationEngine {
                $modulesResolver = static function () use (
                    $application
                ): array {
                    return $application->make(
                        Runtime::class
                    )->modules();
                };

                return (new RuntimeValidationEngine)
                    ->registerMany([
                        new CommandHandlerValidationRule(
                            modulesResolver: $modulesResolver,
                            registry: $application->make(
                                ModuleCommandRegistry::class
                            ),
                        ),
                        new QueryHandlerValidationRule(
                            modulesResolver: $modulesResolver,
                            registry: $application->make(
                                ModuleQueryRegistry::class
                            ),
                        ),
                    ]);
            },
        );
    }

    private function registerRuntimeRepair(
        Application $application
    ): void {
        $application->singleton(
            RuntimeRepairEngine::class,
            function (): RuntimeRepairEngine {
                return (new RuntimeRepairEngine)
                    ->registerProviders([
                        new CommandRepairProvider,
                        new QueryRepairProvider,
                    ]);
            },
        );
    }

    private function registerRuntimeHealth(
        Application $application
    ): void {
        $application->singleton(
            RuntimeHealthService::class,
            function (
                Application $application
            ): RuntimeHealthService {
                return new RuntimeHealthService(
                    $application->make(
                        Runtime::class
                    ),
                );
            },
        );
    }

    private function registerBootPipeline(
        Application $application
    ): void {
        $application->singleton(
            StageRegistry::class,
            function (
                Application $application
            ): StageRegistry {
                $adapter = $application->make(
                    ApplicationAdapter::class
                );

                return (new StageRegistry)
                    ->registerMany([
                        new ConfigStage(
                            $adapter
                        ),
                        new ProviderStage(
                            $adapter
                        ),
                        new RouteStage(
                            $adapter
                        ),
                        new ViewStage(
                            $adapter
                        ),
                        new MigrationStage(
                            $adapter
                        ),
                        new CapabilityStage(
                            $application->make(
                                CapabilityRegistry::class
                            ),
                        ),
                        new PermissionStage(
                            $application->make(
                                PermissionRegistry::class
                            ),
                        ),
                        new RoleDefinitionStage(
                            $application->make(
                                RoleDefinitionRegistry::class
                            ),
                        ),
                        new NavigationStage(
                            $application->make(
                                NavigationRegistry::class
                            ),
                        ),
                        new EventSubscriberStage(
                            $application->make(
                                ModuleEventRegistrar::class
                            ),
                        ),
                        new ConfigurationStage(
                            $application->make(
                                ModuleConfigurationRegistrar::class
                            ),
                        ),
                        new NotificationStage(
                            $application->make(
                                ModuleNotificationRegistrar::class
                            ),
                        ),
                        new ScheduledJobStage(
                            $application->make(
                                ModuleScheduledJobRegistrar::class
                            ),
                        ),
                        new CommandHandlerStage(
                            $application->make(
                                ModuleCommandRegistrar::class
                            ),
                        ),
                        new QueryHandlerStage(
                            $application->make(
                                ModuleQueryRegistrar::class
                            ),
                        ),
                    ]);
            },
        );

        $application->singleton(
            BootPipeline::class,
            function (
                Application $application
            ): BootPipeline {
                return new BootPipeline(
                    $application->make(
                        StageRegistry::class
                    ),
                );
            },
        );
    }
}
