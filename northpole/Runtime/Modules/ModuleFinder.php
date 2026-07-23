<?php

namespace Northpole\Runtime\Modules;

use Illuminate\Support\Facades\File;

final class ModuleFinder
{
    public function find(string $modulesPath): array
    {
        if (! File::exists($modulesPath)) {
            return [];
        }

        return array_values(
            array_filter(
                File::directories($modulesPath),
                fn (string $directory): bool =>
                    File::exists($directory . DIRECTORY_SEPARATOR . 'module.json')
            )
        );
    }
}