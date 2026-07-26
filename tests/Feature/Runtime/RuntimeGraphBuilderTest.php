<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use Northpole\Runtime\Graph\RuntimeGraphBuilder;
use Tests\TestCase;

final class RuntimeGraphBuilderTest extends TestCase
{
    public function test_it_builds_runtime_module_nodes(): void
    {
        $graph = $this->app->make(
            RuntimeGraphBuilder::class,
        )->build();

        $crm = collect(
            $graph->toArray()['nodes'],
        )->firstWhere(
            'id',
            'module:crm',
        );

        $this->assertNotNull($crm);

        $this->assertSame(
            'module',
            $crm['type'],
        );

        $this->assertSame(
            'CRM',
            $crm['label'],
        );

        $this->assertSame(
            '1.0.0',
            $crm['metadata']['version'],
        );

        $this->assertTrue(
            $crm['metadata']['enabled'],
        );
    }

    public function test_it_builds_command_and_handler_relationships(): void
    {
        $payload = $this->app->make(
            RuntimeGraphBuilder::class,
        )
            ->build()
            ->toArray();

        $commandNode = collect(
            $payload['nodes'],
        )->firstWhere(
            'id',
            'command:crm:crm.customer.create',
        );

        $this->assertNotNull(
            $commandNode,
        );

        $this->assertSame(
            'commands',
            $commandNode['metadata']['registry'],
        );

        $handlerNode = collect(
            $payload['nodes'],
        )->first(
            static fn (array $node): bool => $node['type'] === 'command_handler'
                && $node['label'] === 'CreateCustomerHandler',
        );

        $this->assertNotNull(
            $handlerNode,
        );

        $handledByEdge = collect(
            $payload['edges'],
        )->first(
            static fn (array $edge): bool => $edge['source']
                    === 'command:crm:crm.customer.create'
                && $edge['type'] === 'handled_by',
        );

        $this->assertNotNull(
            $handledByEdge,
        );

        $this->assertSame(
            $handlerNode['id'],
            $handledByEdge['target'],
        );
    }

    public function test_it_builds_query_and_agent_nodes(): void
    {
        $payload = $this->app->make(
            RuntimeGraphBuilder::class,
        )
            ->build()
            ->toArray();

        $query = collect(
            $payload['nodes'],
        )->firstWhere(
            'id',
            'query:crm:crm.customer.find',
        );

        $agent = collect(
            $payload['nodes'],
        )->firstWhere(
            'id',
            'agent:crm:crm.customer.summary',
        );

        $this->assertNotNull(
            $query,
        );

        $this->assertNotNull(
            $agent,
        );

        $this->assertSame(
            'query',
            $query['type'],
        );

        $this->assertSame(
            'agent',
            $agent['type'],
        );
    }

    public function test_it_builds_published_event_relationships(): void
    {
        $payload = $this->app->make(
            RuntimeGraphBuilder::class,
        )
            ->build()
            ->toArray();

        $event = collect(
            $payload['nodes'],
        )->first(
            static fn (array $node): bool => $node['type'] === 'event'
                && $node['label'] === 'crm.customer.created',
        );

        $this->assertNotNull(
            $event,
        );

        $publishes = collect(
            $payload['edges'],
        )->first(
            static fn (array $edge): bool => $edge['source'] === 'module:crm'
                && $edge['type'] === 'publishes',
        );

        $this->assertNotNull(
            $publishes,
        );

        $this->assertSame(
            $event['id'],
            $publishes['target'],
        );
    }

    public function test_it_returns_graph_summary_counts(): void
    {
        $summary = $this->app->make(
            RuntimeGraphBuilder::class,
        )
            ->build()
            ->toArray()['summary'];

        $this->assertGreaterThanOrEqual(
            4,
            $summary['node_types']['module'],
        );

        $this->assertGreaterThanOrEqual(
            1,
            $summary['node_types']['command'],
        );

        $this->assertGreaterThanOrEqual(
            1,
            $summary['node_types']['query'],
        );

        $this->assertGreaterThanOrEqual(
            1,
            $summary['node_types']['agent'],
        );

        $this->assertGreaterThanOrEqual(
            1,
            $summary['node_types']['event'],
        );
    }
}
