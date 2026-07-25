<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Jobs;

use InvalidArgumentException;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistrar;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistry;
use Northpole\Runtime\Manifest\ModuleManifest;
use PHPUnit\Framework\TestCase;

final class ModuleScheduledJobRegistrarTest extends TestCase
{
    public function test_it_registers_manifest_scheduled_jobs(): void
    {
        $registry = new ModuleScheduledJobRegistry;

        $registrar = new ModuleScheduledJobRegistrar(
            $registry
        );

        $registrar->register(
            $this->manifest([
                [
                    'class' => 'Modules\\Reports\\Jobs\\RefreshReports',
                    'frequency' => 'daily',
                    'at' => '02:00',
                    'queue' => 'reports',
                    'without_overlapping' => true,
                    'run_in_background' => true,
                ],
                [
                    'class' => 'Modules\\Reports\\Jobs\\CleanupReports',
                    'frequency' => 'hourly',
                ],
            ])
        );

        self::assertSame(
            2,
            $registry->count()
        );

        self::assertSame(
            [
                [
                    'module' => 'reports',
                    'class' => 'Modules\\Reports\\Jobs\\CleanupReports',
                    'frequency' => 'hourly',
                    'at' => null,
                    'queue' => null,
                    'without_overlapping' => false,
                    'run_in_background' => false,
                ],
                [
                    'module' => 'reports',
                    'class' => 'Modules\\Reports\\Jobs\\RefreshReports',
                    'frequency' => 'daily',
                    'at' => '02:00',
                    'queue' => 'reports',
                    'without_overlapping' => true,
                    'run_in_background' => true,
                ],
            ],
            array_map(
                static fn ($job): array => $job->toArray(),
                $registry->all()
            )
        );
    }

    public function test_it_skips_a_module_without_scheduled_jobs(): void
    {
        $registry = new ModuleScheduledJobRegistry;

        $registrar = new ModuleScheduledJobRegistrar(
            $registry
        );

        $registrar->register(
            $this->manifest()
        );

        self::assertTrue(
            $registry->isEmpty()
        );
    }

    public function test_it_exposes_the_registry(): void
    {
        $registry = new ModuleScheduledJobRegistry;

        $registrar = new ModuleScheduledJobRegistrar(
            $registry
        );

        self::assertSame(
            $registry,
            $registrar->registry()
        );
    }

    public function test_it_rejects_duplicate_manifest_schedules(): void
    {
        $registry = new ModuleScheduledJobRegistry;

        $registrar = new ModuleScheduledJobRegistrar(
            $registry
        );

        $job = [
            'class' => 'Modules\\Reports\\Jobs\\RefreshReports',
            'frequency' => 'daily',
            'at' => '02:00',
        ];

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Scheduled job [Modules\\Reports\\Jobs\\RefreshReports] from module [reports] is already registered for frequency [daily].'
        );

        $registrar->register(
            $this->manifest([
                $job,
                $job,
            ])
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $jobs
     */
    private function manifest(
        array $jobs = []
    ): ModuleManifest {
        return new ModuleManifest(
            data: [
                'name' => 'Reports',
                'slug' => 'reports',
                'version' => '1.0.0',
                'enabled' => true,
                'jobs' => [
                    'scheduled' => $jobs,
                ],
            ],
            path: '/modules/reports',
            manifestPath: '/modules/reports/module.json',
        );
    }
}