<?php

declare(strict_types=1);

namespace Northpole\Runtime\Diagnostics;

use Northpole\Runtime\Capabilities\CapabilityRegistry;
use Northpole\Runtime\Commands\ModuleCommandRegistry;
use Northpole\Runtime\Configuration\ModuleConfigurationRegistry;
use Northpole\Runtime\Events\ModuleEventRegistry;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistry;
use Northpole\Runtime\Lifecycle\StageRegistry;
use Northpole\Runtime\Navigation\NavigationRegistry;
use Northpole\Runtime\Notifications\ModuleNotificationRegistry;
use Northpole\Runtime\Permissions\PermissionRegistry;
use Northpole\Runtime\Queries\ModuleQueryRegistry;
use Northpole\Runtime\Roles\RoleDefinitionRegistry;

final class RuntimeRegistryStatisticsService
{
    public function __construct(
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
        private readonly RoleDefinitionRegistry $roleRegistry,
    ) {}

    /**
     * @return array{
     *     boot_stages: int,
     *     capabilities: int,
     *     commands: int,
     *     queries: int,
     *     event_listeners: int,
     *     navigation_items: int,
     *     permissions: int,
     *     configuration: int,
     *     notifications: int,
     *     scheduled_jobs: int,
     *     roles: int
     * }
     */
    public function counts(): array
    {
        return [
            'boot_stages' => $this->stageRegistry->count(),
            'capabilities' => $this->capabilityRegistry->count(),
            'commands' => $this->commandRegistry->count(),
            'queries' => $this->queryRegistry->count(),
            'event_listeners' => $this->eventRegistry->count(),
            'navigation_items' => $this->navigationRegistry->count(),
            'permissions' => $this->permissionRegistry->count(),
            'configuration' => $this->configurationRegistry->count(),
            'notifications' => $this->notificationRegistry->count(),
            'scheduled_jobs' => $this->scheduledJobRegistry->count(),
            'roles' => $this->roleRegistry->count(),
        ];
    }

    public function total(): int
    {
        return array_sum(
            $this->counts(),
        );
    }

    /**
     * @return array<int, array{
     *     label: string,
     *     value: int,
     *     registry?: string
     * }>
     */
    public function metricCards(): array
    {
        $counts = $this->counts();

        return [
            [
                'label' => 'Boot stages',
                'value' => $counts['boot_stages'],
            ],
            [
                'label' => 'Capabilities',
                'value' => $counts['capabilities'],
                'registry' => 'capabilities',
            ],
            [
                'label' => 'Commands',
                'value' => $counts['commands'],
                'registry' => 'commands',
            ],
            [
                'label' => 'Queries',
                'value' => $counts['queries'],
                'registry' => 'queries',
            ],
            [
                'label' => 'Event listeners',
                'value' => $counts['event_listeners'],
                'registry' => 'events',
            ],
            [
                'label' => 'Navigation items',
                'value' => $counts['navigation_items'],
                'registry' => 'navigation',
            ],
            [
                'label' => 'Permissions',
                'value' => $counts['permissions'],
                'registry' => 'permissions',
            ],
            [
                'label' => 'Configuration',
                'value' => $counts['configuration'],
            ],
            [
                'label' => 'Notifications',
                'value' => $counts['notifications'],
                'registry' => 'notifications',
            ],
            [
                'label' => 'Scheduled jobs',
                'value' => $counts['scheduled_jobs'],
                'registry' => 'scheduled-jobs',
            ],
            [
                'label' => 'Roles',
                'value' => $counts['roles'],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     label: string,
     *     value: int,
     *     registry?: string
     * }>
     */
    public function diagnosticCards(): array
    {
        return array_values(
            array_filter(
                $this->metricCards(),
                static fn (array $metric): bool =>
                    $metric['label'] !== 'Boot stages',
            ),
        );
    }

    /**
     * @return array<int, array{0: string, 1: int}>
     */
    public function consoleRows(): array
    {
        return array_map(
            static fn (array $metric): array => [
                $metric['label'],
                $metric['value'],
            ],
            $this->metricCards(),
        );
    }
}