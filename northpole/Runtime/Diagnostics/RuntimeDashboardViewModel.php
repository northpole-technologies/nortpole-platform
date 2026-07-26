<?php

declare(strict_types=1);

namespace Northpole\Runtime\Diagnostics;

use Northpole\Runtime\Health\RuntimeHealthSummaryService;

final class RuntimeDashboardViewModel
{
    public function __construct(
        private readonly RuntimeEnvironmentService $environment,
        private readonly RuntimeModuleStatisticsService $moduleStatistics,
        private readonly RuntimeHealthSummaryService $healthSummary,
        private readonly RuntimeRegistryStatisticsService $registryStatistics,
    ) {}

    /**
     * @return array{
     *     health: string,
     *     runtimeHealth: mixed,
     *     runtimeHealthSummary: array{
     *         score: int,
     *         healthyModules: int,
     *         issues: int
     *     },
     *     environment: string,
     *     laravelVersion: string,
     *     phpVersion: string,
     *     moduleSummary: array{
     *         discovered: int,
     *         enabled: int,
     *         disabled: int
     *     },
     *     metrics: array<int, array<string, mixed>>,
     *     modules: array<int, array<string, mixed>>
     * }
     */
    public function data(): array
    {
        $healthSummary = $this->healthSummary->inspect();
        $environment = $this->environment->dashboardData();

        $runtimeHealth = $healthSummary['runtimeHealth'];
        $moduleHealth = $healthSummary['moduleHealthBySlug'];

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

        return [
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
            'metrics' =>
                $this->registryStatistics->metricCards(),
            'modules' => $modules,
        ];
    }
}