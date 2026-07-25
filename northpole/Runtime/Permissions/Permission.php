<?php

declare(strict_types=1);

namespace Northpole\Runtime\Permissions;

use InvalidArgumentException;

final readonly class Permission
{
    /**
     * @param  array<int, string>  $defaultRoles
     */
    public function __construct(
        private string $moduleSlug,
        private string $name,
        private ?string $title = null,
        private ?string $description = null,
        private ?string $group = null,
        private array $defaultRoles = [],
        private bool $dangerous = false,
    ) {
        if (trim($this->moduleSlug) === '') {
            throw new InvalidArgumentException(
                'A permission must have a module slug.'
            );
        }

        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'A permission must have a name.'
            );
        }

        foreach ($this->defaultRoles as $role) {
            if (
                ! is_string($role)
                || trim($role) === ''
            ) {
                throw new InvalidArgumentException(
                    'Permission default roles must be non-empty strings.'
                );
            }
        }
    }

    public static function fromString(
        string $moduleSlug,
        string $name,
    ): self {
        return new self(
            moduleSlug: trim($moduleSlug),
            name: trim($name),
        );
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    public static function fromArray(
        string $moduleSlug,
        array $definition,
    ): self {
        $name = $definition['key']
            ?? $definition['name']
            ?? null;

        if (
            ! is_string($name)
            || trim($name) === ''
        ) {
            throw new InvalidArgumentException(
                'A permission definition must contain a non-empty key.'
            );
        }

        $title = $definition['title'] ?? null;
        $description = $definition['description'] ?? null;
        $group = $definition['group'] ?? null;
        $defaultRoles = $definition['default_roles']
            ?? $definition['roles']
            ?? [];
        $dangerous = $definition['dangerous'] ?? false;

        self::validateOptionalString(
            value: $title,
            field: 'title',
        );

        self::validateOptionalString(
            value: $description,
            field: 'description',
        );

        self::validateOptionalString(
            value: $group,
            field: 'group',
        );

        if (! is_array($defaultRoles)) {
            throw new InvalidArgumentException(
                'Permission default roles must be an array.'
            );
        }

        if (! is_bool($dangerous)) {
            throw new InvalidArgumentException(
                'Permission dangerous must be a boolean.'
            );
        }

        return new self(
            moduleSlug: trim($moduleSlug),
            name: trim($name),
            title: is_string($title)
                ? trim($title)
                : null,
            description: is_string($description)
                ? trim($description)
                : null,
            group: is_string($group)
                ? trim($group)
                : null,
            defaultRoles: array_values(
                array_map(
                    static fn (mixed $role): mixed => is_string($role)
                        ? trim($role)
                        : $role,
                    $defaultRoles,
                )
            ),
            dangerous: $dangerous,
        );
    }

    public function moduleSlug(): string
    {
        return $this->moduleSlug;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function key(): string
    {
        return $this->name;
    }

    public function title(): string
    {
        if (
            $this->title !== null
            && $this->title !== ''
        ) {
            return $this->title;
        }

        return self::generateTitle(
            $this->name
        );
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function group(): ?string
    {
        return $this->group;
    }

    /**
     * @return array<int, string>
     */
    public function defaultRoles(): array
    {
        return $this->defaultRoles;
    }

    public function dangerous(): bool
    {
        return $this->dangerous;
    }

    /**
     * @return array{
     *     module: string,
     *     key: string,
     *     name: string,
     *     title: string,
     *     description: string|null,
     *     group: string|null,
     *     default_roles: array<int, string>,
     *     dangerous: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'module' => $this->moduleSlug,
            'key' => $this->key(),
            'name' => $this->name,
            'title' => $this->title(),
            'description' => $this->description,
            'group' => $this->group,
            'default_roles' => $this->defaultRoles,
            'dangerous' => $this->dangerous,
        ];
    }

    private static function validateOptionalString(
        mixed $value,
        string $field,
    ): void {
        if (
            $value !== null
            && ! is_string($value)
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Permission %s must be a string or null.',
                    $field
                )
            );
        }
    }

    private static function generateTitle(
        string $permission
    ): string {
        $segments = explode(
            '.',
            $permission
        );

        $title = end($segments);

        if (! is_string($title)) {
            return $permission;
        }

        return ucwords(
            str_replace(
                ['-', '_'],
                ' ',
                $title
            )
        );
    }
}