<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

final class RuntimeGraphTest extends TestCase
{
    public function test_the_runtime_dependency_graph_can_be_viewed(): void
    {
        $this->get('/control-centre/runtime/graph')
            ->assertOk()
            ->assertViewIs('dashboard.graph')
            ->assertSee('Dependency Graph')
            ->assertSee('Runtime modules')
            ->assertSee('Dependency relationships');
    }

    public function test_the_graph_displays_runtime_modules(): void
    {
        $this->get('/control-centre/runtime/graph')
            ->assertOk()
            ->assertSee('CRM')
            ->assertSee('Inventory')
            ->assertSee('HomeDoctor')
            ->assertSee('SantaBuddy');
    }

    public function test_graph_nodes_link_to_module_inspectors(): void
    {
        $this->get('/control-centre/runtime/graph')
            ->assertOk()
            ->assertSee(
                route(
                    'control-centre.modules.show',
                    ['slug' => 'crm'],
                ),
                false,
            );
    }

    public function test_the_dashboard_links_to_the_dependency_graph(): void
    {
        $this->get('/control-centre')
            ->assertOk()
            ->assertSee('Dependency Graph')
            ->assertSee(
                route('control-centre.runtime.graph'),
                false,
            );
    }
}
