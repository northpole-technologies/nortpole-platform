<?php

declare(strict_types=1);

namespace Northpole\Runtime\Relationships;

use Northpole\Runtime\Inspection\RuntimeInspectionReference;
use Northpole\Runtime\Metadata\RuntimeMetadataService;
use Northpole\Runtime\Relationships\Contracts\RuntimeRelationshipResolverContract;

final class RuntimeRelationshipResolver implements RuntimeRelationshipResolverContract
{
    public function __construct(
        private readonly RuntimeMetadataService $metadata,
    ) {}

    /**
     * @return array<int, RuntimeRelationship>
     */
    public function relationshipsFor(
        RuntimeInspectionReference $reference,
        mixed $metadata,
    ): array {
        $relationships = match ($reference->registry) {
            'commands',
            'queries',
            'agents' => $this->resolveHandledRegistration(
                $reference,
                $metadata,
            ),
            'events' => $this->resolveEvent(
                $reference,
                $metadata,
            ),
            'navigation' => $this->resolveNavigation(
                $reference,
                $metadata,
            ),
            'notifications' => $this->resolveNotification(
                $metadata,
            ),
            'scheduled-jobs' => $this->resolveScheduledJob(
                $metadata,
            ),
            default => [],
        };

        usort(
            $relationships,
            static fn (
                RuntimeRelationship $left,
                RuntimeRelationship $right,
            ): int => [
                $left->type,
                $left->label,
                $left->value,
            ] <=> [
                $right->type,
                $right->label,
                $right->value,
            ],
        );

        return $relationships;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function dependenciesFor(
        RuntimeInspectionReference $reference,
    ): array {
        $module = $this->metadata->module(
            $reference->module,
        );

        if (! is_array($module)) {
            return [];
        }

        $dependencies = [];

        foreach (
            $module['dependencies'] ?? [] as $dependency => $constraint
        ) {
            if (
                ! is_string($dependency)
                || ! is_string($constraint)
            ) {
                continue;
            }

            $dependencies[] = [
                'type' => 'module',
                'module' => $dependency,
                'constraint' => $constraint,
            ];
        }

        usort(
            $dependencies,
            static fn (array $left, array $right): int => ($left['module'] ?? '')
                <=>
                ($right['module'] ?? ''),
        );

        return $dependencies;
    }

    /**
     * @return array<int, RuntimeRelationship>
     */
    private function resolveHandledRegistration(
        RuntimeInspectionReference $reference,
        mixed $metadata,
    ): array {
        if (
            ! is_string($metadata)
            || trim($metadata) === ''
        ) {
            return [];
        }

        return [
            new RuntimeRelationship(
                type: 'handled_by',
                label: 'Handled by',
                value: trim($metadata),
                metadata: [
                    'registry' => $reference->registry,
                ],
            ),
        ];
    }

    /**
     * @return array<int, RuntimeRelationship>
     */
    private function resolveEvent(
        RuntimeInspectionReference $reference,
        mixed $metadata,
    ): array {
        if (! is_array($metadata)) {
            return [];
        }

        $relationships = [];

        if (($metadata['published'] ?? false) === true) {
            $relationships[] = new RuntimeRelationship(
                type: 'published_by',
                label: 'Published by module',
                value: $reference->module,
            );
        }

        foreach ($metadata['listeners'] ?? [] as $listener) {
            if (
                ! is_string($listener)
                || trim($listener) === ''
            ) {
                continue;
            }

            $relationships[] = new RuntimeRelationship(
                type: 'handled_by',
                label: 'Subscribed listener',
                value: trim($listener),
            );
        }

        return $relationships;
    }

    /**
     * @return array<int, RuntimeRelationship>
     */
    private function resolveNavigation(
        RuntimeInspectionReference $reference,
        mixed $metadata,
    ): array {
        if (! is_array($metadata)) {
            return [];
        }

        $relationships = [];

        $route = $metadata['route'] ?? null;

        if (
            is_string($route)
            && trim($route) !== ''
        ) {
            $relationships[] = new RuntimeRelationship(
                type: 'routes_to',
                label: 'Routes to',
                value: trim($route),
            );
        }

        $permission = $metadata['permission'] ?? null;

        if (
            is_string($permission)
            && trim($permission) !== ''
        ) {
            $permission = trim($permission);

            $relationships[] = new RuntimeRelationship(
                type: 'requires',
                label: 'Requires permission',
                value: $permission,
                target: new RuntimeInspectionReference(
                    registry: 'permissions',
                    module: $reference->module,
                    key: $permission,
                ),
            );
        }

        $group = $metadata['group'] ?? null;

        if (
            is_string($group)
            && trim($group) !== ''
        ) {
            $relationships[] = new RuntimeRelationship(
                type: 'grouped_under',
                label: 'Navigation group',
                value: trim($group),
            );
        }

        return $relationships;
    }

    /**
     * @return array<int, RuntimeRelationship>
     */
    private function resolveNotification(
        mixed $metadata,
    ): array {
        if (! is_array($metadata)) {
            return [];
        }

        $relationships = [];

        $class = $metadata['class'] ?? null;

        if (
            is_string($class)
            && trim($class) !== ''
        ) {
            $relationships[] = new RuntimeRelationship(
                type: 'handled_by',
                label: 'Notification class',
                value: trim($class),
            );
        }

        foreach ($metadata['channels'] ?? [] as $channel) {
            if (
                ! is_string($channel)
                || trim($channel) === ''
            ) {
                continue;
            }

            $relationships[] = new RuntimeRelationship(
                type: 'delivered_via',
                label: 'Delivery channel',
                value: trim($channel),
            );
        }

        $queue = $metadata['queue'] ?? null;

        if (
            is_string($queue)
            && trim($queue) !== ''
        ) {
            $relationships[] = new RuntimeRelationship(
                type: 'queued_on',
                label: 'Queue',
                value: trim($queue),
            );
        }

        return $relationships;
    }

    /**
     * @return array<int, RuntimeRelationship>
     */
    private function resolveScheduledJob(
        mixed $metadata,
    ): array {
        if (! is_array($metadata)) {
            return [];
        }

        $relationships = [];

        $class = $metadata['class'] ?? null;

        if (
            is_string($class)
            && trim($class) !== ''
        ) {
            $relationships[] = new RuntimeRelationship(
                type: 'handled_by',
                label: 'Job class',
                value: trim($class),
            );
        }

        $frequency = $metadata['frequency'] ?? null;

        if (
            is_string($frequency)
            && trim($frequency) !== ''
        ) {
            $relationships[] = new RuntimeRelationship(
                type: 'scheduled_as',
                label: 'Frequency',
                value: trim($frequency),
            );
        }

        $queue = $metadata['queue'] ?? null;

        if (
            is_string($queue)
            && trim($queue) !== ''
        ) {
            $relationships[] = new RuntimeRelationship(
                type: 'queued_on',
                label: 'Queue',
                value: trim($queue),
            );
        }

        return $relationships;
    }
}
