<?php

declare(strict_types=1);

namespace Northpole\Runtime\Diagnostics;

use Northpole\Runtime\Health\RuntimeHealthSummaryService;

final class RuntimeDoctorViewModel
{
    public function __construct(
        private readonly RuntimeEnvironmentService $environment,
        private readonly RuntimeModuleStatisticsService $moduleStatistics,
        private readonly RuntimeHealthSummaryService $healthSummary,
        private readonly RuntimeRegistryStatisticsService $registryStatistics,
    ) {}

    /**
     * @return array{
     *     healthStatus: string,
     *     overallStatus: string,
     *     platformRows: array<int, array{0: string, 1: string}>,
     *     moduleSummaryRows: array<int, array{0: string, 1: int}>,
     *     registryRows: array<int, array{0: string, 1: int}>,
     *     moduleRows: array<int, array{
     *         0: string,
     *         1: string,
     *         2: string,
     *         3: string
     *     }>
     * }
     */
    public function data(): array
    {
        $healthSummary = $this->healthSummary->inspect();
        $moduleSummary = $this->moduleStatistics->summary();
        $moduleRows = $this->moduleStatistics->consoleRows();

        $healthStatus = strtoupper(
            $healthSummary['runtimeHealth']->status(),
        );

        return [
            'healthStatus' => $healthStatus,
            'overallStatus' => $moduleRows === []
                ? 'DEGRADED'
                : $healthStatus,
            'platformRows' =>
                $this->environment->consoleRows(),
            'moduleSummaryRows' => [
                [
                    'Discovered',
                    $moduleSummary['discovered'],
                ],
                [
                    'Enabled',
                    $moduleSummary['enabled'],
                ],
                [
                    'Disabled',
                    $moduleSummary['disabled'],
                ],
            ],
            'registryRows' =>
                $this->registryStatistics->consoleRows(),
            'moduleRows' => $moduleRows,
        ];
    }
}