<?php

declare(strict_types=1);

namespace Northpole\Runtime\Validation\Rules\Graph;

use Northpole\Runtime\Graph\Contracts\RuntimeGraphBuilderContract;
use Northpole\Runtime\Graph\RuntimeGraph;
use Northpole\Runtime\Validation\Contracts\ValidationRuleContract;
use Northpole\Runtime\Validation\ValidationIssue;
use Northpole\Runtime\Validation\ValidationResult;
use Northpole\Runtime\Validation\ValidationSeverity;

final class DependencyCycleValidationRule implements ValidationRuleContract
{
    public function __construct(
        private readonly RuntimeGraphBuilderContract $graphBuilder,
    ) {}

    public function name(): string
    {
        return 'graph-dependency-cycles';
    }

    public function validate(): ValidationResult
    {
        $graph = $this->graphBuilder->build();
        $result = new ValidationResult;
        $adjacency = $this->dependencyAdjacency($graph);

        $this->reportSelfDependencies(
            graph: $graph,
            adjacency: $adjacency,
            result: $result,
        );

        $this->reportDependencyCycles(
            graph: $graph,
            adjacency: $adjacency,
            result: $result,
        );

        return $result;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function dependencyAdjacency(
        RuntimeGraph $graph,
    ): array {
        $adjacency = [];

        foreach ($graph->nodes() as $node) {
            if ($node->type !== 'module') {
                continue;
            }

            $adjacency[$node->id] = [];
        }

        foreach ($graph->edges() as $edge) {
            if ($edge->type !== 'depends_on') {
                continue;
            }

            if (
                ! isset($adjacency[$edge->source])
                || ! isset($adjacency[$edge->target])
            ) {
                continue;
            }

            $adjacency[$edge->source][] = $edge->target;
        }

        foreach ($adjacency as &$dependencies) {
            $dependencies = array_values(
                array_unique($dependencies),
            );

            sort($dependencies);
        }

        unset($dependencies);

        ksort($adjacency);

        return $adjacency;
    }

    /**
     * @param  array<string, array<int, string>>  $adjacency
     */
    private function reportSelfDependencies(
        RuntimeGraph $graph,
        array $adjacency,
        ValidationResult $result,
    ): void {
        foreach ($adjacency as $moduleId => $dependencies) {
            if (! in_array($moduleId, $dependencies, true)) {
                continue;
            }

            $module = $this->moduleSlug(
                graph: $graph,
                nodeId: $moduleId,
            );

            $result->add(
                new ValidationIssue(
                    code: 'graph.self_dependency',
                    message: sprintf(
                        'Runtime module [%s] depends on itself.',
                        $module,
                    ),
                    severity: ValidationSeverity::Error,
                    module: $module,
                    context: [
                        'module' => $module,
                        'node_id' => $moduleId,
                    ],
                ),
            );
        }
    }

    /**
     * @param  array<string, array<int, string>>  $adjacency
     */
    private function reportDependencyCycles(
        RuntimeGraph $graph,
        array $adjacency,
        ValidationResult $result,
    ): void {
        $states = [];
        $path = [];
        $pathIndexes = [];
        $reportedCycles = [];

        foreach (array_keys($adjacency) as $moduleId) {
            if (($states[$moduleId] ?? 0) !== 0) {
                continue;
            }

            $this->visit(
                graph: $graph,
                moduleId: $moduleId,
                adjacency: $adjacency,
                states: $states,
                path: $path,
                pathIndexes: $pathIndexes,
                reportedCycles: $reportedCycles,
                result: $result,
            );
        }
    }

    /**
     * @param  array<string, array<int, string>>  $adjacency
     * @param  array<string, int>  $states
     * @param  array<int, string>  $path
     * @param  array<string, int>  $pathIndexes
     * @param  array<string, true>  $reportedCycles
     */
    private function visit(
        RuntimeGraph $graph,
        string $moduleId,
        array $adjacency,
        array &$states,
        array &$path,
        array &$pathIndexes,
        array &$reportedCycles,
        ValidationResult $result,
    ): void {
        $states[$moduleId] = 1;
        $pathIndexes[$moduleId] = count($path);
        $path[] = $moduleId;

        foreach ($adjacency[$moduleId] ?? [] as $dependencyId) {
            if ($dependencyId === $moduleId) {
                continue;
            }

            $dependencyState = $states[$dependencyId] ?? 0;

            if ($dependencyState === 0) {
                $this->visit(
                    graph: $graph,
                    moduleId: $dependencyId,
                    adjacency: $adjacency,
                    states: $states,
                    path: $path,
                    pathIndexes: $pathIndexes,
                    reportedCycles: $reportedCycles,
                    result: $result,
                );

                continue;
            }

            if ($dependencyState !== 1) {
                continue;
            }

            $cycleStart = $pathIndexes[$dependencyId] ?? null;

            if ($cycleStart === null) {
                continue;
            }

            $cycleNodeIds = array_slice(
                $path,
                $cycleStart,
            );

            if (count($cycleNodeIds) < 2) {
                continue;
            }

            $canonicalNodeIds = $this->canonicalCycle(
                $cycleNodeIds,
            );

            $cycleKey = implode(
                '>',
                $canonicalNodeIds,
            );

            if (isset($reportedCycles[$cycleKey])) {
                continue;
            }

            $reportedCycles[$cycleKey] = true;

            $cycleModules = array_map(
                fn (string $nodeId): string => $this->moduleSlug(
                    graph: $graph,
                    nodeId: $nodeId,
                ),
                $canonicalNodeIds,
            );

            $cycleModules[] = $cycleModules[0];

            $result->add(
                new ValidationIssue(
                    code: 'graph.dependency_cycle',
                    message: sprintf(
                        'Runtime module dependency cycle detected: %s.',
                        implode(
                            ' -> ',
                            $cycleModules,
                        ),
                    ),
                    severity: ValidationSeverity::Error,
                    module: $cycleModules[0],
                    context: [
                        'cycle' => $cycleModules,
                        'node_ids' => array_merge(
                            $canonicalNodeIds,
                            [$canonicalNodeIds[0]],
                        ),
                    ],
                ),
            );
        }

        array_pop($path);
        unset($pathIndexes[$moduleId]);

        $states[$moduleId] = 2;
    }

    /**
     * @param  array<int, string>  $cycle
     * @return array<int, string>
     */
    private function canonicalCycle(
        array $cycle,
    ): array {
        $rotations = [];
        $count = count($cycle);

        for ($index = 0; $index < $count; $index++) {
            $rotations[] = array_merge(
                array_slice($cycle, $index),
                array_slice($cycle, 0, $index),
            );
        }

        usort(
            $rotations,
            static fn (
                array $left,
                array $right,
            ): int => implode('>', $left)
                <=>
                implode('>', $right),
        );

        return $rotations[0];
    }

    private function moduleSlug(
        RuntimeGraph $graph,
        string $nodeId,
    ): string {
        $node = $graph->node($nodeId);

        if ($node?->module !== null) {
            return $node->module;
        }

        return str_starts_with(
            $nodeId,
            'module:',
        )
            ? substr($nodeId, 7)
            : $nodeId;
    }
}
