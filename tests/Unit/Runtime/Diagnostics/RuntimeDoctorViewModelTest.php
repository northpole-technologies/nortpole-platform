<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Diagnostics;

use Northpole\Runtime\Diagnostics\RuntimeDoctorViewModel;
use Tests\TestCase;

final class RuntimeDoctorViewModelTest extends TestCase
{
    public function test_it_builds_doctor_command_data(): void
    {
        $data = app(
            RuntimeDoctorViewModel::class,
        )->data();

        self::assertSame(
            [
                'healthStatus',
                'overallStatus',
                'platformRows',
                'moduleSummaryRows',
                'registryRows',
                'moduleRows',
            ],
            array_keys($data),
        );

        self::assertIsString(
            $data['healthStatus'],
        );

        self::assertContains(
            $data['overallStatus'],
            [
                'HEALTHY',
                'DEGRADED',
                'UNHEALTHY',
            ],
        );

        self::assertSame(
            [
                'Environment',
                'Laravel',
                'PHP',
                'Peak memory',
            ],
            array_column(
                $data['platformRows'],
                0,
            ),
        );

        self::assertSame(
            [
                'Discovered',
                'Enabled',
                'Disabled',
            ],
            array_column(
                $data['moduleSummaryRows'],
                0,
            ),
        );

        self::assertSame(
            $data['moduleSummaryRows'][0][1],
            $data['moduleSummaryRows'][1][1]
                + $data['moduleSummaryRows'][2][1],
        );

        self::assertIsArray(
            $data['registryRows'],
        );

        self::assertIsArray(
            $data['moduleRows'],
        );

        foreach ($data['platformRows'] as $row) {
            self::assertCount(2, $row);
            self::assertIsString($row[0]);
            self::assertIsString($row[1]);
        }

        foreach ($data['moduleSummaryRows'] as $row) {
            self::assertCount(2, $row);
            self::assertIsString($row[0]);
            self::assertIsInt($row[1]);
        }

        foreach ($data['registryRows'] as $row) {
            self::assertCount(2, $row);
            self::assertIsString($row[0]);
            self::assertIsInt($row[1]);
        }

        foreach ($data['moduleRows'] as $row) {
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