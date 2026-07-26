<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Diagnostics;

use Northpole\Runtime\Diagnostics\RuntimeDashboardViewModel;
use Tests\TestCase;

final class RuntimeDashboardViewModelTest extends TestCase
{
    public function test_it_builds_runtime_dashboard_data(): void
    {
        $data = app(
            RuntimeDashboardViewModel::class,
        )->data();

        self::assertSame(
            [
                'health',
                'runtimeHealth',
                'runtimeHealthSummary',
                'environment',
                'laravelVersion',
                'phpVersion',
                'moduleSummary',
                'metrics',
                'modules',
            ],
            array_keys($data),
        );

        self::assertIsString(
            $data['health'],
        );

        self::assertSame(
            strtoupper(
                $data['runtimeHealth']->status(),
            ),
            $data['health'],
        );

        self::assertSame(
            [
                'score',
                'healthyModules',
                'issues',
            ],
            array_keys(
                $data['runtimeHealthSummary'],
            ),
        );

        self::assertSame(
            $data['runtimeHealth']->score(),
            $data['runtimeHealthSummary']['score'],
        );

        self::assertIsInt(
            $data['runtimeHealthSummary']
                ['healthyModules'],
        );

        self::assertIsInt(
            $data['runtimeHealthSummary']
                ['issues'],
        );

        self::assertSame(
            [
                'discovered',
                'enabled',
                'disabled',
            ],
            array_keys(
                $data['moduleSummary'],
            ),
        );

        self::assertSame(
            $data['moduleSummary']['discovered'],
            count($data['modules']),
        );

        self::assertSame(
            $data['moduleSummary']['discovered'],
            $data['moduleSummary']['enabled']
                + $data['moduleSummary']['disabled'],
        );

        self::assertIsArray(
            $data['metrics'],
        );

        foreach ($data['modules'] as $module) {
            self::assertArrayHasKey(
                'healthStatus',
                $module,
            );

            self::assertArrayHasKey(
                'healthScore',
                $module,
            );

            self::assertArrayHasKey(
                'healthChecks',
                $module,
            );

            self::assertIsString(
                $module['healthStatus'],
            );

            self::assertIsInt(
                $module['healthScore'],
            );

            self::assertIsArray(
                $module['healthChecks'],
            );
        }
    }
}