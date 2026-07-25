<?php

declare(strict_types=1);

namespace Northpole\Runtime\Jobs;

use InvalidArgumentException;

final class ModuleScheduledJobRegistry
{
    /**
     * @var array<string, ScheduledJobDefinition>
     */
    private array $jobs = [];

    public function register(
        ScheduledJobDefinition $job
    ): self {
        $key = $job->key();

        if (isset($this->jobs[$key])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Scheduled job [%s] from module [%s] is already registered for frequency [%s].',
                    $job->class,
                    $job->module,
                    $job->frequency
                )
            );
        }

        $this->jobs[$key] = $job;

        return $this;
    }

    /**
     * @param  iterable<int, ScheduledJobDefinition>  $jobs
     */
    public function registerMany(
        iterable $jobs
    ): self {
        foreach ($jobs as $job) {
            $this->register($job);
        }

        return $this;
    }

    /**
     * @return array<int, ScheduledJobDefinition>
     */
    public function all(): array
    {
        $jobs = array_values(
            $this->jobs
        );

        usort(
            $jobs,
            static function (
                ScheduledJobDefinition $first,
                ScheduledJobDefinition $second
            ): int {
                return [
                    $first->module,
                    $first->class,
                    $first->frequency,
                    $first->at ?? '',
                ] <=> [
                    $second->module,
                    $second->class,
                    $second->frequency,
                    $second->at ?? '',
                ];
            }
        );

        return $jobs;
    }

    /**
     * @return array<int, ScheduledJobDefinition>
     */
    public function forModule(
        string $module
    ): array {
        $module = trim($module);

        if ($module === '') {
            return [];
        }

        return array_values(
            array_filter(
                $this->all(),
                static fn (
                    ScheduledJobDefinition $job
                ): bool => $job->module === $module
            )
        );
    }

    public function count(
        ?string $module = null
    ): int {
        if ($module !== null) {
            return count(
                $this->forModule($module)
            );
        }

        return count($this->jobs);
    }

    public function isEmpty(): bool
    {
        return $this->jobs === [];
    }

    public function clear(): self
    {
        $this->jobs = [];

        return $this;
    }
}