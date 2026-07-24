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
     * @param array<int, mixed> $dependencies
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
     * @param array<array-key, mixed> $dependencies
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
        return $this->data['provider'] ?? null;
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
    public function permissions(): array
    {
        return $this->data['permissions'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
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
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}