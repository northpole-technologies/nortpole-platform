<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Health;

use Northpole\Runtime\Health\RuntimeHealthSummaryService;
use Tests\TestCase;

final class RuntimeHealthSummaryServiceTest extends TestCase
{
    public function test_it_builds_a_shared_runtime_health_summary(): void
    {
        $summary = app(
            RuntimeHealthSummaryService::class,
        )->inspect();

        self::assertSame(
            [
                'runtimeHealth',
                'moduleHealthBySlug',
                'healthyModules',
                'issues',
                'issueCount',
            ],
            array_keys($summary),
        );

        self::assertIsArray(
            $summary['moduleHealthBySlug'],
        );

        self::assertIsInt(
            $summary['healthyModules'],
        );

        self::assertIsArray(
            $summary['issues'],
        );

        self::assertSame(
            count($summary['issues']),
            $summary['issueCount'],
        );

        self::assertLessThanOrEqual(
            count(
                $summary['runtimeHealth']->modules(),
            ),
            $summary['healthyModules'],
        );

        foreach (
            $summary['runtimeHealth']->modules()
            as $moduleHealth
        ) {
            self::assertArrayHasKey(
                $moduleHealth->slug(),
                $summary['moduleHealthBySlug'],
            );

            self::assertSame(
                $moduleHealth,
                $summary['moduleHealthBySlug']
                    [$moduleHealth->slug()],
            );
        }

        foreach ($summary['issues'] as $issue) {
            self::assertSame(
                [
                    'module',
                    'check',
                    'message',
                ],
                array_keys($issue),
            );
        }
    }
}