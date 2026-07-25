<?php

namespace Northpole\Runtime\Lifecycle;

use Northpole\Runtime\Contracts\ModuleManifestContract;
use Northpole\Runtime\Runtime;

final class BootContext
{
    /**
     * @var array<string, mixed>
     */
    private array $attributes = [];

    private readonly ModuleResources $resources;

    public function __construct(
        private readonly Runtime $runtime,
        private readonly ModuleManifestContract $module,
    ) {
        $this->resources = new ModuleResources($module);
    }

    public function runtime(): Runtime
    {
        return $this->runtime;
    }

    public function module(): ModuleManifestContract
    {
        return $this->module;
    }

    public function resources(): ModuleResources
    {
        return $this->resources;
    }

    public function set(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
