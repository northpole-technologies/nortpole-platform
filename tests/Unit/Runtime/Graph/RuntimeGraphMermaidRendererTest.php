<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Graph;

use Northpole\Runtime\Graph\RuntimeGraph;
use Northpole\Runtime\Graph\RuntimeGraphEdge;
use Northpole\Runtime\Graph\RuntimeGraphMermaidRenderer;
use Northpole\Runtime\Graph\RuntimeGraphNode;
use PHPUnit\Framework\TestCase;

final class RuntimeGraphMermaidRendererTest extends TestCase
{
    public function test_it_renders_a_mermaid_runtime_graph(): void
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
                    id: 'command:crm:crm.customer.create',
                    type: 'command',
                    label: 'crm.customer.create',
                    module: 'crm',
                ),
            )
            ->addNode(
                new RuntimeGraphNode(
                    id: 'command_handler:create-customer',
                    type: 'command_handler',
                    label: 'CreateCustomerHandler',
                    module: 'crm',
                ),
            )
            ->addEdge(
                new RuntimeGraphEdge(
                    source: 'module:crm',
                    target: 'command:crm:crm.customer.create',
                    type: 'declares',
                    label: 'Declares',
                ),
            )
            ->addEdge(
                new RuntimeGraphEdge(
                    source: 'command:crm:crm.customer.create',
                    target: 'command_handler:create-customer',
                    type: 'handled_by',
                    label: 'Handled by',
                ),
            );

        $output = (new RuntimeGraphMermaidRenderer)
            ->render(
                $graph,
            );

        $this->assertStringContainsString(
            '# NorthPole Runtime Graph',
            $output,
        );

        $this->assertStringContainsString(
            '```mermaid',
            $output,
        );

        $this->assertStringContainsString(
            'flowchart LR',
            $output,
        );

        $this->assertStringContainsString(
            'CRM',
            $output,
        );

        $this->assertStringContainsString(
            'crm.customer.create',
            $output,
        );

        $this->assertStringContainsString(
            'CreateCustomerHandler',
            $output,
        );

        $this->assertStringContainsString(
            '-->|Declares|',
            $output,
        );

        $this->assertStringContainsString(
            '-->|Handled by|',
            $output,
        );
    }

    public function test_it_renders_supported_node_shapes(): void
    {
        $graph = new RuntimeGraph;

        $graph
            ->addNode(
                new RuntimeGraphNode(
                    id: 'module:crm',
                    type: 'module',
                    label: 'CRM',
                ),
            )
            ->addNode(
                new RuntimeGraphNode(
                    id: 'query:crm:customer.find',
                    type: 'query',
                    label: 'customer.find',
                ),
            )
            ->addNode(
                new RuntimeGraphNode(
                    id: 'event:customer-created',
                    type: 'event',
                    label: 'CustomerCreated',
                ),
            );

        $output = (new RuntimeGraphMermaidRenderer)
            ->render(
                $graph,
            );

        $this->assertMatchesRegularExpression(
            '/node_[a-f0-9]{16}\["CRM"\]/',
            $output,
        );

        $this->assertMatchesRegularExpression(
            '/node_[a-f0-9]{16}\(\["customer\.find"\]\)/',
            $output,
        );

        $this->assertMatchesRegularExpression(
            '/node_[a-f0-9]{16}\{\{"CustomerCreated"\}\}/',
            $output,
        );
    }

    public function test_it_escapes_node_and_edge_labels(): void
    {
        $graph = new RuntimeGraph;

        $graph
            ->addNode(
                new RuntimeGraphNode(
                    id: 'module:source',
                    type: 'module',
                    label: 'Source "Module"',
                ),
            )
            ->addNode(
                new RuntimeGraphNode(
                    id: 'module:target',
                    type: 'module',
                    label: "Target\nModule",
                ),
            )
            ->addEdge(
                new RuntimeGraphEdge(
                    source: 'module:source',
                    target: 'module:target',
                    type: 'depends_on',
                    label: 'Depends | on',
                ),
            );

        $output = (new RuntimeGraphMermaidRenderer)
            ->render(
                $graph,
            );

        $this->assertStringContainsString(
            'Source \"Module\"',
            $output,
        );

        $this->assertStringContainsString(
            'Target Module',
            $output,
        );

        $this->assertStringContainsString(
            'Depends / on',
            $output,
        );
    }
}
