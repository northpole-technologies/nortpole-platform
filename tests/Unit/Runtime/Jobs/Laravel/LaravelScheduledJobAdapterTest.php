<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Jobs\Laravel;

use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;
use Northpole\Runtime\Jobs\Laravel\LaravelScheduledJobAdapter;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistry;
use Northpole\Runtime\Jobs\ScheduledJobDefinition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LaravelScheduledJobAdapterTest extends TestCase
{
    public function test_it_registers_a_daily_queued_job(): void
    {
        $job = new DailyReportsJob;

        $application = $this->createMock(
            Application::class,
        );

        $application
            ->expects(self::once())
            ->method('make')
            ->with(DailyReportsJob::class)
            ->willReturn($job);

        $event = $this->createMock(
            CallbackEvent::class,
        );

        $event
            ->expects(self::once())
            ->method('daily')
            ->willReturnSelf();

        $event
            ->expects(self::once())
            ->method('at')
            ->with('02:30')
            ->willReturnSelf();

        $event
            ->expects(self::once())
            ->method('withoutOverlapping')
            ->willReturnSelf();

        $event
            ->expects(self::once())
            ->method('name')
            ->with(
                self::callback(
                    static fn (string $name): bool =>
                        str_starts_with(
                            $name,
                            'northpole:reports:',
                        ),
                ),
            )
            ->willReturnSelf();

        $schedule = $this->createMock(
            Schedule::class,
        );

        $schedule
            ->expects(self::once())
            ->method('job')
            ->with(
                $job,
                'reports',
            )
            ->willReturn($event);

        $adapter = new LaravelScheduledJobAdapter(
            $application,
            $schedule,
        );

        $registeredEvent = $adapter->registerDefinition(
            new ScheduledJobDefinition(
                module: 'reports',
                class: DailyReportsJob::class,
                frequency: 'daily',
                at: '02:30',
                queue: 'reports',
                withoutOverlapping: true,
                runInBackground: true,
            ),
        );

        self::assertSame(
            $event,
            $registeredEvent,
        );
    }

    public function test_it_registers_an_hourly_job_at_the_requested_minute(): void
    {
        $job = new HourlyReportsJob;

        $application = $this->applicationMock(
            HourlyReportsJob::class,
            $job,
        );

        $event = $this->createMock(
            CallbackEvent::class,
        );

        $event
            ->expects(self::once())
            ->method('hourlyAt')
            ->with(45)
            ->willReturnSelf();

        $event
            ->expects(self::once())
            ->method('name')
            ->willReturnSelf();

        $schedule = $this->scheduleMock(
            $job,
            null,
            $event,
        );

        $adapter = new LaravelScheduledJobAdapter(
            $application,
            $schedule,
        );

        $adapter->registerDefinition(
            new ScheduledJobDefinition(
                module: 'reports',
                class: HourlyReportsJob::class,
                frequency: 'hourly',
                at: '12:45',
            ),
        );
    }

    public function test_it_registers_all_jobs_from_the_registry(): void
    {
        $firstJob = new DailyReportsJob;
        $secondJob = new HourlyReportsJob;

        $application = $this->createMock(
            Application::class,
        );

        $application
            ->expects(self::exactly(2))
            ->method('make')
            ->willReturnMap([
                [
                    DailyReportsJob::class,
                    [],
                    $firstJob,
                ],
                [
                    HourlyReportsJob::class,
                    [],
                    $secondJob,
                ],
            ]);

        $firstEvent = $this->createEventMock();
        $secondEvent = $this->createEventMock();

        $firstEvent
            ->expects(self::once())
            ->method('daily')
            ->willReturnSelf();

        $secondEvent
            ->expects(self::once())
            ->method('hourly')
            ->willReturnSelf();

        $schedule = $this->createMock(
            Schedule::class,
        );

        $schedule
            ->expects(self::exactly(2))
            ->method('job')
            ->willReturnMap([
                [
                    $firstJob,
                    null,
                    null,
                    $firstEvent,
                ],
                [
                    $secondJob,
                    null,
                    null,
                    $secondEvent,
                ],
            ]);

        $registry = new ModuleScheduledJobRegistry;

        $registry->registerMany([
            new ScheduledJobDefinition(
                module: 'reports',
                class: DailyReportsJob::class,
                frequency: 'daily',
            ),
            new ScheduledJobDefinition(
                module: 'reports',
                class: HourlyReportsJob::class,
                frequency: 'hourly',
            ),
        ]);

        $adapter = new LaravelScheduledJobAdapter(
            $application,
            $schedule,
        );

        self::assertSame(
            2,
            $adapter->register(
                $registry,
            ),
        );
    }

    public function test_it_rejects_explicit_times_for_sub_hourly_jobs(): void
    {
        $job = new FrequentReportsJob;

        $application = $this->applicationMock(
            FrequentReportsJob::class,
            $job,
        );

        $event = $this->createMock(
            CallbackEvent::class,
        );

        $event
            ->expects(self::once())
            ->method('everyFiveMinutes')
            ->willReturnSelf();

        $schedule = $this->scheduleMock(
            $job,
            null,
            $event,
        );

        $adapter = new LaravelScheduledJobAdapter(
            $application,
            $schedule,
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Scheduled job frequency [every-five-minutes] cannot define an explicit time.',
        );

        $adapter->registerDefinition(
            new ScheduledJobDefinition(
                module: 'reports',
                class: FrequentReportsJob::class,
                frequency: 'every-five-minutes',
                at: '02:00',
            ),
        );
    }

    private function applicationMock(
        string $class,
        object $job,
    ): Application&MockObject {
        $application = $this->createMock(
            Application::class,
        );

        $application
            ->expects(self::once())
            ->method('make')
            ->with($class)
            ->willReturn($job);

        return $application;
    }

    private function scheduleMock(
        object $job,
        ?string $queue,
        CallbackEvent $event,
    ): Schedule&MockObject {
        $schedule = $this->createMock(
            Schedule::class,
        );

        $schedule
            ->expects(self::once())
            ->method('job')
            ->with(
                $job,
                $queue,
            )
            ->willReturn($event);

        return $schedule;
    }

    private function createEventMock(): CallbackEvent&MockObject
    {
        $event = $this->createMock(
            CallbackEvent::class,
        );

        $event
            ->expects(self::once())
            ->method('name')
            ->willReturnSelf();

        return $event;
    }
}

final class DailyReportsJob {}

final class HourlyReportsJob {}

final class FrequentReportsJob {}