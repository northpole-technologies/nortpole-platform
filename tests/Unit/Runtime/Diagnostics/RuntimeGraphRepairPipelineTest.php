<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Diagnostics;

use Northpole\Runtime\Graph\Contracts\RuntimeGraphBuilderContract;
use Northpole\Runtime\Graph\RuntimeGraph;
use Northpole\Runtime\Graph\RuntimeGraphEdge;
use Northpole\Runtime\Graph\RuntimeGraphNode;
use Northpole\Runtime\Repair\Providers\GraphRepairProvider;
use Northpole\Runtime\Repair\RepairResult;
use Northpole\Runtime\Repair\RuntimeRepairEngine;
use Northpole\Runtime\Validation\Rules\Graph\DependencyCycleValidationRule;
use Northpole\Runtime\Validation\Rules\Graph\OrphanNodeValidationRule;
use Northpole\Runtime\Validation\RuntimeValidationEngine;
use Northpole\Runtime\Validation\ValidationResult;
use PHPUnit\Framework\TestCase;

final class RuntimeGraphRepairPipelineTest extends TestCase
{
    public function test_an_orphaned_node_produces_a_repair_recommendation(): void
    {
        $graph = (new RuntimeGraph)
            ->addNode(
                new RuntimeGraphNode(
                    id: 'command:crm:customer.create',
                    type: 'command',
                    label: 'crm.customer.create',
                    module: 'crm',
                ),
            );

        [
            'validation' => $validation,
            'repairs' => $repairs,
        ] = $this->diagnose($graph);

        self::assertSame(
            1,
            $validation->count(),
        );

        self::assertSame(
            'graph.node_orphaned',
            $validation->all()[0]->code(),
        );

        self::assertSame(
            1,
            $repairs->count(),
        );

        self::assertSame(
            'repair.graph.connect_orphaned_node',
            $repairs->all()[0]->code(),
        );

        self::assertSame(
            'crm',
            $repairs->all()[0]->module(),
        );

        self::assertSame(
            $validation->all()[0],
            $repairs->all()[0]->issue(),
        );
    }

    public function test_a_self_dependency_produces_a_repair_recommendation(): void
    {
        $graph = $this->moduleGraph([
            'crm',
        ]);

        $graph->addEdge(
            new RuntimeGraphEdge(
                source: 'module:crm',
                target: 'module:crm',
                type: 'depends_on',
                label: 'Depends on',
            ),
        );

        [
            'validation' => $validation,
            'repairs' => $repairs,
        ] = $this->diagnose($graph);

        self::assertFalse(
            $validation->passes(),
        );

        self::assertSame(
            1,
            $validation->errorCount(),
        );

        self::assertSame(
            'graph.self_dependency',
            $validation->all()[0]->code(),
        );

        self::assertSame(
            1,
            $repairs->count(),
        );

        self::assertSame(
            'repair.graph.remove_self_dependency',
            $repairs->all()[0]->code(),
        );

        self::assertStringContainsString(
            'crm',
            $repairs->all()[0]->description(),
        );
    }

    public function test_a_dependency_cycle_produces_a_repair_recommendation(): void
    {
        $graph = $this->moduleGraph([
            'crm',
            'reports',
            'sales',
        ]);

        $graph
            ->addEdge(
                new RuntimeGraphEdge(
                    source: 'module:crm',
                    target: 'module:sales',
                    type: 'depends_on',
                    label: 'Depends on',
                ),
            )
            ->addEdge(
                new RuntimeGraphEdge(
                    source: 'module:sales',
                    target: 'module:reports',
                    type: 'depends_on',
                    label: 'Depends on',
                ),
            )
            ->addEdge(
                new RuntimeGraphEdge(
                    source: 'module:reports',
                    target: 'module:crm',
                    type: 'depends_on',
                    label: 'Depends on',
                ),
            );

        [
            'validation' => $validation,
            'repairs' => $repairs,
        ] = $this->diagnose($graph);

        self::assertFalse(
            $validation->passes(),
        );

        self::assertSame(
            1,
            $validation->errorCount(),
        );

        self::assertSame(
            'graph.dependency_cycle',
            $validation->all()[0]->code(),
        );

        self::assertSame(
            [
                'crm',
                'sales',
                'reports',
                'crm',
            ],
            $validation->all()[0]->context()['cycle'],
        );

        self::assertSame(
            1,
            $repairs->count(),
        );

        self::assertSame(
            'repair.graph.break_dependency_cycle',
            $repairs->all()[0]->code(),
        );

        self::assertStringContainsString(
            'crm -> sales -> reports -> crm',
            $repairs->all()[0]->description(),
        );
    }

    public function test_a_healthy_graph_produces_no_issues_or_repairs(): void
    {
        $graph = $this->moduleGraph([
            'core',
            'crm',
        ]);

        $graph
            ->addNode(
                new RuntimeGraphNode(
                    id: 'command:crm:customer.create',
                    type: 'command',
                    label: 'crm.customer.create',
                    module: 'crm',
                ),
            )
            ->addEdge(
                new RuntimeGraphEdge(
                    source: 'module:crm',
                    target: 'module:core',
                    type: 'depends_on',
                    label: 'Depends on',
                ),
            )
            ->addEdge(
                new RuntimeGraphEdge(
                    source: 'module:crm',
                    target: 'command:crm:customer.create',
                    type: 'declares',
                    label: 'Declares',
                ),
            );

        [
            'validation' => $validation,
            'repairs' => $repairs,
        ] = $this->diagnose($graph);

        self::assertTrue(
            $validation->passes(),
        );

        self::assertTrue(
            $validation->isEmpty(),
        );

        self::assertTrue(
            $repairs->isEmpty(),
        );
    }

    /**
     * @param  array<int, string>  $modules
     */
    private function moduleGraph(
        array $modules,
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

        return $graph;
    }

    /**
     * @return array{
     *     validation: ValidationResult,
     *     repairs: RepairResult
     * }
     */
    private function diagnose(
        RuntimeGraph $graph,
    ): array {
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

        $validationEngine = (
            new RuntimeValidationEngine
        )->registerMany([
            new DependencyCycleValidationRule(
                $builder,
            ),
            new OrphanNodeValidationRule(
                $builder,
            ),
        ]);

        $repairEngine = (
            new RuntimeRepairEngine
        )->registerProvider(
            new GraphRepairProvider,
        );

        $validationResult = $validationEngine->validate();

        return [
            'validation' => $validationResult,
            'repairs' => $repairEngine->recommend(
                $validationResult,
            ),
        ];
    }
}
