<?php

namespace Northpole\Runtime\Modules;

final class ModuleFinder
{
    /**
     * @return array<int, string>
     */
    public function find(string $modulesPath): array
    {
        if (! is_dir($modulesPath)) {
            return [];
        }

        $directories = glob(
            $modulesPath.DIRECTORY_SEPARATOR.'*',
            GLOB_ONLYDIR
        );

        if ($directories === false) {
            return [];
        }

        $modules = array_filter(
            $directories,
            static fn (string $directory): bool =>
                is_file(
                    $directory
                    .DIRECTORY_SEPARATOR
                    .'module.json'
                )
        );

        sort($modules);

        return array_values($modules);
    }
}