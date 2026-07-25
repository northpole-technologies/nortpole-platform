<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

final class RuntimeDashboardTest extends TestCase
{
    public function test_the_control_centre_can_be_viewed(): void
    {
        $this->get('/control-centre')
            ->assertOk()
            ->assertViewIs('dashboard.runtime')
            ->assertSee('NorthPole Control Centre')
            ->assertSee('Runtime health')
            ->assertSee('Runtime registries')
            ->assertSee('Discovered modules');
    }

    public function test_the_dashboard_displays_runtime_modules(): void
    {
        $this->get('/control-centre')
            ->assertOk()
            ->assertSee('SantaBuddy')
            ->assertSee('HomeDoctor')
            ->assertSee('Inventory')
            ->assertSee('CRM');
    }

    public function test_the_home_page_redirects_to_the_control_centre(): void
    {
        $this->get('/')
            ->assertRedirect('/control-centre');
    }
}
