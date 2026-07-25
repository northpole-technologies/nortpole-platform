<?php

declare(strict_types=1);

namespace Northpole\Runtime\Roles;

use InvalidArgumentException;

final readonly class RoleDefinition
{
    /**
     * @param  array<int, string>  $permissions
     * @param  array<int, string>  $contributingModules
     */
    public function __construct(
        private string $key,
        private string $name,
        private ?string $description = null,
        private array $permissions = [],
        private array $contributingModules = [],
        private bool $system = true,
    ) {
        if (trim($this->key) === '') {
            throw new InvalidArgumentException(
                'A role definition must have a key.'
            );
        }

        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'A role definition must have a name.'
            );
        }

        self::validateStringList(
            values: $this->permissions,
            field: 'permissions',
        );

        self::validateStringList(
            values: $this->contributingModules,
            field: 'contributing modules',
        );
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    public static function fromArray(
        string $moduleSlug,
        array $definition,
    ): self {
        $key = $definition['key'] ?? null;
        $name = $definition['name'] ?? null;
        $description = $definition['description'] ?? null;
        $permissions = $definition['permissions'] ?? [];
        $system = $definition['system'] ?? true;

        if (
            ! is_string($key)
            || trim($key) === ''
        ) {
            throw new InvalidArgumentException(
                'A role definition must have a key.'
            );
        }

        if (
            ! is_string($name)
            || trim($name) === ''
        ) {
            throw new InvalidArgumentException(
                'A role definition must have a name.'
            );
        }

        if (
            $description !== null
            && ! is_string($description)
        ) {
            throw new InvalidArgumentException(
                'Role definition description must be a string or null.'
            );
        }

        if (! is_array($permissions)) {
            throw new InvalidArgumentException(
                'Role definition permissions must be an array.'
            );
        }

        if (! is_bool($system)) {
            throw new InvalidArgumentException(
                'Role definition system must be a boolean.'
            );
        }

        $normalisedPermissions = self::normaliseStringList(
            $permissions,
            'permissions',
        );

        return new self(
            key: trim($key),
            name: trim($name),
            description: is_string($description)
                ? trim($description)
                : null,
            permissions: $normalisedPermissions,
            contributingModules: [
                trim($moduleSlug),
            ],
            system: $system,
        );
    }

    public function key(): string
    {
        return $this->key;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    /**
     * @return array<int, string>
     */
    public function permissions(): array
    {
        return $this->permissions;
    }

    /**
     * @return array<int, string>
     */
    public function contributingModules(): array
    {
        return $this->contributingModules;
    }

    public function system(): bool
    {
        return $this->system;
    }

    public function contributesPermission(
        string $permission
    ): bool {
        return in_array(
            trim($permission),
            $this->permissions,
            true,
        );
    }

    public function contributedBy(
        string $moduleSlug
    ): bool {
        return in_array(
            trim($moduleSlug),
            $this->contributingModules,
            true,
        );
    }

    public function merge(
        self $contribution
    ): self {
        if ($this->key !== $contribution->key()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot merge role definitions [%s] and [%s].',
                    $this->key,
                    $contribution->key(),
                )
            );
        }

        return new self(
            key: $this->key,
            name: $this->name,
            description: $this->description
                ?? $contribution->description(),
            permissions: self::mergeStringLists(
                $this->permissions,
                $contribution->permissions(),
            ),
            contributingModules: self::mergeStringLists(
                $this->contributingModules,
                $contribution->contributingModules(),
            ),
            system: $this->system
                || $contribution->system(),
        );
    }

    /**
     * @return array{
     *     key: string,
     *     name: string,
     *     description: string|null,
     *     permissions: array<int, string>,
     *     contributing_modules: array<int, string>,
     *     system: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'permissions' => $this->permissions,
            'contributing_modules' => $this->contributingModules,
            'system' => $this->system,
        ];
    }

    /**
     * @param  array<int, mixed>  $values
     *
     * @return array<int, string>
     */
    private static function normaliseStringList(
        array $values,
        string $field,
    ): array {
        $normalised = [];

        foreach ($values as $value) {
            if (
                ! is_string($value)
                || trim($value) === ''
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Role definition %s must contain non-empty strings.',
                        $field,
                    )
                );
            }

            $normalised[
                trim($value)
            ] = true;
        }

        $values = array_keys(
            $normalised
        );

        sort(
            $values
        );

        return $values;
    }

    /**
     * @param  array<int, string>  $values
     */
    private static function validateStringList(
        array $values,
        string $field,
    ): void {
        self::normaliseStringList(
            $values,
            $field,
        );
    }

    /**
     * @param  array<int, string>  $first
     * @param  array<int, string>  $second
     *
     * @return array<int, string>
     */
    private static function mergeStringLists(
        array $first,
        array $second,
    ): array {
        return self::normaliseStringList(
            array_merge(
                $first,
                $second,
            ),
            'values',
        );
    }
}
