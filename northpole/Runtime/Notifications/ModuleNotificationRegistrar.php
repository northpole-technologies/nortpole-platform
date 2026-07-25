<?php

declare(strict_types=1);

namespace Northpole\Runtime\Notifications;

use InvalidArgumentException;

final readonly class ModuleNotificationRegistrar
{
    public function __construct(
        private ModuleNotificationRegistry $registry,
    ) {}

    /**
     * @param  array<int, mixed>  $notifications
     */
    public function register(
        string $module,
        array $notifications,
    ): void {
        $module = trim($module);

        if ($module === '') {
            throw new InvalidArgumentException(
                'A notification module owner cannot be empty.',
            );
        }

        foreach ($notifications as $notification) {
            if (! is_array($notification)) {
                throw new InvalidArgumentException(
                    'A notification definition must be an array.',
                );
            }

            $this->registry->register(
                $this->definition(
                    $module,
                    $notification,
                ),
            );
        }
    }

    public function registry(): ModuleNotificationRegistry
    {
        return $this->registry;
    }

    /**
     * @param  array<string, mixed>  $notification
     */
    private function definition(
        string $module,
        array $notification,
    ): NotificationDefinition {
        return new NotificationDefinition(
            module: $module,
            name: $this->requiredString(
                $notification,
                'name',
            ),
            class: $this->requiredString(
                $notification,
                'class',
            ),
            channels: $this->channels(
                $notification,
            ),
            queue: $this->optionalString(
                $notification,
                'queue',
            ),
        );
    }

    /**
     * @param  array<string, mixed>  $notification
     */
    private function requiredString(
        array $notification,
        string $field,
    ): string {
        $value = $notification[$field] ?? null;

        if (! is_string($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Notification field [%s] must be a string.',
                    $field,
                ),
            );
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $notification
     *
     * @return array<int, mixed>
     */
    private function channels(
        array $notification,
    ): array {
        $channels = $notification['channels'] ?? null;

        if (! is_array($channels) || ! array_is_list($channels)) {
            throw new InvalidArgumentException(
                'Notification field [channels] must be a list.',
            );
        }

        return $channels;
    }

    /**
     * @param  array<string, mixed>  $notification
     */
    private function optionalString(
        array $notification,
        string $field,
    ): ?string {
        if (! array_key_exists($field, $notification)) {
            return null;
        }

        $value = $notification[$field];

        if (! is_string($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Notification field [%s] must be a string.',
                    $field,
                ),
            );
        }

        return $value;
    }
}