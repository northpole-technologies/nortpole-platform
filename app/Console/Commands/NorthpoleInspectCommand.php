<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Runtime;

final class NorthpoleInspectCommand extends Command
{
    protected $signature = 'northpole:inspect
                            {module : The module slug to inspect}
                            {--json : Output module information as JSON}';

    protected $description =
        'Inspect a NorthPole module and its runtime contributions';

    public function __construct(
        private readonly Runtime $runtime,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $slug = strtolower(
            trim(
                (string) $this->argument('module'),
            ),
        );

        $module = $this->runtime->module($slug);

        if (! $module instanceof ModuleManifest) {
            $this->error(
                sprintf(
                    'NorthPole module [%s] was not found.',
                    $slug,
                ),
            );

            return self::FAILURE;
        }

        if ((bool) $this->option('json')) {
            $this->line(
                json_encode(
                    $this->inspect($module),
                    JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_SLASHES
                    | JSON_THROW_ON_ERROR,
                ),
            );

            return self::SUCCESS;
        }

        $this->renderModule($module);

        return self::SUCCESS;
    }

    private function renderModule(
        ModuleManifest $module,
    ): void {
        $this->newLine();

        $this->info('NorthPole Module Inspector');
        $this->line(str_repeat('=', 26));

        $this->newLine();

        $this->comment('Module');

        $this->table(
            ['Property', 'Value'],
            [
                ['Name', $module->name()],
                ['Slug', $module->slug()],
                ['Version', $module->version()],
                [
                    'Status',
                    $module->enabled()
                        ? 'Enabled'
                        : 'Disabled',
                ],
                [
                    'Description',
                    $module->description() ?? 'None',
                ],
                ['Path', $module->path()],
                ['Manifest', $module->manifestPath()],
            ],
        );

        $this->renderList(
            'Providers',
            $module->providers(),
        );

        $this->renderDependencies(
            $module->dependencyConstraints(),
        );

        $this->renderList(
            'Capabilities',
            $module->capabilities(),
        );

        $this->renderList(
            'Permissions',
            $module->permissions(),
        );

        $this->renderMap(
            'Commands',
            $module->handledCommands(),
            'Command',
            'Handler',
        );

        $this->renderMap(
            'Queries',
            $module->handledQueries(),
            'Query',
            'Handler',
        );

        $this->renderList(
            'Published Events',
            $module->publishedEvents(),
        );

        $this->renderSubscribers(
            $module->eventSubscribers(),
        );

        $this->renderNavigation(
            $module->navigation(),
        );

        $this->renderSettings(
            $module->settings(),
        );

        $this->renderNotifications(
            $module->notifications(),
        );

        $this->renderScheduledJobs(
            $module->scheduledJobs(),
        );

        $this->renderMap(
            'Routes',
            $module->routes(),
            'Type',
            'Path',
        );

        $this->renderMap(
            'Configuration Files',
            $module->configuration(),
            'Key',
            'Path',
        );

        $this->renderOptionalPath(
            'Views',
            $module->viewsPath(),
        );

        $this->renderOptionalPath(
            'Migrations',
            $module->migrationsPath(),
        );

        $this->info('Inspection complete.');
    }

    /**
     * @param array<int, string> $items
     */
    private function renderList(
        string $heading,
        array $items,
    ): void {
        $this->comment($heading);

        if ($items === []) {
            $this->line('None');

            $this->newLine();

            return;
        }

        sort($items);

        $this->table(
            ['Value'],
            array_map(
                static fn (string $item): array => [$item],
                $items,
            ),
        );
    }

    /**
     * @param array<string, string> $items
     */
    private function renderMap(
        string $heading,
        array $items,
        string $keyHeading,
        string $valueHeading,
    ): void {
        $this->comment($heading);

        if ($items === []) {
            $this->line('None');

            $this->newLine();

            return;
        }

        ksort($items);

        $rows = [];

        foreach ($items as $key => $value) {
            $rows[] = [
                $key,
                $value,
            ];
        }

        $this->table(
            [$keyHeading, $valueHeading],
            $rows,
        );
    }

    /**
     * @param array<string, string> $dependencies
     */
    private function renderDependencies(
        array $dependencies,
    ): void {
        $this->renderMap(
            'Dependencies',
            $dependencies,
            'Module',
            'Version',
        );
    }

