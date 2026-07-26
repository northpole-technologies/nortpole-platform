<?php

declare(strict_types=1);

namespace Northpole\Runtime\Search;

use Northpole\Runtime\Metadata\RuntimeMetadataService;
use Northpole\Runtime\Search\Contracts\RuntimeSearchServiceContract;

final class RuntimeSearchService implements RuntimeSearchServiceContract
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

    /**
     * @return array<int, array{
     *     registry: string,
     *     module: string,
     *     key: string,
     *     value: mixed
     * }>
     */
    public function search(
        string $query,
        ?string $module = null,
    ): array {
        $query = trim($query);
        $module = $this->normaliseModule($module);

        if ($query === '') {
            return [];
        }

        $matches = [];

        foreach (self::REGISTRIES as $registry => $method) {
            $groups = $this->metadata->{$method}();

            foreach ($groups as $moduleSlug => $items) {
                if (
                    $module !== null
                    && strtolower($moduleSlug) !== $module
                ) {
                    continue;
                }

                if (! is_array($items)) {
                    continue;
                }

                if ($registry === 'events') {
                    $matches = [
                        ...$matches,
                        ...$this->searchEvents(
                            $query,
                            $moduleSlug,
                            $items,
                        ),
                    ];

                    continue;
                }

                foreach ($items as $key => $value) {
                    $registrationKey = $this->registrationKey(
                        $key,
                        $value,
                    );

                    if (
                        ! $this->matches(
                            $query,
                            $registry,
                            $moduleSlug,
                            $registrationKey,
                            $value,
                        )
                    ) {
                        continue;
                    }

                    $matches[] = [
                        'registry' => $registry,
                        'module' => $moduleSlug,
                        'key' => $registrationKey,
                        'value' => $value,
                    ];
                }
            }
        }

        usort(
            $matches,
            static function (
                array $left,
                array $right,
            ): int {
                return [
                    $left['module'],
                    $left['registry'],
                    $left['key'],
                ] <=> [
                    $right['module'],
                    $right['registry'],
                    $right['key'],
                ];
            },
        );

        return $matches;
    }

    /**
     * @param array<string, mixed> $events
     *
     * @return array<int, array{
     *     registry: string,
     *     module: string,
     *     key: string,
     *     value: mixed
     * }>
     */
    private function searchEvents(
        string $query,
        string $module,
        array $events,
    ): array {
        $matches = [];

        foreach ($events['publishes'] ?? [] as $event) {
            if (! is_string($event)) {
                continue;
            }

            if (
                ! $this->matches(
                    $query,
                    'events',
                    $module,
                    $event,
                    $event,
                )
            ) {
                continue;
            }

            $matches[] = [
                'registry' => 'events',
                'module' => $module,
                'key' => $event,
                'value' => [
                    'type' => 'published',
                ],
            ];
        }

        foreach (
            $events['subscribes'] ?? []
            as $event => $listeners
        ) {
            if (! is_array($listeners)) {
                continue;
            }

            foreach ($listeners as $listener) {
                if (! is_string($listener)) {
                    continue;
                }

                if (
                    ! $this->matches(
                        $query,
                        'events',
                        $module,
                        (string) $event,
                        $listener,
                    )
                ) {
                    continue;
                }

                $matches[] = [
                    'registry' => 'events',
                    'module' => $module,
                    'key' => (string) $event,
                    'value' => [
                        'type' => 'subscriber',
                        'listener' => $listener,
                    ],
                ];
            }
        }

        return $matches;
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
                ]
                as $candidate
            ) {
                $candidateValue = $value[$candidate] ?? null;

                if (
                    is_string($candidateValue)
                    && $candidateValue !== ''
                ) {
                    return $candidateValue;
                }
            }
        }

        return sprintf(
            'entry-%d',
            $key + 1,
        );
    }

    private function matches(
        string $query,
        string $registry,
        string $module,
        string $key,
        mixed $value,
    ): bool {
        $haystack = implode(
            ' ',
            [
                $registry,
                $module,
                $key,
                $this->stringify($value),
            ],
        );

        return str_contains(
            strtolower($haystack),
            strtolower($query),
        );
    }

    private function stringify(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (
            is_int($value)
            || is_float($value)
        ) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value
                ? 'true'
                : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        $encoded = json_encode(
            $value,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE,
        );

        return is_string($encoded)
            ? $encoded
            : '';
    }

    private function normaliseModule(
        ?string $module,
    ): ?string {
        if ($module === null) {
            return null;
        }

        $module = trim($module);

        if ($module === '') {
            return null;
        }

        return strtolower($module);
    }
}