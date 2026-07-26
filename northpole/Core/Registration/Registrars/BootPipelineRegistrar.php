<?php

declare(strict_types=1);

namespace Northpole\Core\Registration\Registrars;

use Illuminate\Contracts\Foundation\Application;
use Northpole\Core\Registration\Contracts\ServiceRegistrar;
use Northpole\Runtime\Capabilities\CapabilityRegistry;
use Northpole\Runtime\Commands\ModuleCommandRegistrar;
use Northpole\Runtime\Configuration\ModuleConfigurationRegistrar;
use Northpole\Runtime\Events\ModuleEventRegistrar;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistrar;
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
use Northpole\Runtime\Navigation\NavigationRegistry;
use Northpole\Runtime\Notifications\ModuleNotificationRegistrar;
use Northpole\Runtime\Permissions\PermissionRegistry;
use Northpole\Runtime\Queries\ModuleQueryRegistrar;
use Northpole\Runtime\Roles\RoleDefinitionRegistry;
use Northpole\Runtime\Support\ApplicationAdapter;

final class BootPipelineRegistrar implements ServiceRegistrar
{
    public function register(
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
