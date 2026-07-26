<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Graph;

use InvalidArgumentException;
use Northpole\Runtime\Graph\RuntimeGraph;
use Northpole\Runtime\Graph\RuntimeGraphEdge;
use Northpole\Runtime\Graph\RuntimeGraphNode;
use PHPUnit\Framework\TestCase;

final class RuntimeGraphTest extends TestCase
{
    public function test_it_collects_nodes_and_edges(): void
    {
        $graph = new RuntimeGraph;

        $graph
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
                    id: 'module:inventory',
                    type: 'module',
                    label: 'Inventory',
                    module: 'inventory',
                ),
            )
            ->addEdge(
                new RuntimeGraphEdge(
                    source: 'module:crm',
                    target: 'module:inventory',
                    type: 'depends_on',
                    label: 'Depends on',
                ),
            );

        $payload = $graph->toArray();

        $this->assertCount(
            2,
            $payload['nodes'],
        );

        $this->assertCount(
            1,
            $payload['edges'],
        );

        $this->assertSame(
            2,
            $payload['summary']['nodes'],
        );

        $this->assertSame(
            1,
            $payload['summary']['edges'],
        );

        $this->assertSame(
            [
                'module' => 2,
            ],
            $payload['summary']['node_types'],
        );

        $this->assertSame(
            [
                'depends_on' => 1,
            ],
            $payload['summary']['edge_types'],
        );
    }

    public function test_it_deduplicates_identical_nodes_and_edges(): void
    {
        $graph = new RuntimeGraph;

        $node = new RuntimeGraphNode(
            id: 'module:crm',
            type: 'module',
            label: 'CRM',
            module: 'crm',
        );

        $edge = new RuntimeGraphEdge(
            source: 'module:crm',
            target: 'module:inventory',
            type: 'depends_on',
            label: 'Depends on',
        );

        $graph
            ->addNode($node)
            ->addNode($node)
            ->addEdge($edge)
            ->addEdge($edge);

        $this->assertCount(
            1,
            $graph->nodes(),
        );

        $this->assertCount(
            1,
            $graph->edges(),
        );
    }

    public function test_it_rejects_conflicting_duplicate_nodes(): void
    {
        $graph = new RuntimeGraph;

        $graph->addNode(
            new RuntimeGraphNode(
                id: 'module:crm',
                type: 'module',
                label: 'CRM',
            ),
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $graph->addNode(
            new RuntimeGraphNode(
                id: 'module:crm',
                type: 'module',
                label: 'Different CRM',
            ),
        );
    }
}
