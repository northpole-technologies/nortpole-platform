<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Northpole\Runtime\Diagnostics\RuntimeDiagnosticsService;
use Northpole\Runtime\Diagnostics\RuntimeRegistryStatisticsService;
use Northpole\Runtime\Health\ModuleHealth;

final class RuntimeDiagnosticsController extends Controller
{
    public function __construct(
        private readonly RuntimeDiagnosticsService $diagnostics,
        private readonly RuntimeRegistryStatisticsService $registryStatistics,
    ) {}

    public function __invoke(): View
    {
        $diagnostics = $this->diagnostics->inspect();

        $runtimeHealth = $diagnostics['runtimeHealth'];
        $validationResult = $diagnostics['validationResult'];
        $repairResult = $diagnostics['repairResult'];

        $moduleIssues = [];

        foreach ($runtimeHealth->modules() as $moduleHealth) {
            foreach ($moduleHealth->checks() as $check) {
                if ($check['healthy']) {
                    continue;
                }

                $moduleIssues[] = [
                    'module' => $moduleHealth->slug(),
                    'check' => $check['name']
                        ?? 'Runtime check',
                    'message' => $check['message']
                        ?? 'The runtime check did not pass.',
                ];
            }
        }

        $healthyModules = count(
            array_filter(
                $runtimeHealth->modules(),
                static fn (ModuleHealth $health): bool =>
                    $health->status() === 'healthy',
            ),
        );

        $registryCounts = $this->registryStatistics
            ->diagnosticCards();

        return view(
            'dashboard.diagnostics',
            [
                'runtimeHealth' => $runtimeHealth,
                'validationResult' => $validationResult,
                'validationIssues' => $validationResult->toArray(),
                'repairResult' => $repairResult,
                'repairRecommendations' => $repairResult->toArray(),
                'repairSummary' => [
                    'status' => $repairResult->isEmpty()
                        ? 'clear'
                        : 'recommended',
                    'recommendations' => $repairResult->count(),
                    'providers' => $diagnostics['repairs']['providers'],
                ],
                'validationSummary' => [
                    'status' => $validationResult->passes()
                        ? 'passed'
                        : 'failed',
                    'rules' => $diagnostics['validation']['rules'],
                    'issues' => $validationResult->count(),
                    'errors' => $validationResult->errorCount(),
                    'warnings' => $validationResult->warningCount(),
                    'information' => $validationResult->infoCount(),
                ],
                'summary' => [
                    'status' => $runtimeHealth->status(),
                    'score' => $runtimeHealth->score(),
                    'modules' => count(
                        $runtimeHealth->modules(),
                    ),
                    'healthyModules' => $healthyModules,
                    'issues' => count($moduleIssues),
                    'bootStages' => $diagnostics['registries']
                        ['counts']['boot_stages'],
                ],
                'registryCounts' => $registryCounts,
                'moduleIssues' => $moduleIssues,
            ],
        );
    }
}