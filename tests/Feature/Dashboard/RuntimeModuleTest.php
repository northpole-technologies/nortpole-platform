<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

final class RuntimeModuleTest extends TestCase
{
    public function test_a_runtime_module_can_be_inspected(): void
    {
        $this->get('/control-centre/modules/crm')
            ->assertOk()
            ->assertViewIs('dashboard.module')
            ->assertSee('CRM')
            ->assertSee('Runtime module inspector')
            ->assertSee('crm.customer.create')
            ->assertSee('crm.customer.find')
            ->assertSee('crm.customer.created')
            ->assertSee('crm.customers.view');
    }

    public function test_module_slugs_are_case_insensitive(): void
    {
        $this->get('/control-centre/modules/CRM')
            ->assertOk()
            ->assertSee('CRM')
            ->assertSee('crm.customer.create');
    }

    public function test_an_unknown_runtime_module_returns_not_found(): void
    {
        $this->get('/control-centre/modules/missing-module')
            ->assertNotFound();
    }

    public function test_dashboard_module_cards_link_to_the_inspector(): void
    {
        $this->get('/control-centre')
            ->assertOk()
            ->assertSee(
                route(
                    'control-centre.modules.show',
                    ['slug' => 'crm'],
                ),
                false,
            );
    }
}
