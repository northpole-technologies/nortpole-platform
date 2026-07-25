<?php

namespace Northpole\Registry;

use Northpole\Loader\ModuleLoader;

class ModuleRegistry
{
    protected array $modules = [];

    public function __construct()
    {
        $this->modules = (new ModuleLoader)->discover();
    }

    public function all(): array
    {
        return $this->modules;
    }

    public function providers(): array
    {
        return array_filter(
            array_column($this->modules, 'provider')
        );
    }
}
