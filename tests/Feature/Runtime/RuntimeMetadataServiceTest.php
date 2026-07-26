<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use Northpole\Runtime\Metadata\RuntimeMetadataService;
use Tests\TestCase;

final class RuntimeMetadataServiceTest extends TestCase
{
    public function test_it_returns_a_runtime_summary(): void
    {
        $metadata = $this->app->make(
            RuntimeMetadataService::class,
        );

        $summary = $metadata->summary();

        $this->assertSame(
            'healthy',
            $summary['status'],
        );

        $this->assertGreaterThanOrEqual(
            1,
            $summary['modules']['total'],
        );

        $this->assertGreaterThanOrEqual(
            1,
            $summary['modules']['enabled'],
        );

        $this->assertGreaterThanOrEqual(
            1,
            $summary['commands'],
        );

        $this->assertGreaterThanOrEqual(
            1,
            $summary['queries'],
        );

        $this->assertGreaterThanOrEqual(
            1,
            $summary['agents'],
        );

        $this->assertGreaterThanOrEqual(
            1,
            $summary['published_events'],
        );

        $this->assertGreaterThanOrEqual(
            1,
            $summary['permissions'],
        );

        $this->assertGreaterThanOrEqual(
            1,
            $summary['capabilities'],
        );

        $this->assertGreaterThanOrEqual(
            1,
            $summary['navigation_items'],
        );
    }

    public function test_it_returns_all_module_metadata(): void
    {
        $metadata = $this->app->make(
            RuntimeMetadataService::class,
        );

        $modules = $metadata->modules();

        $crm = collect($modules)->firstWhere(
            'slug',
            'crm',
        );

        $this->assertNotNull($crm);

        $this->assertSame(
            'CRM',
            $crm['name'],
        );

        $this->assertSame(
            '1.0.0',
            $crm['version'],
        );

        $this->assertTrue(
            $crm['enabled'],
        );

        $this->assertArrayHasKey(
            'crm.customer.create',
            $crm['commands'],
        );

        $this->assertArrayHasKey(
            'crm.customer.find',
            $crm['queries'],
        );

        $this->assertArrayHasKey(
            'crm.customer.summary',
            $crm['agents'],
        );

        $this->assertSame(
            'Modules\CRM\Agents\CustomerSummaryAgent',
            $crm['agents']['crm.customer.summary'],
        );

        $this->assertContains(
            'crm.customer.created',
            $crm['published_events'],
        );

        $this->assertContains(
            'crm.customers.view',
            $crm['permissions'],
        );
    }

    public function test_it_returns_agents_grouped_by_module(): void
    {
        $metadata = $this->app->make(
            RuntimeMetadataService::class,
        );

        $agents = $metadata->agents();

        $this->assertArrayHasKey(
            'crm',
            $agents,
        );

        $this->assertSame(
            'Modules\CRM\Agents\CustomerSummaryAgent',
            $agents['crm']['crm.customer.summary'],
        );
    }

    public function test_it_returns_one_module_by_slug(): void
    {
        $metadata = $this->app->make(
            RuntimeMetadataService::class,
        );

        $crm = $metadata->module('CRM');

        $this->assertNotNull($crm);

        $this->assertSame(
            'crm',
            $crm['slug'],
        );

        $this->assertSame(
            'crm.customers.index',
            $crm['navigation'][0]['route'],
        );
    }

    public function test_it_returns_null_for_an_unknown_module(): void
    {
        $metadata = $this->app->make(
            RuntimeMetadataService::class,
        );

        $this->assertNull(
            $metadata->module('missing-module'),
        );
    }

    public function test_it_builds_a_module_dependency_graph(): void
    {
        $metadata = $this->app->make(
            RuntimeMetadataService::class,
        );

        $graph = $metadata->graph();

        $this->assertArrayHasKey(
            'nodes',
            $graph,
        );

        $this->assertArrayHasKey(
            'edges',
            $graph,
        );

        $crm = collect($graph['nodes'])->firstWhere(
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
            'crm',
            $crm['module'],
        );

        $this->assertArrayHasKey(
            'summary',
            $graph,
        );

        $this->assertGreaterThanOrEqual(
            1,
            $graph['summary']['node_types']['command'],
        );

        $this->assertGreaterThanOrEqual(
            1,
            $graph['summary']['edge_types']['handled_by'],
        );
    }
}
