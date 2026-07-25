<?php

declare(strict_types=1);

namespace Northpole\Runtime\Configuration;

use InvalidArgumentException;

final readonly class ModuleConfigurationRegistrar
{
    public function __construct(
        private ModuleConfigurationRegistry $registry,
    ) {}

    /**
     * @param  array<int, mixed>  $settings
     */
    public function register(
        string $module,
        array $settings,
    ): void {
        $module = trim($module);

        if ($module === '') {
            throw new InvalidArgumentException(
                'A configuration module owner cannot be empty.',
            );
        }

        foreach ($settings as $setting) {
            if (! is_array($setting)) {
                throw new InvalidArgumentException(
                    'A configuration definition must be an array.',
                );
            }

            $this->registry->register(
                $this->definition(
                    $module,
                    $setting,
                ),
            );
        }
    }

    public function registry(): ModuleConfigurationRegistry
    {
        return $this->registry;
    }

    /**
     * @param  array<string, mixed>  $setting
     */
    private function definition(
        string $module,
        array $setting,
    ): ConfigurationDefinition {
        return new ConfigurationDefinition(
            module: $module,
            key: $this->requiredString(
                $setting,
                'key',
            ),
            type: $this->requiredString(
                $setting,
                'type',
            ),
            label: $this->optionalString(
                $setting,
                'label',
            ),
            description: $this->optionalString(
                $setting,
                'description',
            ),
            default: $setting['default'] ?? null,
            options: $this->options(
                $setting,
            ),
            hasDefault: array_key_exists(
                'default',
                $setting,
            ),
        );
    }

    /**
     * @param  array<string, mixed>  $setting
     */
    private function requiredString(
        array $setting,
        string $field,
    ): string {
        $value = $setting[$field] ?? null;

        if (! is_string($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Configuration field [%s] must be a string.',
                    $field,
                ),
            );
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $setting
     */
    private function optionalString(
        array $setting,
        string $field,
    ): ?string {
        if (! array_key_exists($field, $setting)) {
            return null;
        }

        $value = $setting[$field];

        if (! is_string($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Configuration field [%s] must be a string.',
                    $field,
                ),
            );
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $setting
     *
     * @return array<int, mixed>
     */
    private function options(
        array $setting,
    ): array {
        if (! array_key_exists('options', $setting)) {
            return [];
        }

        $options = $setting['options'];

        if (
            ! is_array($options)
            || ! array_is_list($options)
        ) {
            throw new InvalidArgumentException(
                'Configuration field [options] must be a list.',
            );
        }

        return $options;
    }
}