<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

final class RuntimeRegistrationInspectorTest extends TestCase
{
    public function test_a_runtime_registration_can_be_inspected(): void
    {
        $response = $this->get(
            route(
                'control-centre.runtime.inspector.show',
                [
                    'registry' => 'commands',
                    'module' => 'crm',
                    'key' => 'crm.customer.create',
                ],
            ),
        );

        $response
            ->assertOk()
            ->assertViewIs('dashboard.inspector')
            ->assertViewHas(
                'inspection',
                static function (mixed $inspection): bool {
                    return is_array($inspection)
                        && ($inspection['found'] ?? false) === true
                        && ($inspection['reference']['registry'] ?? null)
                            === 'commands'
                        && ($inspection['reference']['module'] ?? null)
                            === 'crm'
                        && ($inspection['reference']['key'] ?? null)
                            === 'crm.customer.create';
                },
            )
            ->assertSee('Runtime registration inspector')
            ->assertSee('crm.customer.create')
            ->assertSee('CreateCustomerHandler')
            ->assertSee('Registration metadata')
            ->assertSee('Source information')
            ->assertSee('Relationships')
            ->assertSee('Dependencies')
            ->assertSee('Warnings');
    }

    public function test_registration_references_are_case_insensitive(): void
    {
        $this->get(
            route(
                'control-centre.runtime.inspector.show',
                [
                    'registry' => 'COMMANDS',
                    'module' => 'CRM',
                    'key' => 'CRM.CUSTOMER.CREATE',
                ],
            ),
        )
            ->assertOk()
            ->assertSee('crm.customer.create')
            ->assertSee('CreateCustomerHandler');
    }

    public function test_an_unknown_registration_returns_not_found(): void
    {
        $this->get(
            route(
                'control-centre.runtime.inspector.show',
                [
                    'registry' => 'commands',
                    'module' => 'crm',
                    'key' => 'crm.command.missing',
                ],
            ),
        )->assertNotFound();
    }

    public function test_an_unknown_registry_returns_not_found(): void
    {
        $this->get(
            route(
                'control-centre.runtime.inspector.show',
                [
                    'registry' => 'unknown',
                    'module' => 'crm',
                    'key' => 'crm.customer.create',
                ],
            ),
        )->assertNotFound();
    }

    public function test_the_registration_page_links_back_to_its_registry(): void
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
            ->assertSee(
                route(
                    'control-centre.runtime.registries.show',
                    ['registry' => 'commands'],
                ),
                false,
            );
    }
}
