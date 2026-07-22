<?php

namespace Northpole\Modules;

use Illuminate\Support\Facades\File;
use RuntimeException;

class ModuleManager
{
    protected array $modules = [];

    public function discover(): array
    {
        $this->modules = [];

        $path = base_path('modules');

        if (! File::isDirectory($path)) {
            return [];
        }

        foreach (File::directories($path) as $directory) {
            $manifestPath = $directory . DIRECTORY_SEPARATOR . 'module.json';

            if (! File::isFile($manifestPath)) {
                continue;
            }

            $module = json_decode(
                File::get($manifestPath),
                true
            );

            if (! is_array($module)) {
                throw new RuntimeException(
                    "Invalid module manifest: {$manifestPath}"
                );
            }

            if (($module['enabled'] ?? false) !== true) {
                continue;
            }

            if (empty($module['name'])) {
                throw new RuntimeException(
                    "Module manifest is missing a name: {$manifestPath}"
                );
            }

            if (empty($module['provider'])) {
                throw new RuntimeException(
                    "Module manifest is missing a provider: {$manifestPath}"
                );
            }

            $module['path'] = $directory;
            $module['manifest_path'] = $manifestPath;

            $this->modules[] = $module;
        }

        return $this->modules;
    }

    public function all(): array
    {
        return $this->modules;
    }

    public function providers(): array
    {
        return collect($this->modules)
            ->pluck('provider')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}