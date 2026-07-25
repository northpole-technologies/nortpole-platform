<?php

declare(strict_types=1);

namespace Northpole\Core;

use App\Support\Tenancy\TenantContext;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\ServiceProvider;
use Northpole\Console\Support\ModuleScaffolder;
use Northpole\Console\Support\StubWriter;
use Northpole\Core\Registration\RuntimeServiceRegistrar;
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
use Northpole\Runtime\Jobs\Laravel\LaravelScheduledJobAdapter;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistry;
use Northpole\Runtime\Lifecycle\BootPipeline;
use Northpole\Runtime\Runtime;

final class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        (new RuntimeServiceRegistrar)->register(
            $this->app
        );

        $this->registerLifecycleServices();
        $this->registerConsoleServices();
    }

    public function boot(
        Runtime $runtime,
        BootPipeline $pipeline,
        ModuleScheduledJobRegistry $scheduledJobs,
        LaravelScheduledJobAdapter $scheduledJobAdapter,
    ): void {
        $runtime->discover();

        $pipeline->boot(
            $runtime
        );

        $scheduledJobAdapter->register(
            $scheduledJobs
        );
    }

    private function registerLifecycleServices(): void
    {
        $this->app->singleton(
            LifecycleStageRegistry::class,
            function (
                Application $application
            ): LifecycleStageRegistry {
                return (new LifecycleStageRegistry)
                    ->registerMany([
                        new ResolveInstallationStage,
                        new ResolveManifestStage(
                            $application->make(
                                Runtime::class
                            ),
                        ),
                        new ValidateDependenciesStage(
                            $application->make(
                                Runtime::class
                            ),
                            $application->make(
                                TenantContext::class
                            ),
                        ),
                        $application->make(
                            InstallStage::class
                        ),
                        new EnableStage,
                        new DisableStage,
                        new UninstallStage,
                    ]);
            },
        );

        $this->app->singleton(
            LifecyclePipeline::class,
            function (
                Application $application
            ): LifecyclePipeline {
                return new LifecyclePipeline(
                    $application->make(
                        LifecycleStageRegistry::class
                    ),
                    $application->make(
                        ConnectionInterface::class
                    ),
                );
            },
        );

        $this->app->singleton(
            ModuleLifecycleManager::class
        );
    }

    private function registerConsoleServices(): void
    {
        $this->app->singleton(
            StubWriter::class,
            function (): StubWriter {
                return new StubWriter;
            },
        );

        $this->app->singleton(
            ModuleScaffolder::class,
            function (
                Application $application
            ): ModuleScaffolder {
                return new ModuleScaffolder(
                    stubWriter: $application->make(
                        StubWriter::class
                    ),
                    modulesPath: $application->basePath(
                        'modules'
                    ),
                    stubsPath: $application->basePath(
                        'northpole/Console/Stubs'
                    ),
                );
            },
        );
    }
}