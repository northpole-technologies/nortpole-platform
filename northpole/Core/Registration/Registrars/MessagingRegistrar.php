<?php

declare(strict_types=1);

namespace Northpole\Core\Registration\Registrars;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Northpole\Core\Registration\Contracts\ServiceRegistrar;
use Northpole\Runtime\Agents\ModuleAgentBus;
use Northpole\Runtime\Agents\ModuleAgentRegistrar;
use Northpole\Runtime\Agents\ModuleAgentRegistry;
use Northpole\Runtime\Commands\ModuleCommandBus;
use Northpole\Runtime\Commands\ModuleCommandRegistrar;
use Northpole\Runtime\Commands\ModuleCommandRegistry;
use Northpole\Runtime\Configuration\ModuleConfigurationRegistrar;
use Northpole\Runtime\Configuration\ModuleConfigurationRegistry;
use Northpole\Runtime\Events\ModuleEventBus;
use Northpole\Runtime\Events\ModuleEventRegistrar;
use Northpole\Runtime\Events\ModuleEventRegistry;
use Northpole\Runtime\Events\Subscribers\ModuleInstalledSubscriber;
use Northpole\Runtime\Jobs\Laravel\LaravelScheduledJobAdapter;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistrar;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistry;
use Northpole\Runtime\Notifications\ModuleNotificationBus;
use Northpole\Runtime\Notifications\ModuleNotificationRegistrar;
use Northpole\Runtime\Notifications\ModuleNotificationRegistry;
use Northpole\Runtime\Queries\ModuleQueryBus;
use Northpole\Runtime\Queries\ModuleQueryRegistrar;
use Northpole\Runtime\Queries\ModuleQueryRegistry;

final class MessagingRegistrar implements ServiceRegistrar
{
    public function register(
        Application $application
    ): void {
        $this->registerEvents(
            $application
        );

        $this->registerCommands(
            $application
        );

        $this->registerQueries(
            $application
        );

        $this->registerAgents(
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

    private function registerAgents(
        Application $application
    ): void {
        $application->singleton(
            ModuleAgentRegistry::class,
            function (): ModuleAgentRegistry {
                return new ModuleAgentRegistry;
            },
        );

        $application->singleton(
            ModuleAgentRegistrar::class,
            function (
                Application $application
            ): ModuleAgentRegistrar {
                return new ModuleAgentRegistrar(
                    $application->make(
                        ModuleAgentRegistry::class
                    ),
                );
            },
        );

        $application->singleton(
            ModuleAgentBus::class,
            function (
                Application $application
            ): ModuleAgentBus {
                return new ModuleAgentBus(
                    registry: $application->make(
                        ModuleAgentRegistry::class
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

        $application->singleton(
            ModuleNotificationBus::class,
            function (
                Application $application
            ): ModuleNotificationBus {
                return new ModuleNotificationBus(
                    registry: $application->make(
                        ModuleNotificationRegistry::class
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
}
