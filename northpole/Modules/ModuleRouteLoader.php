<?php

namespace Northpole\Modules;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class ModuleRouteLoader
{
    public static function load(): void
    {
        $modulesPath = base_path('modules');

        if (! File::exists($modulesPath)) {
            return;
        }

        foreach (File::directories($modulesPath) as $modulePath) {
            $manifestPath = $modulePath . DIRECTORY_SEPARATOR . 'module.json';

            if (! File::exists($manifestPath)) {
                continue;
            }

            $manifest = json_decode(File::get($manifestPath), true);

            if (! is_array($manifest)) {
                continue;
            }

            if (($manifest['enabled'] ?? true) !== true) {
                continue;
            }

            $webRoutes = $modulePath . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . 'web.php';
            $apiRoutes = $modulePath . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . 'api.php';

            if (File::exists($webRoutes)) {
                Route::middleware('web')->group($webRoutes);
            }

            if (File::exists($apiRoutes)) {
                Route::middleware('api')
                    ->prefix('api')
                    ->group($apiRoutes);
            }
        }
    }
}