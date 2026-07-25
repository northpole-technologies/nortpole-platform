<?php

declare(strict_types=1);

namespace Northpole\Runtime\Notifications;

use InvalidArgumentException;

final readonly class NotificationDefinition
{
    public string $module;

    public string $name;

    public string $class;

    /**
     * @var array<int, string>
     */
    public array $channels;

    public ?string $queue;

    /**
     * @param  array<int, mixed>  $channels
     */
    public function __construct(
        string $module,
        string $name,
        string $class,
        array $channels,
        ?string $queue = null,
    ) {
        $module = trim($module);
        $name = trim($name);
        $class = trim($class);
        $queue = $queue === null
            ? null
            : trim($queue);

        if ($module === '') {
            throw new InvalidArgumentException(
                'A notification module owner cannot be empty.',
            );
        }

        if ($name === '') {
            throw new InvalidArgumentException(
                'A notification name cannot be empty.',
            );
        }

        if ($class === '') {
            throw new InvalidArgumentException(
                'A notification class cannot be empty.',
            );
        }

        if ($queue === '') {
            throw new InvalidArgumentException(
                'A notification queue cannot be empty.',
            );
        }

        $normalisedChannels = [];

        foreach ($channels as $channel) {
            if (! is_string($channel)) {
                throw new InvalidArgumentException(
                    'Notification channels must be strings.',
                );
            }

            $channel = trim($channel);

            if ($channel === '') {
                throw new InvalidArgumentException(
                    'A notification channel cannot be empty.',
                );
            }

            $normalisedChannels[$channel] = $channel;
        }

        if ($normalisedChannels === []) {
            throw new InvalidArgumentException(
                'A notification must define at least one channel.',
            );
        }

        ksort(
            $normalisedChannels,
        );

        $this->module = $module;
        $this->name = $name;
        $this->class = $class;
        $this->channels = array_values(
            $normalisedChannels,
        );
        $this->queue = $queue;
    }

    public function key(): string
    {
        return sprintf(
            '%s:%s',
            $this->module,
            $this->name,
        );
    }

    /**
     * @return array{
     *     module: string,
     *     name: string,
     *     class: string,
     *     channels: array<int, string>,
     *     queue: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'module' => $this->module,
            'name' => $this->name,
            'class' => $this->class,
            'channels' => $this->channels,
            'queue' => $this->queue,
        ];
    }
}