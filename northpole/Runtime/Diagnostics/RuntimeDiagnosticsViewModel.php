<?php

declare(strict_types=1);

namespace Northpole\Runtime\Diagnostics;

use Northpole\Runtime\Health\RuntimeHealthSummaryService;

final class RuntimeDiagnosticsViewModel
{
    public function __construct(
        private readonly RuntimeDiagnosticsService $diagnostics,
        private readonly RuntimeHealthSummaryService $healthSummary,
        private readonly RuntimeRegistryStatisticsService $registryStatistics,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        $diagnostics = $this->diagnostics->inspect();
        $healthSummary = $this->healthSummary->inspect();

        $runtimeHealth = $healthSummary['runtimeHealth'];
        $validationResult = $diagnostics['validationResult'];
        $repairResult = $diagnostics['repairResult'];

        return [
            'runtimeHealth' => $runtimeHealth,
            'validationResult' => $validationResult,
            'validationIssues' =>
                $validationResult->toArray(),
            'repairResult' => $repairResult,
            'repairRecommendations' =>
                $repairResult->toArray(),
            'repairSummary' => [
                'status' => $repairResult->isEmpty()
                    ? 'clear'
                    : 'recommended',
                'recommendations' =>
                    $repairResult->count(),
                'providers' =>
                    $diagnostics['repairs']['providers'],
            ],
            'validationSummary' => [
                'status' => $validationResult->passes()
                    ? 'passed'
                    : 'failed',
                'rules' =>
                    $diagnostics['validation']['rules'],
                'issues' =>
                    $validationResult->count(),
                'errors' =>
                    $validationResult->errorCount(),
                'warnings' =>
                    $validationResult->warningCount(),
                'information' =>
                    $validationResult->infoCount(),
            ],
            'summary' => [
                'status' => $runtimeHealth->status(),
                'score' => $runtimeHealth->score(),
                'modules' => count(
                    $runtimeHealth->modules(),
                ),
                'healthyModules' =>
                    $healthSummary['healthyModules'],
                'issues' =>
                    $healthSummary['issueCount'],
                'bootStages' =>
                    $diagnostics['registries']
                        ['counts']['boot_stages'],
            ],
            'registryCounts' =>
                $this->registryStatistics
                    ->diagnosticCards(),
            'moduleIssues' =>
                $healthSummary['issues'],
        ];
    }
}