<?php

namespace Platform\Registry;

class ModuleRegistry
{
    protected array $modules = [];

    public function add(string $module): void
    {
        $this->modules[] = $module;
    }

    public function all(): array
    {
        return $this->modules;
    }
}