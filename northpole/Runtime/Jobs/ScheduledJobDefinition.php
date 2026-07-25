<?php

declare(strict_types=1);

namespace Northpole\Runtime\Jobs;

use InvalidArgumentException;

final readonly class ScheduledJobDefinition
{
    /**
     * @var array<int, string>
     */
    private const SUPPORTED_FREQUENCIES = [
        'every-minute',
        'every-five-minutes',
        'every-ten-minutes',
        'every-fifteen-minutes',
        'every-thirty-minutes',
        'hourly',
        'daily',
        'weekly',
        'monthly',
    ];

    public string $module;

    public string $class;

    public string $frequency;

    public ?string $at;

    public ?string $queue;

    public bool $withoutOverlapping;

    public bool $runInBackground;

    public function __construct(
        string $module,
        string $class,
        string $frequency,
        ?string $at = null,
        ?string $queue = null,
        bool $withoutOverlapping = false,
        bool $runInBackground = false,
    ) {
        $module = trim($module);
        $class = trim($class);
        $frequency = strtolower(
            trim($frequency)
        );

        $at = $at !== null
            ? trim($at)
            : null;

        $queue = $queue !== null
            ? trim($queue)
            : null;

        if ($module === '') {
            throw new InvalidArgumentException(
                'A scheduled job module owner cannot be empty.'
            );
        }

        if ($class === '') {
            throw new InvalidArgumentException(
                'A scheduled job class cannot be empty.'
            );
        }

        if ($frequency === '') {
            throw new InvalidArgumentException(
                'A scheduled job frequency cannot be empty.'
            );
        }

        if (
            ! in_array(
                $frequency,
                self::SUPPORTED_FREQUENCIES,
                true
            )
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Scheduled job frequency [%s] is not supported.',
                    $frequency
                )
            );
        }

        if (
            $at !== null
            && preg_match(
                '/^(?:[01]\d|2[0-3]):[0-5]\d$/',
                $at
            ) !== 1
        ) {
            throw new InvalidArgumentException(
                'A scheduled job time must use 24-hour HH:MM format.'
            );
        }

        if (
            $queue !== null
            && $queue === ''
        ) {
            throw new InvalidArgumentException(
                'A scheduled job queue cannot be empty.'
            );
        }

        $this->module = $module;
        $this->class = $class;
        $this->frequency = $frequency;
        $this->at = $at;
        $this->queue = $queue;
        $this->withoutOverlapping = $withoutOverlapping;
        $this->runInBackground = $runInBackground;
    }

    public function key(): string
    {
        return strtolower(
            implode(
                '|',
                [
                    $this->module,
                    $this->class,
                    $this->frequency,
                    $this->at ?? '',
                    $this->queue ?? '',
                    $this->withoutOverlapping ? '1' : '0',
                    $this->runInBackground ? '1' : '0',
                ]
            )
        );
    }

    /**
     * @return array{
     *     module: string,
     *     class: string,
     *     frequency: string,
     *     at: string|null,
     *     queue: string|null,
     *     without_overlapping: bool,
     *     run_in_background: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'module' => $this->module,
            'class' => $this->class,
            'frequency' => $this->frequency,
            'at' => $this->at,
            'queue' => $this->queue,
            'without_overlapping' => $this->withoutOverlapping,
            'run_in_background' => $this->runInBackground,
        ];
    }
}