<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Validation\Rules\Graph;

use Northpole\Runtime\Graph\Contracts\RuntimeGraphBuilderContract;
use Northpole\Runtime\Graph\RuntimeGraph;
use Northpole\Runtime\Graph\RuntimeGraphEdge;
use Northpole\Runtime\Graph\RuntimeGraphNode;
use Northpole\Runtime\Validation\Rules\Graph\DependencyCycleValidationRule;
use PHPUnit\Framework\TestCase;

final class DependencyCycleValidationRuleTest extends TestCase
{
    public function test_acyclic_dependencies_pass_validation(): void
    {
        $graph = $this->graph(
            modules: [
                'accounting',
                'core',
                'crm',
            ],
            dependencies: [
                ['accounting', 'crm'],
                ['crm', 'core'],
            ],
        );

        $result = $this->rule($graph)->validate();

        self::assertTrue($result->passes());
        self::assertTrue($result->isEmpty());
    }

    public function test_it_reports_a_self_dependency(): void
    {
        $graph = $this->graph(
            modules: [
                'crm',
            ],
            dependencies: [
                ['crm', 'crm'],
            ],
        );

        $result = $this->rule($graph)->validate();

        self::assertFalse($result->passes());
        self::assertSame(
            1,
            $result->errorCount(),
        );

        $issue = $result->all()[0];

        self::assertSame(
            'graph.self_dependency',
            $issue->code(),
        );

        self::assertSame(
            'crm',
            $issue->module(),
        );

        self::assertSame(
            [
                'module' => 'crm',
                'node_id' => 'module:crm',
            ],
            $issue->context(),
        );
    }

    public function test_it_reports_an_indirect_dependency_cycle(): void
    {
        $graph = $this->graph(
            modules: [
                'crm',
                'reports',
                'sales',
            ],
            dependencies: [
                ['crm', 'sales'],
                ['sales', 'reports'],
                ['reports', 'crm'],
            ],
        );

        $result = $this->rule($graph)->validate();

        self::assertFalse($result->passes());
        self::assertSame(
            1,
            $result->errorCount(),
        );

        $issue = $result->all()[0];

        self::assertSame(
            'graph.dependency_cycle',
            $issue->code(),
        );

        self::assertSame(
            'crm',
            $issue->module(),
        );

        self::assertSame(
            [
                'cycle' => [
                    'crm',
                    'sales',
                    'reports',
                    'crm',
                ],
                'node_ids' => [
                    'module:crm',
                    'module:sales',
                    'module:reports',
                    'module:crm',
                ],
            ],
            $issue->context(),
        );
    }

    public function test_it_reports_each_distinct_dependency_cycle(): void
    {
        $graph = $this->graph(
            modules: [
                'crm',
                'reports',
                'sales',
            ],
            dependencies: [
                ['crm', 'sales'],
                ['sales', 'reports'],
                ['reports', 'crm'],
                ['reports', 'sales'],
            ],
        );

        $result = $this->rule($graph)->validate();

        self::assertFalse($result->passes());
        self::assertSame(
            2,
            $result->errorCount(),
        );

        self::assertSame(
            [
                'graph.dependency_cycle',
                'graph.dependency_cycle',
            ],
            array_map(
                static fn ($issue): string => $issue->code(),
                $result->all(),
            ),
        );

        self::assertSame(
            [
                [
                    'crm',
                    'sales',
                    'reports',
                    'crm',
                ],
                [
                    'reports',
                    'sales',
                    'reports',
                ],
            ],
            array_map(
                static fn ($issue): array => $issue->context()['cycle'],
                $result->all(),
            ),
        );
    }

    public function test_non_dependency_edges_are_ignored(): void
    {
        $graph = $this->graph(
            modules: [
                'crm',
                'inventory',
            ],
            dependencies: [],
        );

        $graph
            ->addEdge(
                new RuntimeGraphEdge(
                    source: 'module:crm',
                    target: 'module:inventory',
                    type: 'declares',
                    label: 'Declares',
                ),
            )
            ->addEdge(
                new RuntimeGraphEdge(
                    source: 'module:inventory',
                    target: 'module:crm',
                    type: 'publishes',
                    label: 'Publishes',
                ),
            );

        $result = $this->rule($graph)->validate();

        self::assertTrue($result->passes());
        self::assertTrue($result->isEmpty());
    }

    /**
     * @param  array<int, string>  $modules
     * @param  array<int, array{0: string, 1: string}>  $dependencies
     */
    private function graph(
        array $modules,
        array $dependencies,
    ): RuntimeGraph {
        $graph = new RuntimeGraph;

        foreach ($modules as $module) {
            $graph->addNode(
                new RuntimeGraphNode(
                    id: "module:{$module}",
                    type: 'module',
                    label: ucfirst($module),
                    module: $module,
                ),
            );
        }

        foreach ($dependencies as [$source, $target]) {
            $graph->addEdge(
                new RuntimeGraphEdge(
                    source: "module:{$source}",
                    target: "module:{$target}",
                    type: 'depends_on',
                    label: 'Depends on',
                ),
            );
        }

        return $graph;
    }

    private function rule(
        RuntimeGraph $graph,
    ): DependencyCycleValidationRule {
        $builder = new class($graph) implements RuntimeGraphBuilderContract
        {
            public function __construct(
                private readonly RuntimeGraph $graph,
            ) {}

            public function build(): RuntimeGraph
            {
                return $this->graph;
            }
        };

        return new DependencyCycleValidationRule(
            $builder,
        );
    }
}
