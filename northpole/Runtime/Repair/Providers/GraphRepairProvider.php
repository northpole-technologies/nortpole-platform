<?php

declare(strict_types=1);

namespace Northpole\Runtime\Repair\Providers;

use Northpole\Runtime\Repair\Contracts\RepairProviderContract;
use Northpole\Runtime\Repair\RepairAction;
use Northpole\Runtime\Repair\RepairActionType;
use Northpole\Runtime\Repair\RepairRecommendation;
use Northpole\Runtime\Validation\ValidationIssue;

final class GraphRepairProvider implements RepairProviderContract
{
    /**
     * @return array<int, string>
     */
    public function issueCodes(): array
    {
        return [
            'graph.node_orphaned',
            'graph.self_dependency',
            'graph.dependency_cycle',
        ];
    }

    public function supports(
        ValidationIssue $issue,
    ): bool {
        return in_array(
            $issue->code(),
            $this->issueCodes(),
            true,
        );
    }

    public function recommend(
        ValidationIssue $issue,
    ): ?RepairRecommendation {
        if (! $this->supports($issue)) {
            return null;
        }

        return match ($issue->code()) {
            'graph.node_orphaned' => $this->orphanedNode($issue),
            'graph.self_dependency' => $this->selfDependency($issue),
            'graph.dependency_cycle' => $this->dependencyCycle($issue),
            default => null,
        };
    }

    private function orphanedNode(
        ValidationIssue $issue,
    ): RepairRecommendation {
        $context = $issue->context();

        $nodeId = $this->contextString(
            context: $context,
            key: 'node_id',
            fallback: 'unknown',
        );

        $nodeType = $this->contextString(
            context: $context,
            key: 'node_type',
            fallback: 'runtime',
        );

        return new RepairRecommendation(
            code: 'repair.graph.connect_orphaned_node',
            title: 'Connect orphaned runtime node',
            description: sprintf(
                'Runtime %s node [%s] has no graph relationships.',
                $nodeType,
                $nodeId,
            ),
            severity: $issue->severity(),
            module: $issue->module(),
            issue: $issue,
            actions: [
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Review the node declaration',
                    content: sprintf(
                        'Confirm [%s] is still required and is declared by the correct module.',
                        $nodeId,
                    ),
                ),
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Connect or remove the node',
                    content: sprintf(
                        'Add the missing runtime relationship for [%s], or remove the stale declaration if it is no longer used.',
                        $nodeId,
                    ),
                ),
                $this->validationAction(),
            ],
        );
    }

    private function selfDependency(
        ValidationIssue $issue,
    ): RepairRecommendation {
        $context = $issue->context();

        $module = $this->contextString(
            context: $context,
            key: 'module',
            fallback: $issue->module() ?? 'unknown',
        );

        return new RepairRecommendation(
            code: 'repair.graph.remove_self_dependency',
            title: 'Remove self-dependency',
            description: sprintf(
                'Module [%s] declares itself as a dependency.',
                $module,
            ),
            severity: $issue->severity(),
            module: $issue->module(),
            issue: $issue,
            actions: [
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Update the module manifest',
                    content: sprintf(
                        'Remove [%s] from the dependencies declared by module [%s].',
                        $module,
                        $module,
                    ),
                ),
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Review internal coupling',
                    content: sprintf(
                        'Keep internal services inside module [%s] without declaring the module as its own dependency.',
                        $module,
                    ),
                ),
                $this->validationAction(),
            ],
        );
    }

    private function dependencyCycle(
        ValidationIssue $issue,
    ): RepairRecommendation {
        $context = $issue->context();
        $cycle = $this->contextStringList(
            context: $context,
            key: 'cycle',
        );

        $cycleText = $cycle === []
            ? 'unknown dependency cycle'
            : implode(' -> ', $cycle);

        return new RepairRecommendation(
            code: 'repair.graph.break_dependency_cycle',
            title: 'Break module dependency cycle',
            description: sprintf(
                'The runtime detected a circular module dependency: %s.',
                $cycleText,
            ),
            severity: $issue->severity(),
            module: $issue->module(),
            issue: $issue,
            actions: [
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Choose a dependency to remove',
                    content: sprintf(
                        'Review [%s] and remove or reverse one dependency so the module graph becomes acyclic.',
                        $cycleText,
                    ),
                ),
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Extract shared behaviour',
                    content: 'Move shared contracts or services into a lower-level module that each participating module may depend on independently.',
                ),
                new RepairAction(
                    type: RepairActionType::Instruction,
                    label: 'Consider an event boundary',
                    content: 'Replace one direct module dependency with a published event and subscriber when synchronous coupling is unnecessary.',
                ),
                $this->validationAction(),
            ],
        );
    }

    private function validationAction(): RepairAction
    {
        return new RepairAction(
            type: RepairActionType::PowerShell,
            label: 'Re-run graph validation tests',
            content: 'php artisan test .\tests\Unit\Runtime\Validation\Rules\Graph .\tests\Unit\Runtime\Repair',
        );
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function contextString(
        array $context,
        string $key,
        string $fallback,
    ): string {
        $value = $context[$key] ?? null;

        if (! is_string($value)) {
            return $fallback;
        }

        $value = trim($value);

        return $value === ''
            ? $fallback
            : $value;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<int, string>
     */
    private function contextStringList(
        array $context,
        string $key,
    ): array {
        $values = $context[$key] ?? null;

        if (! is_array($values)) {
            return [];
        }

        $result = [];

        foreach ($values as $value) {
            if (! is_string($value)) {
                continue;
            }

            $value = trim($value);

            if ($value === '') {
                continue;
            }

            $result[] = $value;
        }

        return $result;
    }
}
