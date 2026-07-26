<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use Northpole\Runtime\Repair\RepairResult;
use Northpole\Runtime\Validation\ValidationResult;
use Tests\TestCase;

final class RuntimeDiagnosticsControllerTest extends TestCase
{
    public function test_runtime_diagnostics_can_be_viewed(): void
    {
        $this->get(
            route('control-centre.runtime.diagnostics'),
        )
            ->assertOk()
            ->assertViewIs('dashboard.diagnostics')
            ->assertViewHas('runtimeHealth')
            ->assertViewHas('validationResult')
            ->assertViewHas('validationSummary')
            ->assertViewHas('validationIssues')
            ->assertViewHas('repairResult')
            ->assertViewHas('repairSummary')
            ->assertViewHas('repairRecommendations')
            ->assertViewHas('summary')
            ->assertViewHas('registryCounts')
            ->assertViewHas('moduleIssues')
            ->assertSee('Runtime Diagnostics')
            ->assertSee('Runtime validation')
            ->assertSee('Validation status')
            ->assertSee('Rules executed')
            ->assertSee('Automatic repair recommendations')
            ->assertSee('Repair status')
            ->assertSee('Repair providers')
            ->assertSee('Registry statistics')
            ->assertSee('Module diagnostic issues');
    }

    public function test_runtime_diagnostics_receives_summary_data(): void
    {
        $this->get(
            route('control-centre.runtime.diagnostics'),
        )
            ->assertOk()
            ->assertViewHas(
                'summary',
                static fn (mixed $summary): bool => is_array($summary)
                    && array_key_exists('status', $summary)
                    && array_key_exists('score', $summary)
                    && array_key_exists('modules', $summary)
                    && array_key_exists(
                        'healthyModules',
                        $summary,
                    )
                    && array_key_exists('issues', $summary)
                    && array_key_exists(
                        'bootStages',
                        $summary,
                    ),
            );
    }

    public function test_runtime_diagnostics_receives_validation_data(): void
    {
        $this->get(
            route('control-centre.runtime.diagnostics'),
        )
            ->assertOk()
            ->assertViewHas(
                'validationResult',
                static fn (mixed $result): bool => $result instanceof ValidationResult,
            )
            ->assertViewHas(
                'validationSummary',
                static fn (mixed $summary): bool => is_array($summary)
                    && array_key_exists('status', $summary)
                    && array_key_exists('rules', $summary)
                    && array_key_exists('issues', $summary)
                    && array_key_exists('errors', $summary)
                    && array_key_exists('warnings', $summary)
                    && array_key_exists(
                        'information',
                        $summary,
                    )
                    && $summary['rules'] === 4,
            )
            ->assertViewHas(
                'validationIssues',
                static fn (mixed $issues): bool => is_array($issues),
            );
    }

    public function test_runtime_diagnostics_receives_repair_data(): void
    {
        $this->get(
            route('control-centre.runtime.diagnostics'),
        )
            ->assertOk()
            ->assertViewHas(
                'repairResult',
                static fn (mixed $result): bool => $result instanceof RepairResult,
            )
            ->assertViewHas(
                'repairSummary',
                static fn (mixed $summary): bool => is_array($summary)
                    && array_key_exists('status', $summary)
                    && array_key_exists(
                        'recommendations',
                        $summary,
                    )
                    && array_key_exists('providers', $summary)
                    && in_array(
                        $summary['status'],
                        [
                            'clear',
                            'recommended',
                        ],
                        true,
                    )
                    && $summary['providers'] === 3,
            )
            ->assertViewHas(
                'repairRecommendations',
                static fn (mixed $recommendations): bool => is_array($recommendations),
            );
    }

    public function test_runtime_diagnostics_lists_registry_counts(): void
    {
        $this->get(
            route('control-centre.runtime.diagnostics'),
        )
            ->assertOk()
            ->assertViewHas(
                'registryCounts',
                static function (
                    mixed $registries,
                ): bool {
                    if (
                        ! is_array($registries)
                        || count($registries) !== 10
                    ) {
                        return false;
                    }

                    foreach ($registries as $registry) {
                        if (
                            ! array_key_exists('label', $registry)
                            || ! array_key_exists(
                                'value',
                                $registry,
                            )
                        ) {
                            return false;
                        }
                    }

                    return true;
                },
            );
    }

    public function test_control_centre_links_to_runtime_diagnostics(): void
    {
        $this->get('/control-centre')
            ->assertOk()
            ->assertSee(
                route(
                    'control-centre.runtime.diagnostics',
                ),
                false,
            )
            ->assertSee('Diagnostics');
    }
}
