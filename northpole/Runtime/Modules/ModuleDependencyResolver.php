<?php

declare(strict_types=1);

namespace Northpole\Runtime\Modules;

use Northpole\Runtime\Exceptions\ModuleDependencyException;
use Northpole\Runtime\Manifest\ModuleManifest;

final class ModuleDependencyResolver
{
    /**
     * @param array<string, ModuleManifest> $modules
     * @return array<string, ModuleManifest>
     */
    public function resolve(array $modules): array
    {
        $enabledModules = array_filter(
            $modules,
            static fn (ModuleManifest $module): bool => $module->enabled(),
        );

        $this->validateDependencies(
            $modules,
            $enabledModules,
        );

        $resolved = [];
        $visited = [];
        $visiting = [];

        foreach ($enabledModules as $slug => $module) {
            $this->visit(
                slug: $slug,
                modules: $enabledModules,
                resolved: $resolved,
                visited: $visited,
                visiting: $visiting,
                path: [],
            );
        }

        return $resolved;
    }

    /**
     * @param array<string, ModuleManifest> $allModules
     * @param array<string, ModuleManifest> $enabledModules
     */
    private function validateDependencies(
        array $allModules,
        array $enabledModules,
    ): void {
        foreach ($enabledModules as $module) {
            foreach ($module->dependencies() as $dependency) {
                if (! isset($allModules[$dependency])) {
                    throw ModuleDependencyException::missing(
                        $module->slug(),
                        $dependency,
                    );
                }

                if (! isset($enabledModules[$dependency])) {
                    throw ModuleDependencyException::disabled(
                        $module->slug(),
                        $dependency,
                    );
                }
            }
        }
    }

    /**
     * @param array<string, ModuleManifest> $modules
     * @param array<string, ModuleManifest> $resolved
     * @param array<string, bool> $visited
     * @param array<string, bool> $visiting
     * @param array<int, string> $path
     */
    private function visit(
        string $slug,
        array $modules,
        array &$resolved,
        array &$visited,
        array &$visiting,
        array $path,
    ): void {
        if (isset($visited[$slug])) {
            return;
        }

        if (isset($visiting[$slug])) {
            $cycleStart = array_search(
                $slug,
                $path,
                true,
            );

            $cycle = $cycleStart === false
                ? [...$path, $slug]
                : [
                    ...array_slice($path, $cycleStart),
                    $slug,
                ];

            throw ModuleDependencyException::circular(
                $cycle,
            );
        }

        $visiting[$slug] = true;
        $path[] = $slug;

        foreach ($modules[$slug]->dependencies() as $dependency) {
            $this->visit(
                slug: $dependency,
                modules: $modules,
                resolved: $resolved,
                visited: $visited,
                visiting: $visiting,
                path: $path,
            );
        }

        unset($visiting[$slug]);

        $visited[$slug] = true;
        $resolved[$slug] = $modules[$slug];
    }
}