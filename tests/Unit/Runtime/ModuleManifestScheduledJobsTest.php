<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Manifest\ModuleManifest;
use PHPUnit\Framework\TestCase;

final class ModuleManifestScheduledJobsTest extends TestCase
{
    public function test_it_returns_scheduled_jobs(): void
    {
        $manifest = $this->manifest([
            'jobs' => [
                'scheduled' => [
                    [
                        'class' => 'Modules\\Reports\\Jobs\\RefreshReports',
                        'frequency' => 'daily',
                        'at' => '02:00',
                        'queue' => 'reports',
                        'without_overlapping' => true,
                        'run_in_background' => false,
                    ],
                    [
                        'class' => 'Modules\\Reports\\Jobs\\CleanupReports',
                        'frequency' => 'hourly',
                    ],
                ],
            ],
        ]);

        self::assertSame(
            [
                [
                    'class' => 'Modules\\Reports\\Jobs\\RefreshReports',
                    'frequency' => 'daily',
                    'at' => '02:00',
                    'queue' => 'reports',
                    'without_overlapping' => true,
                    'run_in_background' => false,
                ],
                [
                    'class' => 'Modules\\Reports\\Jobs\\CleanupReports',
                    'frequency' => 'hourly',
                ],
            ],
            $manifest->scheduledJobs()
        );
    }

    public function test_it_trims_and_normalises_scheduled_jobs(): void
    {
        $manifest = $this->manifest([
            'jobs' => [
                'scheduled' => [
                    [
                        'class' => ' Modules\\Reports\\Jobs\\RefreshReports ',
                        'frequency' => ' DAILY ',
                        'at' => ' 02:00 ',
                        'queue' => ' reports ',
                    ],
                ],
            ],
        ]);

        self::assertSame(
            [
                [
                    'class' => 'Modules\\Reports\\Jobs\\RefreshReports',
                    'frequency' => 'daily',
                    'at' => '02:00',
                    'queue' => 'reports',
                ],
            ],
            $manifest->scheduledJobs()
        );
    }

    public function test_it_returns_empty_scheduled_jobs_when_not_defined(): void
    {
        $manifest = $this->manifest();

        self::assertSame(
            [],
            $manifest->scheduledJobs()
        );
    }

    public function test_it_returns_empty_scheduled_jobs_when_jobs_are_empty(): void
    {
        $manifest = $this->manifest([
            'jobs' => [],
        ]);

        self::assertSame(
            [],
            $manifest->scheduledJobs()
        );
    }

    public function test_it_rejects_a_non_object_jobs_field(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest field [jobs] must be an object'
        );

        $this->manifest([
            'jobs' => 'daily',
        ]);
    }

    public function test_it_rejects_a_non_list_scheduled_jobs_field(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest field [jobs.scheduled] must be a list'
        );

        $this->manifest([
            'jobs' => [
                'scheduled' => [
                    'daily' => [
                        'class' => 'Modules\\Reports\\Jobs\\RefreshReports',
                        'frequency' => 'daily',
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_a_non_object_scheduled_job(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest scheduled job [0] must be an object'
        );

        $this->manifest([
            'jobs' => [
                'scheduled' => [
                    'Modules\\Reports\\Jobs\\RefreshReports',
                ],
            ],
        ]);
    }

    public function test_it_rejects_a_missing_scheduled_job_class(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest scheduled job [0] must define a non-empty class'
        );

        $this->manifest([
            'jobs' => [
                'scheduled' => [
                    [
                        'frequency' => 'daily',
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_an_empty_scheduled_job_class(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest scheduled job [0] must define a non-empty class'
        );

        $this->manifest([
            'jobs' => [
                'scheduled' => [
                    [
                        'class' => '   ',
                        'frequency' => 'daily',
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_a_missing_scheduled_job_frequency(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest scheduled job [0] must define a non-empty frequency'
        );

        $this->manifest([
            'jobs' => [
                'scheduled' => [
                    [
                        'class' => 'Modules\\Reports\\Jobs\\RefreshReports',
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_an_unsupported_frequency(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest scheduled job [0] has unsupported frequency [sometimes]'
        );

        $this->manifest([
            'jobs' => [
                'scheduled' => [
                    [
                        'class' => 'Modules\\Reports\\Jobs\\RefreshReports',
                        'frequency' => 'sometimes',
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_an_invalid_scheduled_job_time(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest scheduled job [0] field [at] must use 24-hour HH:MM format'
        );

        $this->manifest([
            'jobs' => [
                'scheduled' => [
                    [
                        'class' => 'Modules\\Reports\\Jobs\\RefreshReports',
                        'frequency' => 'daily',
                        'at' => '25:90',
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_an_empty_queue_name(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest scheduled job [0] field [queue] must be a non-empty string'
        );

        $this->manifest([
            'jobs' => [
                'scheduled' => [
                    [
                        'class' => 'Modules\\Reports\\Jobs\\RefreshReports',
                        'frequency' => 'daily',
                        'queue' => '   ',
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_a_non_boolean_without_overlapping_option(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest scheduled job [0] field [without_overlapping] must be a boolean'
        );

        $this->manifest([
            'jobs' => [
                'scheduled' => [
                    [
                        'class' => 'Modules\\Reports\\Jobs\\RefreshReports',
                        'frequency' => 'daily',
                        'without_overlapping' => 'yes',
                    ],
                ],
            ],
        ]);
    }

    public function test_it_rejects_a_non_boolean_run_in_background_option(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module manifest scheduled job [0] field [run_in_background] must be a boolean'
        );

        $this->manifest([
            'jobs' => [
                'scheduled' => [
                    [
                        'class' => 'Modules\\Reports\\Jobs\\RefreshReports',
                        'frequency' => 'daily',
                        'run_in_background' => 1,
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function manifest(
        array $overrides = []
    ): ModuleManifest {
        return new ModuleManifest(
            data: array_replace(
                [
                    'name' => 'Reports',
                    'slug' => 'reports',
                    'version' => '1.0.0',
                    'enabled' => true,
                ],
                $overrides
            ),
            path: '/modules/reports',
            manifestPath: '/modules/reports/module.json',
        );
    }
}