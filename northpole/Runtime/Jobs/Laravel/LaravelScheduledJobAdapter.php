<?php

declare(strict_types=1);

namespace Northpole\Runtime\Jobs\Laravel;

use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistry;
use Northpole\Runtime\Jobs\ScheduledJobDefinition;

final class LaravelScheduledJobAdapter
{
    public function __construct(
        private readonly Application $application,
        private readonly Schedule $schedule,
    ) {}

    public function register(
        ModuleScheduledJobRegistry $registry,
    ): int {
        $registered = 0;

        foreach ($registry->all() as $definition) {
            $this->registerDefinition(
                $definition,
            );

            $registered++;
        }

        return $registered;
    }

    public function registerDefinition(
        ScheduledJobDefinition $definition,
    ): CallbackEvent {
        $job = $this->application->make(
            $definition->class,
        );

        $event = $this->schedule->job(
            $job,
            $definition->queue,
        );

        $this->applyFrequency(
            $event,
            $definition,
        );

        if ($definition->withoutOverlapping) {
            $event->withoutOverlapping();
        }

        $event->name(
            $this->eventName(
                $definition,
            ),
        );

        return $event;
    }

    private function applyFrequency(
        CallbackEvent $event,
        ScheduledJobDefinition $definition,
    ): void {
        match ($definition->frequency) {
            'every-minute' => $event->everyMinute(),
            'every-five-minutes' => $event->everyFiveMinutes(),
            'every-ten-minutes' => $event->everyTenMinutes(),
            'every-fifteen-minutes' => $event->everyFifteenMinutes(),
            'every-thirty-minutes' => $event->everyThirtyMinutes(),
            'hourly' => $this->applyHourlyFrequency(
                $event,
                $definition->at,
            ),
            'daily' => $this->applyTimedFrequency(
                $event,
                'daily',
                $definition->at,
            ),
            'weekly' => $this->applyTimedFrequency(
                $event,
                'weekly',
                $definition->at,
            ),
            'monthly' => $this->applyTimedFrequency(
                $event,
                'monthly',
                $definition->at,
            ),
            default => throw new InvalidArgumentException(
                sprintf(
                    'Scheduled job frequency [%s] is not supported by the Laravel scheduler adapter.',
                    $definition->frequency,
                ),
            ),
        };

        if (
            $definition->at !== null
            && in_array(
                $definition->frequency,
                [
                    'every-minute',
                    'every-five-minutes',
                    'every-ten-minutes',
                    'every-fifteen-minutes',
                    'every-thirty-minutes',
                ],
                true,
            )
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Scheduled job frequency [%s] cannot define an explicit time.',
                    $definition->frequency,
                ),
            );
        }
    }

    private function applyHourlyFrequency(
        CallbackEvent $event,
        ?string $at,
    ): void {
        if ($at === null) {
            $event->hourly();

            return;
        }

        [, $minute] = explode(
            ':',
            $at,
            2,
        );

        $event->hourlyAt(
            (int) $minute,
        );
    }

    private function applyTimedFrequency(
        CallbackEvent $event,
        string $frequency,
        ?string $at,
    ): void {
        match ($frequency) {
            'daily' => $event->daily(),
            'weekly' => $event->weekly(),
            'monthly' => $event->monthly(),
            default => throw new InvalidArgumentException(
                sprintf(
                    'Timed scheduled job frequency [%s] is not supported.',
                    $frequency,
                ),
            ),
        };

        if ($at !== null) {
            $event->at(
                $at,
            );
        }
    }

    private function eventName(
        ScheduledJobDefinition $definition,
    ): string {
        return sprintf(
            'northpole:%s:%s',
            $definition->module,
            hash(
                'sha256',
                $definition->key(),
            ),
        );
    }
}