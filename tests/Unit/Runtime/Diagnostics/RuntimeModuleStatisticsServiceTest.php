<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Diagnostics;

use Northpole\Runtime\Diagnostics\RuntimeModuleStatisticsService;
use Tests\TestCase;

final class RuntimeModuleStatisticsServiceTest extends TestCase
{
    public function test_it_returns_module_summary_counts(): void
    {
        $service = app(
            RuntimeModuleStatisticsService::class,
        );

        $summary = $service->summary();

        self::assertSame(
            [
                'discovered',
                'enabled',
                'disabled',
            ],
            array_keys($summary),
        );

        self::assertSame(
            $summary['discovered'],
            $summary['enabled'] + $summary['disabled'],
        );

        self::assertSame(
            count($service->modules()),
            $summary['discovered'],
        );
    }

    public function test_it_returns_dashboard_module_rows(): void
    {
        $rows = app(
            RuntimeModuleStatisticsService::class,
        )->dashboardRows();

        foreach ($rows as $row) {
            self::assertSame(
                [
                    'name',
                    'slug',
                    'version',
                    'description',
                    'enabled',
                    'dependencies',
                    'commands',
                    'queries',
                    'permissions',
                ],
                array_keys($row),
            );

            self::assertIsString($row['name']);
            self::assertIsString($row['slug']);
            self::assertIsString($row['version']);
            self::assertIsString($row['description']);
            self::assertIsBool($row['enabled']);
            self::assertIsInt($row['dependencies']);
            self::assertIsInt($row['commands']);
            self::assertIsInt($row['queries']);
            self::assertIsInt($row['permissions']);
        }

        $names = array_column(
            $rows,
            'name',
        );

        $sortedNames = $names;

        sort($sortedNames);

        self::assertSame(
            $sortedNames,
            $names,
        );
    }

    public function test_it_returns_console_module_rows(): void
    {
        $rows = app(
            RuntimeModuleStatisticsService::class,
        )->consoleRows();

        foreach ($rows as $row) {
            self::assertCount(4, $row);
            self::assertIsString($row[0]);
            self::assertIsString($row[1]);
            self::assertIsString($row[2]);

            self::assertContains(
                $row[3],
                [
                    'Enabled',
                    'Disabled',
                ],
            );
        }
    }
}