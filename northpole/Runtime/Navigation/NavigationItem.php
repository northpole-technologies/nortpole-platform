<?php

declare(strict_types=1);

namespace Northpole\Runtime\Navigation;

use InvalidArgumentException;

final readonly class NavigationItem
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        private string $moduleSlug,
        private string $label,
        private string $route,
        private ?string $icon = null,
        private ?string $permission = null,
        private ?string $group = null,
        private int $order = 100,
        private array $metadata = [],
    ) {
        if (trim($this->moduleSlug) === '') {
            throw new InvalidArgumentException(
                'A navigation item must have a module slug.'
            );
        }

        if (trim($this->label) === '') {
            throw new InvalidArgumentException(
                'A navigation item must have a label.'
            );
        }

        if (trim($this->route) === '') {
            throw new InvalidArgumentException(
                'A navigation item must have a route.'
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(
        string $moduleSlug,
        array $data,
    ): self {
        return new self(
            moduleSlug: $moduleSlug,
            label: self::requiredString(
                $data,
                'label',
            ),
            route: self::requiredString(
                $data,
                'route',
            ),
            icon: self::optionalString(
                $data,
                'icon',
            ),
            permission: self::optionalString(
                $data,
                'permission',
            ),
            group: self::optionalString(
                $data,
                'group',
            ),
            order: self::integer(
                $data,
                'order',
                100,
            ),
            metadata: self::extractMetadata($data),
        );
    }

    public function moduleSlug(): string
    {
        return $this->moduleSlug;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function route(): string
    {
        return $this->route;
    }

    public function icon(): ?string
    {
        return $this->icon;
    }

    public function permission(): ?string
    {
        return $this->permission;
    }

    public function group(): ?string
    {
        return $this->group;
    }

    public function order(): int
    {
        return $this->order;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    public function key(): string
    {
        return implode(
            ':',
            [
                $this->moduleSlug,
                $this->route,
                $this->label,
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'module' => $this->moduleSlug,
            'label' => $this->label,
            'route' => $this->route,
            'icon' => $this->icon,
            'permission' => $this->permission,
            'group' => $this->group,
            'order' => $this->order,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function requiredString(
        array $data,
        string $key,
    ): string {
        $value = $data[$key] ?? null;

        if (
            ! is_string($value)
            || trim($value) === ''
        ) {
            throw new InvalidArgumentException(
                "Navigation field [{$key}] must be a non-empty string."
            );
        }

        return trim($value);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function optionalString(
        array $data,
        string $key,
    ): ?string {
        $value = $data[$key] ?? null;

        if ($value === null) {
            return null;
        }

        if (! is_string($value)) {
            throw new InvalidArgumentException(
                "Navigation field [{$key}] must be a string or null."
            );
        }

        $value = trim($value);

        return $value === ''
            ? null
            : $value;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function integer(
        array $data,
        string $key,
        int $default,
    ): int {
        $value = $data[$key] ?? $default;

        if (! is_int($value)) {
            throw new InvalidArgumentException(
                "Navigation field [{$key}] must be an integer."
            );
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function extractMetadata(array $data): array
    {
        $knownFields = [
            'label',
            'route',
            'icon',
            'permission',
            'group',
            'order',
        ];

        return array_diff_key(
            $data,
            array_flip($knownFields)
        );
    }
}
