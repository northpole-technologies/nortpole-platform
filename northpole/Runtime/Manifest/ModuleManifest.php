<?php

declare(strict_types=1);

namespace Northpole\Runtime\Manifest;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\ModuleManifestContract;

final class ModuleManifest implements ModuleManifestContract
{
    public function __construct(
        private readonly array $data,
        private readonly string $path,
        private readonly string $manifestPath,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        foreach (['name', 'slug', 'version'] as $required) {
            if (! isset($this->data[$required])) {
                throw new InvalidArgumentException(
                    "Module manifest missing required field [{$required}]"
                );
            }
        }

        $this->validateProviders();
        $this->validateDependencies();
        $this->validateEvents();
        $this->validateNotifications();
        $this->validateSettings();
        $this->validateRoles();
        $this->validateCommands();
        $this->validateQueries();
        $this->validateJobs();
    }

    private function validateRoles(): void
    {
        if (! isset($this->data['roles'])) {
            return;
        }

        if (
            ! is_array($this->data['roles'])
            || ! array_is_list($this->data['roles'])
        ) {
            throw new InvalidArgumentException(
                'Module manifest field [roles] must be a list'
            );
        }

        foreach ($this->data['roles'] as $role) {
            if (! is_array($role)) {
                throw new InvalidArgumentException(
                    'Module manifest field [roles] must contain structured role definitions'
                );
            }

            if (
                ! isset($role['key'])
                || ! is_string($role['key'])
                || trim($role['key']) === ''
            ) {
                throw new InvalidArgumentException(
                    'Module role definitions must contain a non-empty key'
                );
            }

            if (
                ! isset($role['name'])
                || ! is_string($role['name'])
                || trim($role['name']) === ''
            ) {
                throw new InvalidArgumentException(
                    'Module role definitions must contain a non-empty name'
                );
            }

            if (
                array_key_exists('description', $role)
                && $role['description'] !== null
                && ! is_string($role['description'])
            ) {
                throw new InvalidArgumentException(
                    'Module role definition descriptions must be strings or null'
                );
            }

            if (
                array_key_exists('permissions', $role)
                && ! is_array($role['permissions'])
            ) {
                throw new InvalidArgumentException(
                    'Module role definition permissions must be lists'
                );
            }

            if (
                isset($role['permissions'])
                && ! array_is_list($role['permissions'])
            ) {
                throw new InvalidArgumentException(
                    'Module role definition permissions must be lists'
                );
            }

            foreach ($role['permissions'] ?? [] as $permission) {
                if (
                    ! is_string($permission)
                    || trim($permission) === ''
                ) {
                    throw new InvalidArgumentException(
                        'Module role definition permissions must contain non-empty strings'
                    );
                }
            }

            if (
                array_key_exists('system', $role)
                && ! is_bool($role['system'])
            ) {
                throw new InvalidArgumentException(
                    'Module role definition system flags must be booleans'
                );
            }
        }
    }

    private function validateProviders(): void
    {
        if (
            isset($this->data['provider'])
            && ! is_string($this->data['provider'])
        ) {
            throw new InvalidArgumentException(
                'Module manifest field [provider] must be a string'
            );
        }

        if (! isset($this->data['providers'])) {
            return;
        }

        if (
            ! is_array($this->data['providers'])
            || ! array_is_list($this->data['providers'])
        ) {
            throw new InvalidArgumentException(
                'Module manifest field [providers] must be a list'
            );
        }

        foreach ($this->data['providers'] as $provider) {
            if (
                ! is_string($provider)
                || trim($provider) === ''
            ) {
                throw new InvalidArgumentException(
                    'Module manifest field [providers] must contain non-empty provider class names'
                );
            }
        }
    }

    private function validateDependencies(): void
    {
        if (
            isset($this->data['dependencies'])
            && ! is_array($this->data['dependencies'])
        ) {
            throw new InvalidArgumentException(
                'Module manifest field [dependencies] must be an array'
            );
        }

        $dependencies = $this->data['dependencies'] ?? [];

        if (array_is_list($dependencies)) {
            $this->validateLegacyDependencies(
                $dependencies
            );

            return;
        }

        $this->validateVersionedDependencies(
            $dependencies
        );
    }

