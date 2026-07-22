<?php

namespace Platform\Modules;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class ModuleResourceLoader
{
    public static function load(): void
    {
        $modulesPath = base_path('modules');

        if (! File::isDirectory($modulesPath)) {
            return;
        }

        foreach (File::directories($modulesPath) as $modulePath) {

            $manifest = self::manifest($modulePath);

            if ($manifest === null) {
                continue;
            }

            if (! ($manifest['enabled'] ?? false)) {
                continue;
            }

            $slug = $manifest['slug'] ?? '';

            self::loadViews($modulePath, $slug);
        }
    }

    private static function manifest(string $modulePath): ?array
    {
        $manifestFile = $modulePath
            . DIRECTORY_SEPARATOR
            . 'module.json';

        if (! File::exists($manifestFile)) {
            return null;
        }

        $manifest = json_decode(
            File::get($manifestFile),
            true
        );

        return is_array($manifest)
            ? $manifest
            : null;
    }

    private static function loadViews(
        string $modulePath,
        string $slug
    ): void
    {
        $views = $modulePath
            . DIRECTORY_SEPARATOR
            . 'Resources'
            . DIRECTORY_SEPARATOR
            . 'Views';

        if (! File::isDirectory($views)) {
            return;
        }

        View::addNamespace($slug, $views);
    }
}