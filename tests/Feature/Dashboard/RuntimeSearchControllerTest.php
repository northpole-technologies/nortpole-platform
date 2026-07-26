<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

final class RuntimeSearchControllerTest extends TestCase
{
    public function test_runtime_search_can_be_viewed(): void
    {
        $this->get(
            route('control-centre.runtime.search'),
        )
            ->assertOk()
            ->assertViewIs('dashboard.search')
            ->assertViewHas('query', '')
            ->assertViewHas('groups', [])
            ->assertSee('Runtime Search')
            ->assertSee(
                'Enter a search term to inspect the runtime.',
            );
    }

    public function test_runtime_registrations_can_be_searched(): void
    {
        $response = $this->get(
            route(
                'control-centre.runtime.search',
                ['q' => 'customer.summary'],
            ),
        );

        $response
            ->assertOk()
            ->assertViewHas('query', 'customer.summary')
            ->assertViewHas(
                'matches',
                static function (mixed $matches): bool {
                    if (! is_array($matches)) {
                        return false;
                    }

                    foreach ($matches as $match) {
                        if (
                            ($match['registry'] ?? null) === 'agents'
                            && ($match['module'] ?? null) === 'crm'
                            && ($match['key'] ?? null)
                                === 'crm.customer.summary'
                        ) {
                            return true;
                        }
                    }

                    return false;
                },
            )
            ->assertSee('crm.customer.summary')
            ->assertSee('CustomerSummaryAgent');
    }

    public function test_search_results_link_to_the_registration_inspector(): void
    {
        $this->get(
            route(
                'control-centre.runtime.search',
                ['q' => 'customer.summary'],
            ),
        )
            ->assertOk()
            ->assertSee(
                route(
                    'control-centre.runtime.inspector.show',
                    [
                        'registry' => 'agents',
                        'module' => 'crm',
                        'key' => 'crm.customer.summary',
                    ],
                ),
                false,
            );
    }

    public function test_search_can_be_filtered_by_module(): void
    {
        $response = $this->get(
            route(
                'control-centre.runtime.search',
                [
                    'q' => 'customer',
                    'module' => 'crm',
                ],
            ),
        );

        $response
            ->assertOk()
            ->assertViewHas('selectedModule', 'crm')
            ->assertViewHas(
                'matches',
                static function (mixed $matches): bool {
                    if (
                        ! is_array($matches)
                        || $matches === []
                    ) {
                        return false;
                    }

                    foreach ($matches as $match) {
                        if (
                            ($match['module'] ?? null)
                            !== 'crm'
                        ) {
                            return false;
                        }
                    }

                    return true;
                },
            )
            ->assertSee('crm.customer.create')
            ->assertSee('crm.customer.summary');
    }

    public function test_filtered_search_results_link_to_the_registration_inspector(): void
    {
        $this->get(
            route(
                'control-centre.runtime.search',
                [
                    'q' => 'customer.create',
                    'module' => 'crm',
                ],
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

    public function test_search_returns_an_empty_result_set_for_unknown_terms(): void
    {
        $this->get(
            route(
                'control-centre.runtime.search',
                ['q' => 'definitely-not-a-runtime-registration'],
            ),
        )
            ->assertOk()
            ->assertViewHas('matches', [])
            ->assertSee(
                'No matching runtime registrations were found.',
            );
    }

    public function test_control_centre_links_to_runtime_search(): void
    {
        $this->get('/control-centre')
            ->assertOk()
            ->assertSee('Search')
            ->assertSee(
                route(
                    'control-centre.runtime.search',
                ),
                false,
            );
    }
}
