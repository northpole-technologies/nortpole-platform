<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Validation\Rules\Graph;

use Northpole\Runtime\Graph\Contracts\RuntimeGraphBuilderContract;
use Northpole\Runtime\Graph\RuntimeGraph;
use Northpole\Runtime\Graph\RuntimeGraphEdge;
use Northpole\Runtime\Graph\RuntimeGraphNode;
use Northpole\Runtime\Validation\Rules\Graph\OrphanNodeValidationRule;
use Northpole\Runtime\Validation\ValidationIssue;
use PHPUnit\Framework\TestCase;

final class OrphanNodeValidationRuleTest extends TestCase
{
    public function test_connected_nodes_pass_validation(): void
    {
        $graph = (new RuntimeGraph)
            ->addNode(
                new RuntimeGraphNode(
                    id: 'module:crm',
                    type: 'module',
                    label: 'CRM',
                    module: 'crm',
                ),
            )
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
                    target: 'command:crm:customer.create',
                    type: 'declares',
                    label: 'Declares',
                ),
            );

        $result = $this->rule($graph)->validate();

        self::assertTrue($result->passes());
        self::assertTrue($result->isEmpty());
    }

    public function test_it_reports_an_orphaned_non_module_node(): void
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

        $result = $this->rule($graph)->validate();

        self::assertTrue($result->passes());
        self::assertSame(
            1,
            $result->warningCount(),
        );

        $issue = $result->all()[0];

        self::assertSame(
            'graph.node_orphaned',
            $issue->code(),
        );

        self::assertSame(
            'crm',
            $issue->module(),
        );

        self::assertSame(
            [
                'node_id' => 'command:crm:customer.create',
                'node_type' => 'command',
                'node_label' => 'crm.customer.create',
            ],
            $issue->context(),
        );
    }

    public function test_standalone_module_nodes_are_allowed(): void
    {
        $graph = (new RuntimeGraph)
            ->addNode(
                new RuntimeGraphNode(
                    id: 'module:inventory',
                    type: 'module',
                    label: 'Inventory',
                    module: 'inventory',
                ),
            );

        $result = $this->rule($graph)->validate();

        self::assertTrue($result->passes());
        self::assertTrue($result->isEmpty());
    }

    public function test_it_reports_each_orphaned_node(): void
    {
        $graph = (new RuntimeGraph)
            ->addNode(
                new RuntimeGraphNode(
                    id: 'query:crm:customer.find',
                    type: 'query',
                    label: 'crm.customer.find',
                    module: 'crm',
                ),
            )
            ->addNode(
                new RuntimeGraphNode(
                    id: 'query_handler:missing',
                    type: 'query_handler',
                    label: 'MissingQueryHandler',
                    module: 'crm',
                ),
            );

        $result = $this->rule($graph)->validate();

        self::assertSame(
            2,
            $result->warningCount(),
        );

        self::assertSame(
            [
                'graph.node_orphaned',
                'graph.node_orphaned',
            ],
            array_map(
                static fn (
                    ValidationIssue $issue,
                ): string => $issue->code(),
                $result->all(),
            ),
        );
    }

    private function rule(
        RuntimeGraph $graph,
    ): OrphanNodeValidationRule {
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

        return new OrphanNodeValidationRule(
            $builder,
        );
    }
}
