<?php

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

    public function configuration(): array
    {
        return $this->data['config'] ?? [];
    }

    public function permissions(): array
    {
        return $this->data['permissions'] ?? [];
    }

    public function navigation(): array
    {
        return $this->data['navigation'] ?? [];
    }

    public function capabilities(): array
    {
        return $this->data['capabilities'] ?? [];
    }

    public function toArray(): array
    {
        return $this->data;
    }
}