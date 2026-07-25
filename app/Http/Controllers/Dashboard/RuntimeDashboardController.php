<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Northpole\Runtime\Capabilities\CapabilityRegistry;
use Northpole\Runtime\Commands\ModuleCommandRegistry;
use Northpole\Runtime\Configuration\ModuleConfigurationRegistry;
use Northpole\Runtime\Events\ModuleEventRegistry;
use Northpole\Runtime\Health\ModuleHealth;
use Northpole\Runtime\Health\RuntimeHealthService;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistry;
use Northpole\Runtime\Lifecycle\StageRegistry;
use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Navigation\NavigationRegistry;
use Northpole\Runtime\Notifications\ModuleNotificationRegistry;
use Northpole\Runtime\Permissions\PermissionRegistry;
use Northpole\Runtime\Queries\ModuleQueryRegistry;
use Northpole\Runtime\Runtime;

final class RuntimeDashboardController extends Controller
{
    public function __construct(
        private readonly Runtime $runtime,
        private readonly RuntimeHealthService $runtimeHealthService,
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
        $runtimeHealth = $this->runtimeHealthService->report();

        $moduleHealth = [];

        foreach ($runtimeHealth->modules() as $health) {
            $moduleHealth[$health->slug()] = $health;
        }

        $modules = array_map(
            static function (
                ModuleManifest $module
            ) use (
                $moduleHealth
            ): array {
                $health = $moduleHealth[$module->slug()] ?? null;

                return [
                    'name' => $module->name(),
                    'slug' => $module->slug(),
                    'version' => $module->version(),
                    'description' => $module->description(),
                    'enabled' => $module->enabled(),
                    'dependencies' => count(
                        $module->dependencyConstraints(),
                    ),
                    'commands' => count(
                        $module->handledCommands(),
                    ),
                    'queries' => count(
                        $module->handledQueries(),
                    ),
                    'permissions' => count(
                        $module->permissions(),
                    ),
                    'healthStatus' => $health?->status()
                        ?? 'unhealthy',
                    'healthScore' => $health?->score()
                        ?? 0,
                    'healthChecks' => $health?->checks()
                        ?? [],
                ];
            },
            $this->runtime->modules(),
        );

        usort(
            $modules,
            static fn (
                array $left,
                array $right,
            ): int => $left['name'] <=> $right['name'],
        );

        $enabledModules = count(
            array_filter(
                $modules,
                static fn (array $module): bool =>
                    $module['enabled'],
            ),
        );

        $healthyModules = count(
            array_filter(
                $runtimeHealth->modules(),
                static fn (ModuleHealth $health): bool =>
                    $health->status() === 'healthy',
            ),
        );

        $issueCount = array_reduce(
            $runtimeHealth->modules(),
            static function (
                int $count,
                ModuleHealth $health,
            ): int {
                return $count + count(
                    array_filter(
                        $health->checks(),
                        static fn (array $check): bool =>
                            ! $check['healthy'],
                    ),
                );
            },
            0,
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
                    'healthyModules' => $healthyModules,
                    'issues' => $issueCount,
                ],
                'environment' => app()->environment(),
                'laravelVersion' => app()->version(),
                'phpVersion' => PHP_VERSION,
                'moduleSummary' => [
                    'discovered' => count($modules),
                    'enabled' => $enabledModules,
                    'disabled' => count($modules) - $enabledModules,
                ],
                'metrics' => [
                    [
                        'label' => 'Boot stages',
                        'value' => $this->stageRegistry->count(),
                    ],
                    [
                        'label' => 'Capabilities',
                        'value' => $this->capabilityRegistry->count(),
                    ],
                    [
                        'label' => 'Commands',
                        'value' => $this->commandRegistry->count(),
                    ],
                    [
                        'label' => 'Queries',
                        'value' => $this->queryRegistry->count(),
                    ],
                    [
                        'label' => 'Event listeners',
                        'value' => $this->eventRegistry->count(),
                    ],
                    [
                        'label' => 'Navigation items',
                        'value' => $this->navigationRegistry->count(),
                    ],
                    [
                        'label' => 'Permissions',
                        'value' => $this->permissionRegistry->count(),
                    ],
                    [
                        'label' => 'Configuration',
                        'value' => $this->configurationRegistry->count(),
                    ],
                    [
                        'label' => 'Notifications',
                        'value' => $this->notificationRegistry->count(),
                    ],
                    [
                        'label' => 'Scheduled jobs',
                        'value' => $this->scheduledJobRegistry->count(),
                    ],
                ],
                'modules' => $modules,
            ],
        );
    }
}