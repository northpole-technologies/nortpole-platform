<?php

declare(strict_types=1);

namespace Northpole\Runtime\Providers;

use Illuminate\Support\ServiceProvider;
use Northpole\Runtime\Diagnostics\RuntimeDashboardViewModel;
use Northpole\Runtime\Diagnostics\RuntimeDiagnosticsService;
use Northpole\Runtime\Diagnostics\RuntimeDiagnosticsViewModel;
use Northpole\Runtime\Diagnostics\RuntimeDoctorViewModel;
use Northpole\Runtime\Diagnostics\RuntimeEnvironmentService;
use Northpole\Runtime\Diagnostics\RuntimeModuleStatisticsService;
use Northpole\Runtime\Diagnostics\RuntimeRegistryStatisticsService;
use Northpole\Runtime\Health\RuntimeHealthSummaryService;
use Northpole\Runtime\Inspection\Contracts\RuntimeInspectionServiceContract;
use Northpole\Runtime\Inspection\RuntimeInspectionService;

final class NorthpoleRuntimeServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $singletons = [
        RuntimeDiagnosticsService::class => RuntimeDiagnosticsService::class,
        RuntimeEnvironmentService::class => RuntimeEnvironmentService::class,
        RuntimeModuleStatisticsService::class => RuntimeModuleStatisticsService::class,
        RuntimeRegistryStatisticsService::class => RuntimeRegistryStatisticsService::class,
        RuntimeHealthSummaryService::class => RuntimeHealthSummaryService::class,
        RuntimeDashboardViewModel::class => RuntimeDashboardViewModel::class,
        RuntimeDiagnosticsViewModel::class => RuntimeDiagnosticsViewModel::class,
        RuntimeDoctorViewModel::class => RuntimeDoctorViewModel::class,
        RuntimeInspectionService::class => RuntimeInspectionService::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->alias(
            RuntimeInspectionService::class,
            RuntimeInspectionServiceContract::class,
        );
    }
}
