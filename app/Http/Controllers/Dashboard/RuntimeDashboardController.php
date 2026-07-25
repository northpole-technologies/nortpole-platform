<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Northpole\Runtime\Capabilities\CapabilityRegistry;
use Northpole\Runtime\Commands\ModuleCommandRegistry;
use Northpole\Runtime\Configuration\ModuleConfigurationRegistry;
use Northpole\Runtime\Events\ModuleEventRegistry;
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
        $modules = array_map(
            static fn (ModuleManifest $module): array => [
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
            ],
            $this->runtime->modules(),
        );

        usort(
            $modules,
            static fn (array $left, array $right): int => $left['name'] <=> $right['name'],
        );

        $enabledModules = count(
            array_filter(
                $modules,
                static fn (array $module): bool => $module['enabled'],
            ),
        );

        return view(
            'dashboard.runtime',
            [
                'health' => $modules === []
                    ? 'DEGRADED'
                    : 'HEALTHY',
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
