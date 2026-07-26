<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Diagnostics;

use Northpole\Runtime\Diagnostics\RuntimeRegistryStatisticsService;
use Tests\TestCase;

final class RuntimeRegistryStatisticsServiceTest extends TestCase
{
    public function test_it_returns_all_runtime_registry_counts(): void
    {
        $statistics = app(
            RuntimeRegistryStatisticsService::class,
        );

        $counts = $statistics->counts();

        self::assertSame(
            [
                'boot_stages',
                'capabilities',
                'commands',
                'queries',
                'event_listeners',
                'navigation_items',
                'permissions',
                'configuration',
                'notifications',
                'scheduled_jobs',
                'roles',
            ],
            array_keys($counts),
        );

        foreach ($counts as $count) {
            self::assertIsInt($count);
            self::assertGreaterThanOrEqual(0, $count);
        }

        self::assertSame(
            array_sum($counts),
            $statistics->total(),
        );
    }

    public function test_it_returns_runtime_metric_cards(): void
    {
        $statistics = app(
            RuntimeRegistryStatisticsService::class,
        );

        $metrics = $statistics->metricCards();

        self::assertCount(11, $metrics);

        self::assertSame(
            'Boot stages',
            $metrics[0]['label'],
        );

        self::assertSame(
            'Roles',
            $metrics[10]['label'],
        );

        self::assertCount(
            10,
            $statistics->diagnosticCards(),
        );

        self::assertSame(
            array_map(
                static fn (array $metric): array => [
                    $metric['label'],
                    $metric['value'],
                ],
                $metrics,
            ),
            $statistics->consoleRows(),
        );
    }
}