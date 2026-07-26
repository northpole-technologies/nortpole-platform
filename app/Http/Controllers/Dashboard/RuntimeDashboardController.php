<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Northpole\Runtime\Diagnostics\RuntimeEnvironmentService;
use Northpole\Runtime\Diagnostics\RuntimeModuleStatisticsService;
use Northpole\Runtime\Diagnostics\RuntimeRegistryStatisticsService;
use Northpole\Runtime\Health\RuntimeHealthSummaryService;

final class RuntimeDashboardController extends Controller
{
    public function __construct(
        private readonly RuntimeEnvironmentService $environment,
        private readonly RuntimeModuleStatisticsService $moduleStatistics,
        private readonly RuntimeHealthSummaryService $healthSummary,
        private readonly RuntimeRegistryStatisticsService $registryStatistics,
    ) {}

    public function __invoke(): View
    {
        $healthSummary = $this->healthSummary->inspect();

        $runtimeHealth = $healthSummary['runtimeHealth'];
        $moduleHealth = $healthSummary['moduleHealthBySlug'];
        $environment = $this->environment->dashboardData();

        $modules = array_map(
            static function (
                array $module
            ) use (
                $moduleHealth
            ): array {
                $health = $moduleHealth[$module['slug']] ?? null;

                return [
                    ...$module,
                    'healthStatus' => $health?->status()
                        ?? 'unhealthy',
                    'healthScore' => $health?->score()
                        ?? 0,
                    'healthChecks' => $health?->checks()
                        ?? [],
                ];
            },
            $this->moduleStatistics->dashboardRows(),
        );

        return view(
            'dashboard.runtime',
            [
                'health' => strtoupper(
                    $runtimeHealth->status(),
                ),
                'runtimeHealth' => $runtimeHealth,
                'runtimeHealthSummary' => [
                    'score' => $runtimeHealth->score(),
                    'healthyModules' =>
                        $healthSummary['healthyModules'],
                    'issues' =>
                        $healthSummary['issueCount'],
                ],
                'environment' =>
                    $environment['environment'],
                'laravelVersion' =>
                    $environment['laravelVersion'],
                'phpVersion' =>
                    $environment['phpVersion'],
                'moduleSummary' =>
                    $this->moduleStatistics->summary(),
                'metrics' => $this->registryStatistics
                    ->metricCards(),
                'modules' => $modules,
            ],
        );
    }
}