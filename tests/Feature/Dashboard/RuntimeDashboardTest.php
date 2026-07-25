<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use Northpole\Runtime\Health\RuntimeHealth;
use Tests\TestCase;

final class RuntimeDashboardTest extends TestCase
{
    public function test_the_control_centre_can_be_viewed(): void
    {
        $this->get('/control-centre')
            ->assertOk()
            ->assertViewIs('dashboard.runtime')
            ->assertViewHas(
                'runtimeHealth',
                static fn (mixed $health): bool =>
                    $health instanceof RuntimeHealth,
            )
            ->assertViewHas(
                'runtimeHealthSummary',
                static fn (mixed $summary): bool =>
                    is_array($summary)
                    && array_key_exists('score', $summary)
                    && array_key_exists(
                        'healthyModules',
                        $summary,
                    )
                    && array_key_exists('issues', $summary),
            )
            ->assertSee('NorthPole Control Centre')
            ->assertSee('Runtime health')
            ->assertSee('Runtime score')
            ->assertSee('Healthy modules')
            ->assertSee('Runtime issues')
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
            ->assertSee('CRM')
            ->assertSee('Health');
    }

    public function test_the_dashboard_receives_module_health_data(): void
    {
        $this->get('/control-centre')
            ->assertOk()
            ->assertViewHas(
                'modules',
                static function (
                    mixed $modules,
                ): bool {
                    if (
                        ! is_array($modules)
                        || $modules === []
                    ) {
                        return false;
                    }

                    foreach ($modules as $module) {
                        if (
                            ! array_key_exists(
                                'healthStatus',
                                $module,
                            )
                            || ! array_key_exists(
                                'healthScore',
                                $module,
                            )
                            || ! array_key_exists(
                                'healthChecks',
                                $module,
                            )
                        ) {
                            return false;
                        }
                    }

                    return true;
                },
            );
    }

    public function test_the_home_page_redirects_to_the_control_centre(): void
    {
        $this->get('/')
            ->assertRedirect('/control-centre');
    }
}