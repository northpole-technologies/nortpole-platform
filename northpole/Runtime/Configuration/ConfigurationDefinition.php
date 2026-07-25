<?php

declare(strict_types=1);

namespace Northpole\Runtime\Configuration;

use InvalidArgumentException;

final readonly class ConfigurationDefinition
{
    private const SUPPORTED_TYPES = [
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

    public string $module;

    public string $key;

    public string $type;

    public ?string $label;

    public ?string $description;

    public mixed $default;

    /**
     * @var array<int, string>
     */
    public array $options;

    /**
     * @param  array<int, mixed>  $options
     */
    public function __construct(
        string $module,
        string $key,
        string $type,
        ?string $label = null,
        ?string $description = null,
        mixed $default = null,
        array $options = [],
        bool $hasDefault = false,
    ) {
        $module = trim($module);
        $key = trim($key);
        $type = strtolower(trim($type));
        $label = $label === null
            ? null
            : trim($label);
        $description = $description === null
            ? null
            : trim($description);

        if ($module === '') {
            throw new InvalidArgumentException(
                'A configuration module owner cannot be empty.',
            );
        }

        if ($key === '') {
            throw new InvalidArgumentException(
                'A configuration key cannot be empty.',
            );
        }

        if (! in_array($type, self::SUPPORTED_TYPES, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Configuration type [%s] is not supported.',
                    $type,
                ),
            );
        }

        if ($label === '') {
            throw new InvalidArgumentException(
                'A configuration label cannot be empty.',
            );
        }

        if ($description === '') {
            throw new InvalidArgumentException(
                'A configuration description cannot be empty.',
            );
        }

        $normalisedOptions = [];

        foreach ($options as $option) {
            if (! is_string($option)) {
                throw new InvalidArgumentException(
                    'Configuration options must be strings.',
                );
            }

            $option = trim($option);

            if ($option === '') {
                throw new InvalidArgumentException(
                    'A configuration option cannot be empty.',
                );
            }

            $normalisedOptions[$option] = $option;
        }

        if (
            $type === 'select'
            && $normalisedOptions === []
        ) {
            throw new InvalidArgumentException(
                'A select configuration must define options.',
            );
        }

        if ($hasDefault) {
            $this->validateDefault(
                $type,
                $default,
                array_values($normalisedOptions),
            );
        }

        $this->module = $module;
        $this->key = $key;
        $this->type = $type;
        $this->label = $label;
        $this->description = $description;
        $this->default = $default;
        $this->options = array_values(
            $normalisedOptions,
        );
    }

    public function registryKey(): string
    {
        return sprintf(
            '%s:%s',
            $this->module,
            $this->key,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'module' => $this->module,
            'key' => $this->key,
            'type' => $this->type,
            'label' => $this->label,
            'description' => $this->description,
            'default' => $this->default,
            'options' => $this->options,
        ];
    }

    /**
     * @param  array<int, string>  $options
     */
    private function validateDefault(
        string $type,
        mixed $default,
        array $options,
    ): void {
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
                    'Configuration default does not match type [%s].',
                    $type,
                ),
            );
        }

        if (
            $type === 'select'
            && ! in_array(
                $default,
                $options,
                true,
            )
        ) {
            throw new InvalidArgumentException(
                'A select configuration default must exist in its options.',
            );
        }
    }
}