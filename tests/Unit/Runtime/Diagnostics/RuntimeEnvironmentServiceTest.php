<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Diagnostics;

use Northpole\Runtime\Diagnostics\RuntimeEnvironmentService;
use Tests\TestCase;

final class RuntimeEnvironmentServiceTest extends TestCase
{
    public function test_it_returns_runtime_environment_metadata(): void
    {
        $metadata = app(
            RuntimeEnvironmentService::class,
        )->metadata();

        self::assertSame(
            [
                'environment',
                'laravelVersion',
                'phpVersion',
                'peakMemoryBytes',
                'peakMemory',
            ],
            array_keys($metadata),
        );

        self::assertSame(
            (string) app()->environment(),
            $metadata['environment'],
        );

        self::assertSame(
            app()->version(),
            $metadata['laravelVersion'],
        );

        self::assertSame(
            PHP_VERSION,
            $metadata['phpVersion'],
        );

        self::assertIsInt(
            $metadata['peakMemoryBytes'],
        );

        self::assertGreaterThan(
            0,
            $metadata['peakMemoryBytes'],
        );

        self::assertMatchesRegularExpression(
            '/^\d+(?:\.\d{2})? (?:B|KB|MB|GB)$/',
            $metadata['peakMemory'],
        );
    }

    public function test_it_returns_dashboard_environment_data(): void
    {
        $data = app(
            RuntimeEnvironmentService::class,
        )->dashboardData();

        self::assertSame(
            [
                'environment',
                'laravelVersion',
                'phpVersion',
            ],
            array_keys($data),
        );

        self::assertSame(
            (string) app()->environment(),
            $data['environment'],
        );

        self::assertSame(
            app()->version(),
            $data['laravelVersion'],
        );

        self::assertSame(
            PHP_VERSION,
            $data['phpVersion'],
        );
    }

    public function test_it_returns_console_environment_rows(): void
    {
        $rows = app(
            RuntimeEnvironmentService::class,
        )->consoleRows();

        self::assertCount(4, $rows);

        self::assertSame(
            [
                'Environment',
                'Laravel',
                'PHP',
                'Peak memory',
            ],
            array_column(
                $rows,
                0,
            ),
        );

        foreach ($rows as $row) {
            self::assertCount(2, $row);
            self::assertIsString($row[0]);
            self::assertIsString($row[1]);
        }
    }
}