    /**
     * @param array<string, array<int, string>> $subscribers
     */
    private function renderSubscribers(
        array $subscribers,
    ): void {
        $this->comment('Event Subscribers');

        if ($subscribers === []) {
            $this->line('None');

            $this->newLine();

            return;
        }

        ksort($subscribers);

        $rows = [];

        foreach ($subscribers as $event => $listeners) {
            sort($listeners);

            foreach ($listeners as $listener) {
                $rows[] = [
                    $event,
                    $listener,
                ];
            }
        }

        $this->table(
            ['Event', 'Listener'],
            $rows,
        );
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function renderNavigation(
        array $items,
    ): void {
        $this->comment('Navigation');

        if ($items === []) {
            $this->line('None');

            $this->newLine();

            return;
        }

        $rows = [];

        foreach ($items as $item) {
            $rows[] = [
                (string) ($item['label'] ?? ''),
                (string) ($item['route'] ?? ''),
                (string) ($item['group'] ?? 'None'),
                (string) ($item['permission'] ?? 'None'),
                (string) ($item['order'] ?? 100),
            ];
        }

        $this->table(
            [
                'Label',
                'Route',
                'Group',
                'Permission',
                'Order',
            ],
            $rows,
        );
    }

    /**
     * @param array<int, array<string, mixed>> $settings
     */
    private function renderSettings(
        array $settings,
    ): void {
        $this->comment('Settings');

        if ($settings === []) {
            $this->line('None');

            $this->newLine();

            return;
        }

        $rows = [];

        foreach ($settings as $setting) {
            $rows[] = [
                (string) ($setting['key'] ?? ''),
                (string) ($setting['type'] ?? ''),
                (string) ($setting['label'] ?? 'None'),
                $this->formatValue(
                    $setting['default'] ?? null,
                ),
            ];
        }

        $this->table(
            ['Key', 'Type', 'Label', 'Default'],
            $rows,
        );
    }

    /**
     * @param array<int, array<string, mixed>> $notifications
     */
    private function renderNotifications(
        array $notifications,
    ): void {
        $this->comment('Notifications');

        if ($notifications === []) {
            $this->line('None');

            $this->newLine();

            return;
        }

        $rows = [];

        foreach ($notifications as $notification) {
            $rows[] = [
                (string) ($notification['name'] ?? ''),
                (string) ($notification['class'] ?? ''),
                implode(
                    ', ',
                    $notification['channels'] ?? [],
                ),
                (string) ($notification['queue'] ?? 'Default'),
            ];
        }

        $this->table(
            ['Name', 'Class', 'Channels', 'Queue'],
            $rows,
        );
    }

    /**
     * @param array<int, array<string, mixed>> $jobs
     */
    private function renderScheduledJobs(
        array $jobs,
    ): void {
        $this->comment('Scheduled Jobs');

        if ($jobs === []) {
            $this->line('None');

            $this->newLine();

            return;
        }

        $rows = [];

        foreach ($jobs as $job) {
            $rows[] = [
                (string) ($job['class'] ?? ''),
                (string) ($job['frequency'] ?? ''),
                (string) ($job['at'] ?? 'None'),
                (string) ($job['queue'] ?? 'Default'),
                ($job['without_overlapping'] ?? false)
                    ? 'Yes'
                    : 'No',
                ($job['run_in_background'] ?? false)
                    ? 'Yes'
                    : 'No',
            ];
        }

        $this->table(
            [
                'Class',
                'Frequency',
                'At',
                'Queue',
                'No overlap',
                'Background',
            ],
            $rows,
        );
    }

    private function renderOptionalPath(
        string $heading,
        ?string $path,
    ): void {
        $this->comment($heading);
        $this->line($path ?? 'None');
        $this->newLine();
    }

    private function formatValue(
        mixed $value,
    ): string {
        if ($value === null) {
            return 'None';
        }

        if (is_bool($value)) {
            return $value
                ? 'true'
                : 'false';
        }

        if (is_array($value)) {
            return json_encode(
                $value,
                JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR,
            );
        }

        return (string) $value;
    }

    /**
     * @return array<string, mixed>
     */
    private function inspect(
        ModuleManifest $module,
    ): array {
        return [
            'module' => [
                'name' => $module->name(),
                'slug' => $module->slug(),
                'version' => $module->version(),
                'description' => $module->description(),
                'enabled' => $module->enabled(),
                'path' => $module->path(),
                'manifest_path' => $module->manifestPath(),
            ],
            'providers' => $module->providers(),
            'dependencies' =>
                $module->dependencyConstraints(),
            'routes' => $module->routes(),
            'views' => $module->viewsPath(),
            'migrations' => $module->migrationsPath(),
            'configuration' => $module->configuration(),
            'settings' => $module->settings(),
            'capabilities' => $module->capabilities(),
            'permissions' => $module->permissions(),
            'navigation' => $module->navigation(),
            'published_events' =>
                $module->publishedEvents(),
            'event_subscribers' =>
                $module->eventSubscribers(),
            'notifications' => $module->notifications(),
            'commands' => $module->handledCommands(),
            'queries' => $module->handledQueries(),
            'scheduled_jobs' => $module->scheduledJobs(),
        ];
    }
}