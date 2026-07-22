<?php

namespace Platform\Modules;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class ModuleRouteLoader
{
    public static function load(): void
    {
        $modulesPath = base_path('modules');

        if (! File::isDirectory($modulesPath)) {
            return;
        }

        foreach (File::directories($modulesPath) as $modulePath) {
            if (! self::isEnabled($modulePath)) {
                continue;
            }

            self::loadWebRoutes($modulePath);
            self::loadApiRoutes($modulePath);
        }
    }

    private static function isEnabled(string $modulePath): bool
    {
        $manifestPath = $modulePath.DIRECTORY_SEPARATOR.'module.json';

        if (! File::exists($manifestPath)) {
            return false;
        }

        $manifest = json_decode(
            File::get($manifestPath),
            true
        );

        if (! is_array($manifest)) {
            return false;
        }

        return (bool) ($manifest['enabled'] ?? false);
    }

    private static function loadWebRoutes(string $modulePath): void
    {
        $routePath = $modulePath
            .DIRECTORY_SEPARATOR
            .'Routes'
            .DIRECTORY_SEPARATOR
            .'web.php';

        if (! File::exists($routePath)) {
            return;
        }

        Route::middleware('web')
            ->group($routePath);
    }

    private static function loadApiRoutes(string $modulePath): void
    {
        $routePath = $modulePath
            .DIRECTORY_SEPARATOR
            .'Routes'
            .DIRECTORY_SEPARATOR
            .'api.php';

        if (! File::exists($routePath)) {
            return;
        }

        Route::middleware('api')
            ->prefix('api')
            ->group($routePath);
    }
}
