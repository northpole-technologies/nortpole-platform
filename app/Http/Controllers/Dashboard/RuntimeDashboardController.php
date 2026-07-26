<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Northpole\Runtime\Diagnostics\RuntimeRegistryStatisticsService;
use Northpole\Runtime\Health\RuntimeHealthSummaryService;
use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Runtime;

final class RuntimeDashboardController extends Controller
{
    public function __construct(
        private readonly Runtime $runtime,
        private readonly RuntimeHealthSummaryService $healthSummary,
        private readonly RuntimeRegistryStatisticsService $registryStatistics,
    ) {}

    public function __invoke(): View
    {
        $healthSummary = $this->healthSummary->inspect();

        $runtimeHealth = $healthSummary['runtimeHealth'];
        $moduleHealth = $healthSummary['moduleHealthBySlug'];

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
                    'issues' => $healthSummary['issueCount'],
                ],
                'environment' => app()->environment(),
                'laravelVersion' => app()->version(),
                'phpVersion' => PHP_VERSION,
                'moduleSummary' => [
                    'discovered' => count($modules),
                    'enabled' => $enabledModules,
                    'disabled' =>
                        count($modules) - $enabledModules,
                ],
                'metrics' => $this->registryStatistics
                    ->metricCards(),
                'modules' => $modules,
            ],
        );
    }
}