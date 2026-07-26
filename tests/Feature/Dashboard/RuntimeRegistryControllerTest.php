<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

final class RuntimeRegistryControllerTest extends TestCase
{
    public function test_commands_registry_can_be_viewed(): void
    {
        $response = $this->get(
            route(
                'control-centre.runtime.registries.show',
                ['registry' => 'commands'],
            ),
        );

        $response
            ->assertOk()
            ->assertViewIs('dashboard.registry')
            ->assertViewHas('registry', 'commands')
            ->assertViewHas('title', 'Commands')
            ->assertSee('Runtime registry explorer')
            ->assertSee('Commands');
    }

    public function test_queries_registry_can_be_viewed(): void
    {
        $response = $this->get(
            route(
                'control-centre.runtime.registries.show',
                ['registry' => 'queries'],
            ),
        );

        $response
            ->assertOk()
            ->assertViewIs('dashboard.registry')
            ->assertViewHas('registry', 'queries')
            ->assertViewHas('title', 'Queries');
    }

    public function test_agents_registry_can_be_viewed(): void
    {
        $response = $this->get(
            route(
                'control-centre.runtime.registries.show',
                ['registry' => 'agents'],
            ),
        );

        $response
            ->assertOk()
            ->assertViewIs('dashboard.registry')
            ->assertViewHas('registry', 'agents')
            ->assertViewHas('title', 'Agents')
            ->assertViewHas(
                'groups',
                static function (array $groups): bool {
                    return isset(
                        $groups['crm']['crm.customer.summary'],
                    );
                },
            )
            ->assertSee('crm.customer.summary')
            ->assertSee('CustomerSummaryAgent');
    }

    public function test_events_registry_can_be_viewed(): void
    {
        $response = $this->get(
            route(
                'control-centre.runtime.registries.show',
                ['registry' => 'events'],
            ),
        );

        $response
            ->assertOk()
            ->assertViewIs('dashboard.registry')
            ->assertViewHas('registry', 'events')
            ->assertViewHas('title', 'Events');
    }

    public function test_supported_registry_pages_can_be_viewed(): void
    {
        $registries = [
            'permissions',
            'capabilities',
            'navigation',
            'notifications',
            'scheduled-jobs',
        ];

        foreach ($registries as $registry) {
            $this->get(
                route(
                    'control-centre.runtime.registries.show',
                    ['registry' => $registry],
                ),
            )
                ->assertOk()
                ->assertViewIs('dashboard.registry')
                ->assertViewHas('registry', $registry);
        }
    }

    public function test_registry_entries_link_to_the_registration_inspector(): void
    {
        $this->get(
            route(
                'control-centre.runtime.registries.show',
                ['registry' => 'commands'],
            ),
        )
            ->assertOk()
            ->assertSee(
                route(
                    'control-centre.runtime.inspector.show',
                    [
                        'registry' => 'commands',
                        'module' => 'crm',
                        'key' => 'crm.customer.create',
                    ],
                ),
                false,
            );
    }

    public function test_event_entries_link_to_the_registration_inspector(): void
    {
        $this->get(
            route(
                'control-centre.runtime.registries.show',
                ['registry' => 'events'],
            ),
        )
            ->assertOk()
            ->assertSee(
                route(
                    'control-centre.runtime.inspector.show',
                    [
                        'registry' => 'events',
                        'module' => 'crm',
                        'key' => 'crm.customer.created',
                    ],
                ),
                false,
            );
    }

    public function test_unknown_registry_returns_not_found(): void
    {
        $this->get(
            route(
                'control-centre.runtime.registries.show',
                ['registry' => 'unknown-registry'],
            ),
        )->assertNotFound();
    }
}
