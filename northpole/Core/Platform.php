<?php

namespace Northpole\Core;

class Platform
{
    protected array $modules = [];

    public function register(string $module): void
    {
        $this->modules[] = $module;
    }

    public function modules(): array
    {
        return $this->modules;
    }
}
