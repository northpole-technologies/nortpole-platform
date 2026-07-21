<?php

namespace Platform\Modules;

use Illuminate\Support\Facades\File;

class ModuleManager
{
    protected array $modules = [];

    public function discover(): array
    {
        $path = base_path('modules');

        if (! File::exists($path)) {
            return [];
        }

        foreach (File::directories($path) as $directory) {

            $manifest = $directory . DIRECTORY_SEPARATOR . 'module.json';

            if (! File::exists($manifest)) {
                continue;
            }

            $module = json_decode(File::get($manifest), true);

            if ($module) {
                $this->modules[] = $module;
            }
        }

        return $this->modules;
    }

    public function all(): array
    {
        return $this->modules;
    }
}