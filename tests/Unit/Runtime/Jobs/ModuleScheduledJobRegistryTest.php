<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Jobs;

use InvalidArgumentException;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistry;
use Northpole\Runtime\Jobs\ScheduledJobDefinition;
use PHPUnit\Framework\TestCase;

final class ModuleScheduledJobRegistryTest extends TestCase
{
    public function test_it_registers_a_scheduled_job(): void
    {
        $registry = new ModuleScheduledJobRegistry;

        $job = $this->job();

        $registry->register($job);

        self::assertSame(
            [$job],
            $registry->all()
        );

        self::assertSame(
            1,
            $registry->count()
        );
    }

    public function test_it_registers_multiple_scheduled_jobs(): void
    {
        $registry = new ModuleScheduledJobRegistry;

        $first = $this->job(
            class: 'Modules\\Reports\\Jobs\\RefreshReports'
        );

        $second = $this->job(
            class: 'Modules\\Reports\\Jobs\\CleanupReports',
            frequency: 'hourly'
        );

        $registry->registerMany([
            $first,
            $second,
        ]);

        self::assertSame(
            2,
            $registry->count()
        );

        self::assertContains(
            $first,
            $registry->all()
        );

        self::assertContains(
            $second,
            $registry->all()
        );
    }

    public function test_it_allows_the_same_class_with_different_schedules(): void
    {
        $registry = new ModuleScheduledJobRegistry;

        $registry->register(
            $this->job(
                frequency: 'daily',
                at: '02:00'
            )
        );

        $registry->register(
            $this->job(
                frequency: 'daily',
                at: '14:00'
            )
        );

        self::assertSame(
            2,
            $registry->count()
        );
    }

    public function test_it_rejects_an_exact_duplicate_registration(): void
    {
        $registry = new ModuleScheduledJobRegistry;

        $registry->register(
            $this->job()
        );

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Scheduled job [Modules\\Reports\\Jobs\\RefreshReports] from module [reports] is already registered for frequency [daily].'
        );

        $registry->register(
            $this->job()
        );
    }

    public function test_it_filters_jobs_by_module(): void
    {
        $registry = new ModuleScheduledJobRegistry;

        $reportsJob = $this->job(
            module: 'reports'
        );

        $crmJob = $this->job(
            module: 'crm',
            class: 'Modules\\CRM\\Jobs\\RefreshCustomers'
        );

        $registry->registerMany([
            $reportsJob,
            $crmJob,
        ]);

        self::assertSame(
            [$reportsJob],
            $registry->forModule('reports')
        );

        self::assertSame(
            1,
            $registry->count('reports')
        );
    }

    public function test_an_empty_module_filter_returns_no_jobs(): void
    {
        $registry = new ModuleScheduledJobRegistry;

        $registry->register(
            $this->job()
        );

        self::assertSame(
            [],
            $registry->forModule('   ')
        );
    }

    public function test_it_orders_jobs_by_module_class_frequency_and_time(): void
    {
        $registry = new ModuleScheduledJobRegistry;

        $later = $this->job(
            class: 'Modules\\Reports\\Jobs\\RefreshReports',
            at: '14:00'
        );

        $crm = $this->job(
            module: 'crm',
            class: 'Modules\\CRM\\Jobs\\RefreshCustomers',
            at: '03:00'
        );

        $earlier = $this->job(
            class: 'Modules\\Reports\\Jobs\\RefreshReports',
            at: '02:00'
        );

        $registry->registerMany([
            $later,
            $crm,
            $earlier,
        ]);

        self::assertSame(
            [
                $crm,
                $earlier,
                $later,
            ],
            $registry->all()
        );
    }

    public function test_it_can_be_cleared(): void
    {
        $registry = new ModuleScheduledJobRegistry;

        $registry->register(
            $this->job()
        );

        self::assertFalse(
            $registry->isEmpty()
        );

        $registry->clear();

        self::assertTrue(
            $registry->isEmpty()
        );

        self::assertSame(
            0,
            $registry->count()
        );
    }

    private function job(
        string $module = 'reports',
        string $class = 'Modules\\Reports\\Jobs\\RefreshReports',
        string $frequency = 'daily',
        ?string $at = '02:00',
    ): ScheduledJobDefinition {
        return new ScheduledJobDefinition(
            module: $module,
            class: $class,
            frequency: $frequency,
            at: $at,
        );
    }
}