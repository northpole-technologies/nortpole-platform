<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class RuntimeMetadataApiTest extends TestCase
{
    public function test_runtime_summary_can_be_viewed(): void
    {
        $response = $this->getJson(
            '/api/runtime',
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.status',
                'healthy',
            )
            ->assertJsonStructure([
                'data' => [
                    'status',
                    'modules' => [
                        'total',
                        'enabled',
                        'disabled',
                    ],
                    'commands',
                    'queries',
                    'published_events',
                    'event_subscriptions',
                    'permissions',
                    'capabilities',
                    'navigation_items',
                    'notifications',
                    'scheduled_jobs',
                ],
            ]);

        $this->assertGreaterThanOrEqual(
            1,
            $response->json('data.modules.total'),
        );
    }

    public function test_runtime_modules_can_be_listed(): void
    {
        $response = $this->getJson(
            '/api/runtime/modules',
        );

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'name',
                        'slug',
                        'version',
                        'description',
                        'enabled',
                        'path',
                        'manifest_path',
                        'providers',
                        'dependencies',
                        'routes',
                        'views',
                        'migrations',
                        'configuration',
                        'settings',
                        'capabilities',
                        'permissions',
                        'navigation',
                        'published_events',
                        'event_subscribers',
                        'notifications',
                        'commands',
                        'queries',
                        'scheduled_jobs',
                    ],
                ],
            ]);

        $crm = collect(
            $response->json('data'),
        )->firstWhere(
            'slug',
            'crm',
        );

        $this->assertNotNull($crm);

        $this->assertSame(
            'CRM',
            $crm['name'],
        );
    }

    public function test_one_runtime_module_can_be_viewed(): void
    {
        $response = $this->getJson(
            '/api/runtime/modules/crm',
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.name',
                'CRM',
            )
            ->assertJsonPath(
                'data.slug',
                'crm',
            )
            ->assertJsonPath(
                'data.version',
                '1.0.0',
            )
            ->assertJsonPath(
                'data.enabled',
                true,
            );

        $this->assertSame(
            'Modules\CRM\Commands\CreateCustomerHandler',
            $response->json('data.commands')['crm.customer.create'],
        );

        $this->assertSame(
            'Modules\CRM\Queries\FindCustomerHandler',
            $response->json('data.queries')['crm.customer.find'],
        );

        $response
            ->assertJsonFragment([
                'crm.customer.created',
            ])
            ->assertJsonFragment([
                'crm.customers.view',
            ]);
    }

    public function test_module_slug_is_case_insensitive(): void
    {
        $this->getJson(
            '/api/runtime/modules/CRM',
        )
            ->assertOk()
            ->assertJsonPath(
                'data.slug',
                'crm',
            );
    }

    public function test_unknown_runtime_module_returns_not_found(): void
    {
        $this->getJson(
            '/api/runtime/modules/missing-module',
        )
            ->assertNotFound()
            ->assertJsonPath(
                'message',
                'NorthPole module [missing-module] was not found.',
            );
    }

    public function test_runtime_dependency_graph_can_be_viewed(): void
    {
        $response = $this->getJson(
            '/api/runtime/graph',
        );

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'nodes' => [
                        '*' => [
                            'id',
                            'type',
                            'label',
                            'module',
                            'metadata',
                        ],
                    ],
                    'edges' => [
                        '*' => [
                            'source',
                            'target',
                            'type',
                            'label',
                            'metadata',
                        ],
                    ],
                    'summary' => [
                        'nodes',
                        'edges',
                        'node_types',
                        'edge_types',
                    ],
                ],
            ]);

        $crm = collect(
            $response->json('data.nodes'),
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

        $command = collect(
            $response->json('data.nodes'),
        )->firstWhere(
            'id',
            'command:crm:crm.customer.create',
        );

        $this->assertNotNull($command);

        $this->assertSame(
            'commands',
            $command['metadata']['registry'],
        );

        $this->assertGreaterThanOrEqual(
            1,
            $response->json(
                'data.summary.edge_types.handled_by',
            ),
        );
    }
}
