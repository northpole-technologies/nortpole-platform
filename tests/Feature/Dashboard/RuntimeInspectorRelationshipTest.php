<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

final class RuntimeInspectorRelationshipTest extends TestCase
{
    public function test_command_inspector_displays_handler_relationships(): void
    {
        $this->get(
            route(
                'control-centre.runtime.inspector.show',
                [
                    'registry' => 'commands',
                    'module' => 'crm',
                    'key' => 'crm.customer.create',
                ],
            ),
        )
            ->assertOk()
            ->assertSee('Relationships')
            ->assertSee('Handled by')
            ->assertSee(
                'Modules\CRM\Commands\CreateCustomerHandler',
            );
    }

    public function test_navigation_relationship_links_to_its_permission(): void
    {
        $permissionUrl = route(
            'control-centre.runtime.inspector.show',
            [
                'registry' => 'permissions',
                'module' => 'crm',
                'key' => 'crm.customers.view',
            ],
        );

        $this->get(
            route(
                'control-centre.runtime.inspector.show',
                [
                    'registry' => 'navigation',
                    'module' => 'crm',
                    'key' => 'crm.customers.index',
                ],
            ),
        )
            ->assertOk()
            ->assertSee('Requires permission')
            ->assertSee('crm.customers.view')
            ->assertSee(
                $permissionUrl,
                false,
            );
    }

    public function test_navigation_inspector_displays_route_relationships(): void
    {
        $this->get(
            route(
                'control-centre.runtime.inspector.show',
                [
                    'registry' => 'navigation',
                    'module' => 'crm',
                    'key' => 'crm.customers.index',
                ],
            ),
        )
            ->assertOk()
            ->assertSee('Routes to')
            ->assertSee('crm.customers.index')
            ->assertSee('Navigation group')
            ->assertSee('CRM');
    }

    public function test_event_inspector_displays_its_publisher(): void
    {
        $this->get(
            route(
                'control-centre.runtime.inspector.show',
                [
                    'registry' => 'events',
                    'module' => 'crm',
                    'key' => 'crm.customer.created',
                ],
            ),
        )
            ->assertOk()
            ->assertSee('Published by module')
            ->assertSee('crm');
    }
}
