<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Northpole\Runtime\Capabilities\CapabilityRegistry;
use Northpole\Runtime\Commands\ModuleCommandRegistry;
use Northpole\Runtime\Configuration\ModuleConfigurationRegistry;
use Northpole\Runtime\Diagnostics\RuntimeDiagnosticsService;
use Northpole\Runtime\Events\ModuleEventRegistry;
use Northpole\Runtime\Health\ModuleHealth;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistry;
use Northpole\Runtime\Lifecycle\StageRegistry;
use Northpole\Runtime\Navigation\NavigationRegistry;
use Northpole\Runtime\Notifications\ModuleNotificationRegistry;
use Northpole\Runtime\Permissions\PermissionRegistry;
use Northpole\Runtime\Queries\ModuleQueryRegistry;

final class RuntimeDiagnosticsController extends Controller
{
    public function __construct(
        private readonly RuntimeDiagnosticsService $diagnostics,
        private readonly StageRegistry $stageRegistry,
        private readonly CapabilityRegistry $capabilityRegistry,
        private readonly ModuleCommandRegistry $commandRegistry,
        private readonly ModuleQueryRegistry $queryRegistry,
        private readonly ModuleEventRegistry $eventRegistry,
        private readonly NavigationRegistry $navigationRegistry,
        private readonly PermissionRegistry $permissionRegistry,
        private readonly ModuleConfigurationRegistry $configurationRegistry,
        private readonly ModuleNotificationRegistry $notificationRegistry,
        private readonly ModuleScheduledJobRegistry $scheduledJobRegistry,
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
                static fn (ModuleHealth $health): bool => $health->status() === 'healthy',
            ),
        );

        $registryCounts = [
            [
                'label' => 'Capabilities',
                'value' => $this->capabilityRegistry->count(),
                'registry' => 'capabilities',
            ],
            [
                'label' => 'Commands',
                'value' => $this->commandRegistry->count(),
                'registry' => 'commands',
            ],
            [
                'label' => 'Queries',
                'value' => $this->queryRegistry->count(),
                'registry' => 'queries',
            ],
            [
                'label' => 'Event listeners',
                'value' => $this->eventRegistry->count(),
                'registry' => 'events',
            ],
            [
                'label' => 'Navigation items',
                'value' => $this->navigationRegistry->count(),
                'registry' => 'navigation',
            ],
            [
                'label' => 'Permissions',
                'value' => $this->permissionRegistry->count(),
                'registry' => 'permissions',
            ],
            [
                'label' => 'Configuration',
                'value' => $this->configurationRegistry->count(),
            ],
            [
                'label' => 'Notifications',
                'value' => $this->notificationRegistry->count(),
                'registry' => 'notifications',
            ],
            [
                'label' => 'Scheduled jobs',
                'value' => $this->scheduledJobRegistry->count(),
                'registry' => 'scheduled-jobs',
            ],
        ];

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
                    'bootStages' => $this->stageRegistry->count(),
                ],
                'registryCounts' => $registryCounts,
                'moduleIssues' => $moduleIssues,
            ],
        );
    }
}
