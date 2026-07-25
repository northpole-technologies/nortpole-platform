<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Jobs;

use InvalidArgumentException;
use Northpole\Runtime\Jobs\ScheduledJobDefinition;
use PHPUnit\Framework\TestCase;

final class ScheduledJobDefinitionTest extends TestCase
{
    public function test_it_preserves_a_scheduled_job_definition(): void
    {
        $job = new ScheduledJobDefinition(
            module: 'reports',
            class: 'Modules\\Reports\\Jobs\\RefreshReports',
            frequency: 'daily',
            at: '02:00',
            queue: 'reports',
            withoutOverlapping: true,
            runInBackground: true,
        );

        self::assertSame(
            [
                'module' => 'reports',
                'class' => 'Modules\\Reports\\Jobs\\RefreshReports',
                'frequency' => 'daily',
                'at' => '02:00',
                'queue' => 'reports',
                'without_overlapping' => true,
                'run_in_background' => true,
            ],
            $job->toArray()
        );
    }

    public function test_it_normalises_string_values(): void
    {
        $job = new ScheduledJobDefinition(
            module: ' reports ',
            class: ' Modules\\Reports\\Jobs\\RefreshReports ',
            frequency: ' DAILY ',
            at: ' 02:00 ',
            queue: ' reports ',
        );

        self::assertSame(
            'reports',
            $job->module
        );

        self::assertSame(
            'Modules\\Reports\\Jobs\\RefreshReports',
            $job->class
        );

        self::assertSame(
            'daily',
            $job->frequency
        );

        self::assertSame(
            '02:00',
            $job->at
        );

        self::assertSame(
            'reports',
            $job->queue
        );
    }

    public function test_it_creates_the_same_key_for_equivalent_definitions(): void
    {
        $first = new ScheduledJobDefinition(
            module: 'reports',
            class: 'Modules\\Reports\\Jobs\\RefreshReports',
            frequency: 'daily',
            at: '02:00',
        );

        $second = new ScheduledJobDefinition(
            module: ' REPORTS ',
            class: ' Modules\\Reports\\Jobs\\RefreshReports ',
            frequency: ' DAILY ',
            at: ' 02:00 ',
        );

        self::assertSame(
            $first->key(),
            $second->key()
        );
    }

    public function test_different_times_create_different_keys(): void
    {
        $first = new ScheduledJobDefinition(
            module: 'reports',
            class: 'Modules\\Reports\\Jobs\\RefreshReports',
            frequency: 'daily',
            at: '02:00',
        );

        $second = new ScheduledJobDefinition(
            module: 'reports',
            class: 'Modules\\Reports\\Jobs\\RefreshReports',
            frequency: 'daily',
            at: '03:00',
        );

        self::assertNotSame(
            $first->key(),
            $second->key()
        );
    }

    public function test_it_rejects_an_empty_module_owner(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'A scheduled job module owner cannot be empty.'
        );

        new ScheduledJobDefinition(
            module: '   ',
            class: 'Modules\\Reports\\Jobs\\RefreshReports',
            frequency: 'daily',
        );
    }

    public function test_it_rejects_an_empty_job_class(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'A scheduled job class cannot be empty.'
        );

        new ScheduledJobDefinition(
            module: 'reports',
            class: '   ',
            frequency: 'daily',
        );
    }

    public function test_it_rejects_an_empty_frequency(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'A scheduled job frequency cannot be empty.'
        );

        new ScheduledJobDefinition(
            module: 'reports',
            class: 'Modules\\Reports\\Jobs\\RefreshReports',
            frequency: '   ',
        );
    }

    public function test_it_rejects_an_unsupported_frequency(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Scheduled job frequency [occasionally] is not supported.'
        );

        new ScheduledJobDefinition(
            module: 'reports',
            class: 'Modules\\Reports\\Jobs\\RefreshReports',
            frequency: 'occasionally',
        );
    }

    public function test_it_rejects_an_invalid_time(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'A scheduled job time must use 24-hour HH:MM format.'
        );

        new ScheduledJobDefinition(
            module: 'reports',
            class: 'Modules\\Reports\\Jobs\\RefreshReports',
            frequency: 'daily',
            at: '26:70',
        );
    }

    public function test_it_rejects_an_empty_queue(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'A scheduled job queue cannot be empty.'
        );

        new ScheduledJobDefinition(
            module: 'reports',
            class: 'Modules\\Reports\\Jobs\\RefreshReports',
            frequency: 'daily',
            queue: '   ',
        );
    }
}