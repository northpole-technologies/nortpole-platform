<?php

namespace Northpole\Loader;

class ModuleLoader
{
    protected string $modulePath;

    public function __construct()
    {
        $this->modulePath = base_path('modules');
    }

    public function discover(): array
    {
        if (! is_dir($this->modulePath)) {
            return [];
        }

        $modules = [];

        foreach (glob($this->modulePath.'/*', GLOB_ONLYDIR) as $directory) {
            $manifest = $directory.'/module.json';

            if (! file_exists($manifest)) {
                continue;
            }

            $module = json_decode(file_get_contents($manifest), true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $module['path'] = $directory;
                $modules[] = $module;
            }
        }

        return $modules;
    }
}
