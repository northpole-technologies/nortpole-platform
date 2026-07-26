<?php

declare(strict_types=1);

namespace Northpole\Runtime\Inspection;

use Northpole\Runtime\Inspection\Contracts\RuntimeInspectionServiceContract;
use Northpole\Runtime\Metadata\RuntimeMetadataService;

final class RuntimeInspectionService implements RuntimeInspectionServiceContract
{
    /**
     * @var array<string, string>
     */
    private const REGISTRIES = [
        'commands' => 'commands',
        'queries' => 'queries',
        'agents' => 'agents',
        'events' => 'events',
        'permissions' => 'permissions',
        'capabilities' => 'capabilities',
        'navigation' => 'navigation',
        'notifications' => 'notifications',
        'scheduled-jobs' => 'scheduledJobs',
    ];

    public function __construct(
        private readonly RuntimeMetadataService $metadata,
    ) {}

    public function inspect(
        RuntimeInspectionReference $reference,
    ): ?RuntimeInspectionResult {
        $method = self::REGISTRIES[
            $reference->registry
        ] ?? null;

        if ($method === null) {
            return null;
        }

        $groups = $this->metadata->{$method}();

        if (! is_array($groups)) {
            return null;
        }

        $items = $this->moduleItems(
            $groups,
            $reference->module,
        );

        if ($items === null) {
            return null;
        }

        if ($reference->registry === 'events') {
            return $this->inspectEvent(
                $reference,
                $items,
            );
        }

        foreach ($items as $key => $value) {
            $registrationKey = $this->registrationKey(
                $key,
                $value,
            );

            if (
                strtolower($registrationKey)
                !== strtolower($reference->key)
            ) {
                continue;
            }

            return new RuntimeInspectionResult(
                reference: new RuntimeInspectionReference(
                    registry: $reference->registry,
                    module: $reference->module,
                    key: $registrationKey,
                ),
                value: $value,
            );
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $groups
     * @return array<mixed>|null
     */
    private function moduleItems(
        array $groups,
        string $module,
    ): ?array {
        foreach ($groups as $moduleSlug => $items) {
            if (
                ! is_string($moduleSlug)
                || strtolower($moduleSlug)
                    !== strtolower($module)
            ) {
                continue;
            }

            return is_array($items)
                ? $items
                : null;
        }

        return null;
    }

    /**
     * @param  array<mixed>  $events
     */
    private function inspectEvent(
        RuntimeInspectionReference $reference,
        array $events,
    ): ?RuntimeInspectionResult {
        $published = false;
        $listeners = [];

        foreach ($events['publishes'] ?? [] as $event) {
            if (
                is_string($event)
                && strtolower($event)
                    === strtolower($reference->key)
            ) {
                $published = true;
            }
        }

        foreach (
            $events['subscribes'] ?? [] as $event => $registeredListeners
        ) {
            if (
                ! is_string($event)
                || strtolower($event)
                    !== strtolower($reference->key)
                || ! is_array($registeredListeners)
            ) {
                continue;
            }

            foreach ($registeredListeners as $listener) {
                if (is_string($listener)) {
                    $listeners[$listener] = $listener;
                }
            }
        }

        if (! $published && $listeners === []) {
            return null;
        }

        ksort($listeners);

        return new RuntimeInspectionResult(
            reference: $reference,
            value: [
                'published' => $published,
                'listeners' => array_values(
                    $listeners,
                ),
            ],
        );
    }

    private function registrationKey(
        int|string $key,
        mixed $value,
    ): string {
        if (is_string($key)) {
            return $key;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            foreach (
                [
                    'key',
                    'name',
                    'id',
                    'route',
                    'permission',
                    'capability',
                    'notification',
                    'job',
                    'class',
                ] as $candidate
            ) {
                $candidateValue = $value[$candidate] ?? null;

                if (
                    is_string($candidateValue)
                    && trim($candidateValue) !== ''
                ) {
                    return trim($candidateValue);
                }
            }
        }

        return sprintf(
            'entry-%d',
            $key + 1,
        );
    }
}
