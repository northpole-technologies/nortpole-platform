<?php

declare(strict_types=1);

namespace Northpole\Runtime\Metadata;

use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Metadata\Contracts\RuntimeMetadataServiceContract;
use Northpole\Runtime\Runtime;

final class RuntimeMetadataService implements RuntimeMetadataServiceContract
{
    public function __construct(
        private readonly Runtime $runtime,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $modules = $this->runtime->modules();

        $enabledModules = array_filter(
            $modules,
            static fn (ModuleManifest $module): bool =>
                $module->enabled(),
        );

        return [
            'status' => 'healthy',
            'modules' => [
                'total' => count($modules),
                'enabled' => count($enabledModules),
                'disabled' =>
                    count($modules) - count($enabledModules),
            ],
            'commands' => $this->countMapItems(
                $this->commands(),
            ),
            'queries' => $this->countMapItems(
                $this->queries(),
            ),
            'agents' => $this->countMapItems(
                $this->agents(),
            ),
            'published_events' => $this->countEventItems(
                'publishes',
            ),
            'event_subscriptions' => $this->countEventItems(
                'subscribes',
            ),
            'permissions' => $this->countListItems(
                $this->permissions(),
            ),
            'capabilities' => $this->countListItems(
                $this->capabilities(),
            ),
            'navigation_items' => $this->countListItems(
                $this->navigation(),
            ),
            'notifications' => $this->countListItems(
                $this->notifications(),
            ),
            'scheduled_jobs' => $this->countListItems(
                $this->scheduledJobs(),
            ),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function modules(): array
    {
        $modules = [];

        foreach ($this->runtime->modules() as $module) {
            $modules[] = $this->normaliseModule($module);
        }

        usort(
            $modules,
            static fn (array $left, array $right): int =>
                $left['slug'] <=> $right['slug'],
        );

        return $modules;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function module(string $slug): ?array
    {
        $normalisedSlug = strtolower(
            trim($slug),
        );

        if ($normalisedSlug === '') {
            return null;
        }

        $module = $this->runtime->module(
            $normalisedSlug,
        );

        if (! $module instanceof ModuleManifest) {
            return null;
        }

        return $this->normaliseModule($module);
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function commands(): array
    {
        $commands = [];

        foreach ($this->runtime->modules() as $module) {
            $items = $module->handledCommands();

            if ($items === []) {
                continue;
            }

            ksort($items);

            $commands[$module->slug()] = $items;
        }

        ksort($commands);

        return $commands;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function queries(): array
    {
        $queries = [];

        foreach ($this->runtime->modules() as $module) {
            $items = $module->handledQueries();

            if ($items === []) {
                continue;
            }

            ksort($items);

            $queries[$module->slug()] = $items;
        }

        ksort($queries);

        return $queries;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    /**
     * @return array<string, array<string, string>>
     */
    public function agents(): array
    {
        $agents = [];

        foreach ($this->runtime->modules() as $module) {
            $items = $module->handledAgents();

            if ($items === []) {
                continue;
            }

            ksort($items);

            $agents[$module->slug()] = $items;
        }

        ksort($agents);

        return $agents;
    }
    public function events(): array
    {
        $events = [];

        foreach ($this->runtime->modules() as $module) {
            $publishes = $module->publishedEvents();
            $subscribes = $module->eventSubscribers();

            if (
                $publishes === []
                && $subscribes === []
            ) {
                continue;
            }

            sort($publishes);
            ksort($subscribes);

            foreach ($subscribes as &$listeners) {
                sort($listeners);
            }

            unset($listeners);

            $events[$module->slug()] = [
                'publishes' => $publishes,
                'subscribes' => $subscribes,
            ];
        }

        ksort($events);

        return $events;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function permissions(): array
    {
        return $this->moduleLists(
            static fn (ModuleManifest $module): array =>
                $module->permissions(),
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function capabilities(): array
    {
        return $this->moduleLists(
            static fn (ModuleManifest $module): array =>
                $module->capabilities(),
        );
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function navigation(): array
    {
        return $this->moduleDefinitions(
            static fn (ModuleManifest $module): array =>
                $module->navigation(),
        );
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function notifications(): array
    {
        return $this->moduleDefinitions(
            static fn (ModuleManifest $module): array =>
                $module->notifications(),
        );
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function scheduledJobs(): array
    {
        return $this->moduleDefinitions(
            static fn (ModuleManifest $module): array =>
                $module->scheduledJobs(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function graph(): array
    {
        $nodes = [];
        $edges = [];

        foreach ($this->runtime->modules() as $module) {
            $nodes[] = [
                'id' => $module->slug(),
                'name' => $module->name(),
                'version' => $module->version(),
                'enabled' => $module->enabled(),
            ];

            foreach (
                $module->dependencyConstraints()
                as $dependency => $constraint
            ) {
                $edges[] = [
                    'source' => $module->slug(),
                    'target' => $dependency,
                    'type' => 'depends_on',
                    'constraint' => $constraint,
                ];
            }
        }

        usort(
            $nodes,
            static fn (array $left, array $right): int =>
                $left['id'] <=> $right['id'],
        );

        usort(
            $edges,
            static function (
                array $left,
                array $right,
            ): int {
                return [
                    $left['source'],
                    $left['target'],
                ] <=> [
                    $right['source'],
                    $right['target'],
                ];
            },
        );

        return [
            'nodes' => $nodes,
            'edges' => $edges,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normaliseModule(
        ModuleManifest $module,
    ): array {
        return [
            'name' => $module->name(),
            'slug' => $module->slug(),
            'version' => $module->version(),
            'description' => $module->description(),
            'enabled' => $module->enabled(),
            'path' => $module->path(),
            'manifest_path' => $module->manifestPath(),
            'providers' => $module->providers(),
            'dependencies' =>
                $module->dependencyConstraints(),
            'routes' => $module->routes(),
            'views' => $module->viewsPath(),
            'migrations' => $module->migrationsPath(),
            'configuration' =>
                $module->configuration(),
            'settings' => $module->settings(),
            'capabilities' =>
                $module->capabilities(),
            'permissions' =>
                $module->permissions(),
            'navigation' =>
                $module->navigation(),
            'published_events' =>
                $module->publishedEvents(),
            'event_subscribers' =>
                $module->eventSubscribers(),
            'notifications' =>
                $module->notifications(),
            'commands' =>
                $module->handledCommands(),
            'queries' =>
                $module->handledQueries(),
            'agents' =>
                $module->handledAgents(),
            'scheduled_jobs' =>
                $module->scheduledJobs(),
        ];
    }

    /**
     * @param callable(ModuleManifest): array<int, string> $resolver
     * @return array<string, array<int, string>>
     */
    private function moduleLists(
        callable $resolver,
    ): array {
        $result = [];

        foreach ($this->runtime->modules() as $module) {
            $items = $resolver($module);

            if ($items === []) {
                continue;
            }

            sort($items);

            $result[$module->slug()] = $items;
        }

        ksort($result);

        return $result;
    }

    /**
     * @param callable(ModuleManifest): array<int, array<string, mixed>> $resolver
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function moduleDefinitions(
        callable $resolver,
    ): array {
        $result = [];

        foreach ($this->runtime->modules() as $module) {
            $items = $resolver($module);

            if ($items === []) {
                continue;
            }

            $result[$module->slug()] = $items;
        }

        ksort($result);

        return $result;
    }

    /**
     * @param array<string, array<mixed>> $groups
     */
    private function countListItems(
        array $groups,
    ): int {
        return array_sum(
            array_map(
                'count',
                $groups,
            ),
        );
    }

    /**
     * @param array<string, array<string, string>> $groups
     */
    private function countMapItems(
        array $groups,
    ): int {
        return array_sum(
            array_map(
                'count',
                $groups,
            ),
        );
    }

    private function countEventItems(
        string $type,
    ): int {
        $count = 0;

        foreach ($this->events() as $eventMetadata) {
            if ($type === 'publishes') {
                $count += count(
                    $eventMetadata['publishes'],
                );

                continue;
            }

            foreach (
                $eventMetadata['subscribes']
                as $listeners
            ) {
                $count += count($listeners);
            }
        }

        return $count;
    }
}