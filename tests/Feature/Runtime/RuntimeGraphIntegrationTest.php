<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use Northpole\Runtime\Graph\RuntimeGraphBuilder;
use Northpole\Runtime\Metadata\RuntimeMetadataService;
use Tests\TestCase;

final class RuntimeGraphIntegrationTest extends TestCase
{
    public function test_metadata_service_uses_the_typed_graph_builder(): void
    {
        $metadata = $this->app->make(
            RuntimeMetadataService::class,
        );

        $builder = $this->app->make(
            RuntimeGraphBuilder::class,
        );

        $this->assertSame(
            $builder->build()->toArray(),
            $metadata->graph(),
        );
    }

    public function test_graph_api_exposes_runtime_topology(): void
    {
        $response = $this->getJson(
            '/api/runtime/graph',
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.nodes.0.type',
                'agent',
            )
            ->assertJsonStructure([
                'data' => [
                    'nodes',
                    'edges',
                    'summary' => [
                        'nodes',
                        'edges',
                        'node_types',
                        'edge_types',
                    ],
                ],
            ]);

        $nodes = collect(
            $response->json('data.nodes'),
        );

        $edges = collect(
            $response->json('data.edges'),
        );

        $this->assertNotNull(
            $nodes->firstWhere(
                'id',
                'module:crm',
            ),
        );

        $this->assertNotNull(
            $nodes->firstWhere(
                'id',
                'command:crm:crm.customer.create',
            ),
        );

        $this->assertNotNull(
            $edges->first(
                static fn (array $edge): bool => $edge['source']
                        === 'command:crm:crm.customer.create'
                    && $edge['type'] === 'handled_by',
            ),
        );
    }

    public function test_dashboard_adapts_topology_to_module_cards(): void
    {
        $this->get(
            '/control-centre/runtime/graph',
        )
            ->assertOk()
            ->assertViewIs(
                'dashboard.graph',
            )
            ->assertViewHas(
                'topologySummary',
            )
            ->assertSee('CRM')
            ->assertSee('Inventory')
            ->assertSee('HomeDoctor')
            ->assertSee('SantaBuddy');
    }
}
