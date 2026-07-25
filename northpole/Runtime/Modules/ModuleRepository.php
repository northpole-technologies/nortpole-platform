<?php

namespace Northpole\Runtime\Modules;

use Northpole\Runtime\Manifest\ModuleManifest;

final class ModuleRepository
{
    /**
     * @var array<string, ModuleManifest>
     */
    private array $modules = [];

    public function add(ModuleManifest $module): void
    {
        $this->modules[$module->slug()] = $module;
    }

    /**
     * @return array<string, ModuleManifest>
     */
    public function all(): array
    {
        return $this->modules;
    }

    public function has(string $slug): bool
    {
        return isset($this->modules[$slug]);
    }

    public function get(string $slug): ?ModuleManifest
    {
        return $this->modules[$slug] ?? null;
    }

    /**
     * @return array<string, ModuleManifest>
     */
    public function enabled(): array
    {
        return array_filter(
            $this->modules,
            fn (ModuleManifest $module): bool => $module->enabled()
        );
    }

    public function count(): int
    {
        return count($this->modules);
    }
}
