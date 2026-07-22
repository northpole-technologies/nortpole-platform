<?php

namespace Northpole\Modules;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class ModuleResourceLoader
{
    public static function load(): void
    {
        $modulesPath = base_path('modules');

        if (! File::exists($modulesPath)) {
            return;
        }

        foreach (File::directories($modulesPath) as $modulePath) {
            $viewsPath = $modulePath . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'Views';

            if (! File::exists($viewsPath)) {
                continue;
            }

            $namespace = str(basename($modulePath))
                ->kebab()
                ->toString();

            View::addNamespace($namespace, $viewsPath);
        }
    }
}