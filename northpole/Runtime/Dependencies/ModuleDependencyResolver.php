<?php

namespace Northpole\Runtime\Dependencies;

use Northpole\Runtime\Exceptions\ModuleDependencyException;
use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Modules\ModuleRepository;

final class ModuleDependencyResolver
{
    public function __construct(
        private readonly ModuleRepository $repository
    ) {
    }

    /**
     * Resolve all enabled modules into dependency-safe boot order.
     *
     * @return array<string, ModuleManifest>
     */
    public function resolve(): array
    {
        $enabledModules = $this->repository->enabled();

        ksort($enabledModules);

        $resolved = [];
        $visiting = [];
        $visited = [];

        foreach ($enabledModules as $module) {
            $this->visit(
                $module,
                $resolved,
                $visiting,
                $visited,
                []
            );
        }

        return $resolved;
    }

    /**
     * @param array<string, ModuleManifest> $resolved
     * @param array<string, bool> $visiting
     * @param array<string, bool> $visited
     * @param array<int, string> $path
     */
    private function visit(
        ModuleManifest $module,
        array &$resolved,
        array &$visiting,
        array &$visited,
        array $path
    ): void {
        $slug = $module->slug();

        if (isset($visited[$slug])) {
            return;
        }

        if (isset($visiting[$slug])) {
            $cycleStart = array_search(
                $slug,
                $path,
                true
            );

            $cycle = $cycleStart === false
                ? [...$path, $slug]
                : [
                    ...array_slice($path, $cycleStart),
                    $slug,
                ];

            throw ModuleDependencyException::circular($cycle);
        }

        $visiting[$slug] = true;
        $path[] = $slug;

        $dependencies = $module->dependencies();

        sort($dependencies);

        foreach ($dependencies as $dependencySlug) {
            if ($dependencySlug === $slug) {
                throw ModuleDependencyException::selfReference($slug);
            }

            $dependency = $this->repository->get(
                $dependencySlug
            );

            if ($dependency === null) {
                throw ModuleDependencyException::missing(
                    $slug,
                    $dependencySlug
                );
            }

            if (! $dependency->enabled()) {
                throw ModuleDependencyException::disabled(
                    $slug,
                    $dependencySlug
                );
            }

            $this->visit(
                $dependency,
                $resolved,
                $visiting,
                $visited,
                $path
            );
        }

        unset($visiting[$slug]);

        $visited[$slug] = true;
        $resolved[$slug] = $module;
    }
}