    /**
     * @param  array<int, mixed>  $dependencies
     */
    private function validateLegacyDependencies(
        array $dependencies
    ): void {
        foreach ($dependencies as $dependency) {
            if (
                ! is_string($dependency)
                || trim($dependency) === ''
            ) {
                throw new InvalidArgumentException(
                    'Module manifest dependencies must contain non-empty strings'
                );
            }
        }
    }

    /**
     * @param  array<array-key, mixed>  $dependencies
     */
    private function validateVersionedDependencies(
        array $dependencies
    ): void {
        foreach (
            $dependencies as $dependency => $constraint
        ) {
            if (
                ! is_string($dependency)
                || trim($dependency) === ''
            ) {
                throw new InvalidArgumentException(
                    'Module manifest dependency names must be non-empty strings'
                );
            }

            if (
                ! is_string($constraint)
                || trim($constraint) === ''
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Module manifest dependency [%s] must have a non-empty version constraint',
                        $dependency
                    )
                );
            }
        }
    }

    private function validateEvents(): void
    {
        if (! isset($this->data['events'])) {
            return;
        }

        if (! is_array($this->data['events'])) {
            throw new InvalidArgumentException(
                'Module manifest field [events] must be an object'
            );
        }

        $events = $this->data['events'];

        $this->validatePublishedEvents(
            $events['publishes'] ?? []
        );

        $this->validateEventSubscribers(
            $events['subscribes'] ?? []
        );
    }

    private function validatePublishedEvents(
        mixed $publishedEvents
    ): void {
        if (
            ! is_array($publishedEvents)
            || ! array_is_list($publishedEvents)
        ) {
            throw new InvalidArgumentException(
                'Module manifest field [events.publishes] must be a list'
            );
        }

        foreach ($publishedEvents as $eventName) {
            if (
                ! is_string($eventName)
                || trim($eventName) === ''
            ) {
                throw new InvalidArgumentException(
                    'Module manifest field [events.publishes] must contain non-empty strings'
                );
            }
        }
    }

    private function validateEventSubscribers(
        mixed $subscribers
    ): void {
        if (! is_array($subscribers)) {
            throw new InvalidArgumentException(
                'Module manifest field [events.subscribes] must be an object'
            );
        }

        foreach ($subscribers as $eventName => $listeners) {
            if (
                ! is_string($eventName)
                || trim($eventName) === ''
            ) {
                throw new InvalidArgumentException(
                    'Module manifest subscribed event names must be non-empty strings'
                );
            }

            if (
                ! is_array($listeners)
                || ! array_is_list($listeners)
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Module manifest event subscription [%s] must contain a list of listener classes',
                        $eventName
                    )
                );
            }

            foreach ($listeners as $listenerClass) {
                if (
                    ! is_string($listenerClass)
                    || trim($listenerClass) === ''
                ) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Module manifest event subscription [%s] must contain non-empty listener class names',
                            $eventName
                        )
                    );
                }
            }
        }
    }

    private function validateNotifications(): void
    {
        if (! isset($this->data['notifications'])) {
            return;
        }

        if (! is_array($this->data['notifications'])) {
            throw new InvalidArgumentException(
                'Module manifest field [notifications] must be an object'
            );
        }

        $this->validateSentNotifications(
            $this->data['notifications']['sends'] ?? []
        );
    }

    private function validateSentNotifications(
        mixed $notifications
    ): void {
        if (
            ! is_array($notifications)
            || ! array_is_list($notifications)
        ) {
            throw new InvalidArgumentException(
                'Module manifest field [notifications.sends] must be a list'
            );
        }

        foreach ($notifications as $index => $notification) {
            if (! is_array($notification)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Module manifest notification [%d] must be an object',
                        $index
                    )
                );
            }

            $this->validateNotificationStringField(
                $notification,
                $index,
                'name'
            );

            $this->validateNotificationStringField(
                $notification,
                $index,
                'class'
            );

            $this->validateNotificationChannels(
                $notification,
                $index
            );

            $this->validateNotificationQueue(
                $notification,
                $index
            );
        }
    }

    /**
     * @param  array<string, mixed>  $notification
     */
    private function validateNotificationStringField(
        array $notification,
        int $index,
        string $field
    ): void {
        $value = $notification[$field] ?? null;

        if (
            ! is_string($value)
            || trim($value) === ''
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module manifest notification [%d] must define a non-empty %s',
                    $index,
                    $field
                )
            );
        }
    }

    /**
     * @param  array<string, mixed>  $notification
     */
    private function validateNotificationChannels(
        array $notification,
        int $index
    ): void {
        $channels = $notification['channels'] ?? null;

        if (
            ! is_array($channels)
            || ! array_is_list($channels)
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module manifest notification [%d] field [channels] must be a list',
                    $index
                )
            );
        }

        if ($channels === []) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module manifest notification [%d] must define at least one channel',
                    $index
                )
            );
        }

        foreach ($channels as $channel) {
            if (
                ! is_string($channel)
                || trim($channel) === ''
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Module manifest notification [%d] field [channels] must contain non-empty strings',
                        $index
                    )
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $notification
     */
    private function validateNotificationQueue(
        array $notification,
        int $index
    ): void {
        if (! array_key_exists('queue', $notification)) {
            return;
        }

        $queue = $notification['queue'];

        if (
            ! is_string($queue)
            || trim($queue) === ''
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module manifest notification [%d] field [queue] must be a non-empty string',
                    $index
                )
            );
        }
    }

    private function validateSettings(): void
    {
        if (! isset($this->data['settings'])) {
            return;
        }

        if (
            ! is_array($this->data['settings'])
            || ! array_is_list($this->data['settings'])
        ) {
            throw new InvalidArgumentException(
                'Module manifest field [settings] must be a list'
            );
        }

        foreach ($this->data['settings'] as $index => $setting) {
            if (! is_array($setting)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Module manifest setting [%d] must be an object',
                        $index
                    )
                );
            }

            $this->validateSettingStringField(
                $setting,
                $index,
                'key'
            );

            $this->validateSettingStringField(
                $setting,
                $index,
                'type'
            );

            $this->validateSettingType(
                $setting,
                $index
            );

            $this->validateOptionalSettingStringField(
                $setting,
                $index,
                'label'
            );

            $this->validateOptionalSettingStringField(
                $setting,
                $index,
                'description'
            );

            $this->validateSettingOptions(
                $setting,
                $index
            );

            $this->validateSettingDefault(
                $setting,
                $index
            );
        }
    }

    /**
     * @param  array<string, mixed>  $setting
     */
    private function validateSettingStringField(
        array $setting,
        int $index,
        string $field
    ): void {
        $value = $setting[$field] ?? null;

        if (
            ! is_string($value)
            || trim($value) === ''
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module manifest setting [%d] must define a non-empty %s',
                    $index,
                    $field
                )
            );
        }
    }

    /**
     * @param  array<string, mixed>  $setting
     */
    private function validateOptionalSettingStringField(
        array $setting,
        int $index,
        string $field
    ): void {
        if (! array_key_exists($field, $setting)) {
            return;
        }

        if (
            ! is_string($setting[$field])
            || trim($setting[$field]) === ''
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module manifest setting [%d] field [%s] must be a non-empty string',
                    $index,
                    $field
                )
            );
        }
    }

    /**
     * @param  array<string, mixed>  $setting
     */
    private function validateSettingType(
        array $setting,
        int $index
    ): void {
        $type = strtolower(
            trim((string) ($setting['type'] ?? ''))
        );

        $supportedTypes = [
            'boolean',
            'date',
            'float',
            'integer',
            'json',
            'password',
            'select',
            'string',
            'textarea',
        ];

        if (! in_array($type, $supportedTypes, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module manifest setting [%d] has unsupported type [%s]',
                    $index,
                    $type
                )
            );
        }
    }

    /**
     * @param  array<string, mixed>  $setting
     */
    private function validateSettingOptions(
        array $setting,
        int $index
    ): void {
        $type = strtolower(
            trim((string) ($setting['type'] ?? ''))
        );

        if (! array_key_exists('options', $setting)) {
            if ($type === 'select') {
                throw new InvalidArgumentException(
                    sprintf(
                        'Module manifest setting [%d] of type [select] must define options',
                        $index
                    )
                );
            }

            return;
        }

        $options = $setting['options'];

        if (
            ! is_array($options)
            || ! array_is_list($options)
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module manifest setting [%d] field [options] must be a list',
                    $index
                )
            );
        }

        if ($type === 'select' && $options === []) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module manifest setting [%d] of type [select] must define at least one option',
                    $index
                )
            );
        }

        foreach ($options as $option) {
            if (
                ! is_string($option)
                || trim($option) === ''
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Module manifest setting [%d] field [options] must contain non-empty strings',
                        $index
                    )
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $setting
     */
    private function validateSettingDefault(
        array $setting,
        int $index
    ): void {
        if (! array_key_exists('default', $setting)) {
            return;
        }

        $default = $setting['default'];
        $type = strtolower(
            trim((string) ($setting['type'] ?? ''))
        );

        $valid = match ($type) {
            'boolean' => is_bool($default),
            'integer' => is_int($default),
            'float' => is_float($default) || is_int($default),
            'json' => is_array($default),
            'select' => is_string($default),
            'date',
            'password',
            'string',
            'textarea' => is_string($default),
            default => false,
        };

        if (! $valid) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module manifest setting [%d] default value does not match type [%s]',
                    $index,
                    $type
                )
            );
        }

        if (
            $type === 'select'
            && ! in_array(
                $default,
                $setting['options'] ?? [],
                true
            )
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module manifest setting [%d] default value must exist in its options',
                    $index
                )
            );
        }
    }
    private function validateCommands(): void
    {
        if (! isset($this->data['commands'])) {
            return;
        }

        if (! is_array($this->data['commands'])) {
            throw new InvalidArgumentException(
                'Module manifest field [commands] must be an object'
            );
        }

        $this->validateHandledCommands(
            $this->data['commands']['handles'] ?? []
        );
    }

    private function validateHandledCommands(
        mixed $handledCommands
    ): void {
        if (! is_array($handledCommands)) {
            throw new InvalidArgumentException(
                'Module manifest field [commands.handles] must be an object'
            );
        }

        foreach (
            $handledCommands as $commandName => $handlerClass
        ) {
            if (
                ! is_string($commandName)
                || trim($commandName) === ''
            ) {
                throw new InvalidArgumentException(
                    'Module manifest handled command names must be non-empty strings'
                );
            }

            if (
                ! is_string($handlerClass)
                || trim($handlerClass) === ''
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Module manifest command [%s] must have a non-empty handler class',
                        $commandName
                    )
                );
            }
        }
    }

    private function validateQueries(): void
    {
        if (! isset($this->data['queries'])) {
            return;
        }

        if (! is_array($this->data['queries'])) {
            throw new InvalidArgumentException(
                'Module manifest field [queries] must be an object'
            );
        }

        $this->validateHandledQueries(
            $this->data['queries']['handles'] ?? []
        );
    }

    private function validateHandledQueries(
        mixed $handledQueries
    ): void {
        if (! is_array($handledQueries)) {
            throw new InvalidArgumentException(
                'Module manifest field [queries.handles] must be an object'
            );
        }

        foreach (
            $handledQueries as $queryName => $handlerClass
        ) {
            if (
                ! is_string($queryName)
                || trim($queryName) === ''
            ) {
                throw new InvalidArgumentException(
                    'Module manifest handled query names must be non-empty strings'
                );
            }

            if (
                ! is_string($handlerClass)
                || trim($handlerClass) === ''
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Module manifest query [%s] must have a non-empty handler class',
                        $queryName
                    )
                );
            }
        }
    }

    private function validateJobs(): void
    {
        if (! isset($this->data['jobs'])) {
            return;
        }

        if (! is_array($this->data['jobs'])) {
            throw new InvalidArgumentException(
                'Module manifest field [jobs] must be an object'
            );
        }

        $this->validateScheduledJobs(
            $this->data['jobs']['scheduled'] ?? []
        );
    }

    private function validateScheduledJobs(
        mixed $scheduledJobs
    ): void {
        if (
            ! is_array($scheduledJobs)
            || ! array_is_list($scheduledJobs)
        ) {
            throw new InvalidArgumentException(
                'Module manifest field [jobs.scheduled] must be a list'
            );
        }

        foreach (
            $scheduledJobs as $index => $scheduledJob
        ) {
            if (! is_array($scheduledJob)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Module manifest scheduled job [%d] must be an object',
                        $index
                    )
                );
            }

            $this->validateScheduledJobClass(
                $scheduledJob,
                $index
            );

            $this->validateScheduledJobFrequency(
                $scheduledJob,
                $index
            );

            $this->validateScheduledJobTime(
                $scheduledJob,
                $index
            );

            $this->validateScheduledJobQueue(
                $scheduledJob,
                $index
            );

            $this->validateScheduledJobBooleanOption(
                $scheduledJob,
                $index,
                'without_overlapping'
            );

            $this->validateScheduledJobBooleanOption(
                $scheduledJob,
                $index,
                'run_in_background'
            );
        }
    }

    /**
     * @param  array<string, mixed>  $scheduledJob
     */
    private function validateScheduledJobClass(
        array $scheduledJob,
        int $index
    ): void {
        $class = $scheduledJob['class'] ?? null;

        if (
            ! is_string($class)
            || trim($class) === ''
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module manifest scheduled job [%d] must define a non-empty class',
                    $index
                )
            );
        }
    }

    /**
     * @param  array<string, mixed>  $scheduledJob
     */
    private function validateScheduledJobFrequency(
        array $scheduledJob,
        int $index
    ): void {
        $frequency = $scheduledJob['frequency'] ?? null;

        if (
            ! is_string($frequency)
            || trim($frequency) === ''
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module manifest scheduled job [%d] must define a non-empty frequency',
                    $index
                )
            );
        }

        $supportedFrequencies = [
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

        $normalisedFrequency = strtolower(
            trim($frequency)
        );

        if (
            ! in_array(
                $normalisedFrequency,
                $supportedFrequencies,
                true
            )
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module manifest scheduled job [%d] has unsupported frequency [%s]',
                    $index,
                    $normalisedFrequency
                )
            );
        }
    }

    /**
     * @param  array<string, mixed>  $scheduledJob
     */
    private function validateScheduledJobTime(
        array $scheduledJob,
        int $index
    ): void {
        if (! array_key_exists('at', $scheduledJob)) {
            return;
        }

        $time = $scheduledJob['at'];

        if (
            ! is_string($time)
            || preg_match(
                '/^(?:[01]\d|2[0-3]):[0-5]\d$/',
                trim($time)
            ) !== 1
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module manifest scheduled job [%d] field [at] must use 24-hour HH:MM format',
                    $index
                )
            );
        }
    }

    /**
     * @param  array<string, mixed>  $scheduledJob
     */
    private function validateScheduledJobQueue(
        array $scheduledJob,
        int $index
    ): void {
        if (! array_key_exists('queue', $scheduledJob)) {
            return;
        }

        $queue = $scheduledJob['queue'];

        if (
            ! is_string($queue)
            || trim($queue) === ''
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module manifest scheduled job [%d] field [queue] must be a non-empty string',
                    $index
                )
            );
        }
    }

    /**
     * @param  array<string, mixed>  $scheduledJob
     */
    private function validateScheduledJobBooleanOption(
        array $scheduledJob,
        int $index,
        string $option
    ): void {
        if (! array_key_exists($option, $scheduledJob)) {
            return;
        }

        if (! is_bool($scheduledJob[$option])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Module manifest scheduled job [%d] field [%s] must be a boolean',
                    $index,
                    $option
                )
            );
        }
    }

    public function name(): string
    {
        return (string) $this->data['name'];
    }

    public function slug(): string
    {
        return (string) $this->data['slug'];
    }

    public function version(): string
    {
        return (string) $this->data['version'];
    }

    public function description(): ?string
    {
        return $this->data['description'] ?? null;
    }

    public function provider(): ?string
    {
        return $this->providers()[0] ?? null;
    }

    /**
     * @return array<int, string>
     */
    public function providers(): array
    {
        $providers = [];

        $legacyProvider = $this->data['provider'] ?? null;

        if (
            is_string($legacyProvider)
            && trim($legacyProvider) !== ''
        ) {
            $providers[trim($legacyProvider)] = true;
        }

        foreach ($this->data['providers'] ?? [] as $provider) {
            $providers[trim($provider)] = true;
        }

        return array_keys($providers);
    }

    public function enabled(): bool
    {
        return (bool) ($this->data['enabled'] ?? false);
    }

    public function path(): string
    {
        return $this->path;
    }

    public function manifestPath(): string
    {
        return $this->manifestPath;
    }

    /**
     * @return array<int, string>
     */
    public function dependencies(): array
    {
        return array_keys(
            $this->dependencyConstraints()
        );
    }

    /**
     * @return array<string, string>
     */
    public function dependencyConstraints(): array
    {
        $dependencies = $this->data['dependencies'] ?? [];

        if (array_is_list($dependencies)) {
            $constraints = [];

            foreach ($dependencies as $dependency) {
                $constraints[trim($dependency)] = '*';
            }

            return $constraints;
        }

        $constraints = [];

        foreach (
            $dependencies as $dependency => $constraint
        ) {
            $constraints[trim($dependency)] = trim($constraint);
        }

        return $constraints;
    }

    /**
     * @return array<string, string>
     */
    public function routes(): array
    {
        return $this->data['routes'] ?? [];
    }

    public function viewsPath(): ?string
    {
        return $this->data['views'] ?? null;
    }

    public function migrationsPath(): ?string
    {
        return $this->data['migrations'] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function configuration(): array
    {
        return $this->data['config'] ?? [];
    }

    /**
     * @return array<int, string>
     */
    /**
     * @return array<int, array<string, mixed>>
     */
    public function settings(): array
    {
        $settings = $this->data['settings'] ?? [];
        $normalisedSettings = [];

        foreach ($settings as $setting) {
            $normalisedSetting = [
                'key' => trim($setting['key']),
                'type' => strtolower(
                    trim($setting['type'])
                ),
            ];

            if (array_key_exists('label', $setting)) {
                $normalisedSetting['label'] = trim(
                    $setting['label']
                );
            }

            if (array_key_exists('description', $setting)) {
                $normalisedSetting['description'] = trim(
                    $setting['description']
                );
            }

            if (array_key_exists('default', $setting)) {
                $normalisedSetting['default'] =
                    $setting['default'];
            }

            if (array_key_exists('options', $setting)) {
                $normalisedOptions = [];

                foreach ($setting['options'] as $option) {
                    $normalisedOption = trim($option);

                    $normalisedOptions[
                        $normalisedOption
                    ] = $normalisedOption;
                }

                $normalisedSetting['options'] = array_values(
                    $normalisedOptions
                );
            }

            $normalisedSettings[] = $normalisedSetting;
        }

        return $normalisedSettings;
    }
    public function permissions(): array
    {
        return $this->data['permissions'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    /**
     * @return array<int, array<string, mixed>>
     */
    public function roles(): array
    {
        return $this->data['roles'] ?? [];
    }

    public function navigation(): array
    {
        return $this->data['navigation'] ?? [];
    }

    /**
     * @return array<int, string>
     */
    public function capabilities(): array
    {
        return $this->data['capabilities'] ?? [];
    }

    /**
     * @return array<int, string>
     */
    public function publishedEvents(): array
    {
        $publishedEvents = $this->data['events']['publishes'] ?? [];

        $normalisedEvents = [];

        foreach ($publishedEvents as $eventName) {
            $normalisedEvents[trim($eventName)] = true;
        }

        return array_keys($normalisedEvents);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function eventSubscribers(): array
    {
        $subscribers = $this->data['events']['subscribes'] ?? [];

        $normalisedSubscribers = [];

        foreach ($subscribers as $eventName => $listeners) {
            $normalisedEventName = trim($eventName);
            $normalisedListeners = [];

            foreach ($listeners as $listenerClass) {
                $normalisedListeners[trim($listenerClass)] = true;
            }

            $normalisedSubscribers[$normalisedEventName] = array_keys(
                $normalisedListeners
            );
        }

        return $normalisedSubscribers;
    }

    /**
     * @return array<int, array{
     *     name: string,
     *     class: string,
     *     channels: array<int, string>,
     *     queue?: string
     * }>
     */
    public function notifications(): array
    {
        $notifications =
            $this->data['notifications']['sends'] ?? [];

        $normalisedNotifications = [];

        foreach ($notifications as $notification) {
            $normalisedChannels = [];

            foreach ($notification['channels'] as $channel) {
                $normalisedChannel = trim($channel);

                $normalisedChannels[
                    $normalisedChannel
                ] = $normalisedChannel;
            }

            ksort(
                $normalisedChannels
            );

            $normalisedNotification = [
                'name' => trim(
                    $notification['name']
                ),
                'class' => trim(
                    $notification['class']
                ),
                'channels' => array_values(
                    $normalisedChannels
                ),
            ];

            if (array_key_exists('queue', $notification)) {
                $normalisedNotification['queue'] = trim(
                    $notification['queue']
                );
            }

            $normalisedNotifications[] =
                $normalisedNotification;
        }

        return $normalisedNotifications;
    }

    /**
     * @return array<string, string>
     */
    public function handledCommands(): array
    {
        $handledCommands = $this->data['commands']['handles'] ?? [];

        $normalisedCommands = [];

        foreach (
            $handledCommands as $commandName => $handlerClass
        ) {
            $normalisedCommands[trim($commandName)] = trim(
                $handlerClass
            );
        }

        return $normalisedCommands;
    }

    /**
     * @return array<string, string>
     */
    public function handledQueries(): array
    {
        $handledQueries = $this->data['queries']['handles'] ?? [];

        $normalisedQueries = [];

        foreach (
            $handledQueries as $queryName => $handlerClass
        ) {
            $normalisedQueries[trim($queryName)] = trim(
                $handlerClass
            );
        }

        return $normalisedQueries;
    }

    /**
     * @return array<int, array{
     *     class: string,
     *     frequency: string,
     *     at?: string,
     *     queue?: string,
     *     without_overlapping?: bool,
     *     run_in_background?: bool
     * }>
     */
    public function scheduledJobs(): array
    {
        $scheduledJobs = $this->data['jobs']['scheduled'] ?? [];
        $normalisedJobs = [];

        foreach ($scheduledJobs as $scheduledJob) {
            $normalisedJob = [
                'class' => trim($scheduledJob['class']),
                'frequency' => strtolower(
                    trim($scheduledJob['frequency'])
                ),
            ];

            if (array_key_exists('at', $scheduledJob)) {
                $normalisedJob['at'] = trim(
                    $scheduledJob['at']
                );
            }

            if (array_key_exists('queue', $scheduledJob)) {
                $normalisedJob['queue'] = trim(
                    $scheduledJob['queue']
                );
            }

            if (
                array_key_exists(
                    'without_overlapping',
                    $scheduledJob
                )
            ) {
                $normalisedJob['without_overlapping'] =
                    $scheduledJob['without_overlapping'];
            }

            if (
                array_key_exists(
                    'run_in_background',
                    $scheduledJob
                )
            ) {
                $normalisedJob['run_in_background'] =
                    $scheduledJob['run_in_background'];
            }

            $normalisedJobs[] = $normalisedJob;
        }

        return $normalisedJobs;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
