<?php

declare(strict_types=1);

namespace Northpole\Runtime\Diagnostics;

use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Runtime;

final class RuntimeModuleStatisticsService
{
    public function __construct(
        private readonly Runtime $runtime,
    ) {}

    /**
     * @return array<int, ModuleManifest>
     */
    public function modules(): array
    {
        $modules = $this->runtime->modules();

        usort(
            $modules,
            static fn (
                ModuleManifest $left,
                ModuleManifest $right,
            ): int => $left->name() <=> $right->name(),
        );

        return $modules;
    }

    /**
     * @return array{
     *     discovered: int,
     *     enabled: int,
     *     disabled: int
     * }
     */
    public function summary(): array
    {
        $modules = $this->modules();

        $enabled = count(
            array_filter(
                $modules,
                static fn (ModuleManifest $module): bool =>
                    $module->enabled(),
            ),
        );

        return [
            'discovered' => count($modules),
            'enabled' => $enabled,
            'disabled' => count($modules) - $enabled,
        ];
    }

    /**
     * @return array<int, array{
     *     name: string,
     *     slug: string,
     *     version: string,
     *     description: string,
     *     enabled: bool,
     *     dependencies: int,
     *     commands: int,
     *     queries: int,
     *     permissions: int
     * }>
     */
    public function dashboardRows(): array
    {
        return array_map(
            static fn (ModuleManifest $module): array => [
                'name' => $module->name(),
                'slug' => $module->slug(),
                'version' => $module->version(),
                'description' => $module->description(),
                'enabled' => $module->enabled(),
                'dependencies' => count(
                    $module->dependencyConstraints(),
                ),
                'commands' => count(
                    $module->handledCommands(),
                ),
                'queries' => count(
                    $module->handledQueries(),
                ),
                'permissions' => count(
                    $module->permissions(),
                ),
            ],
            $this->modules(),
        );
    }

    /**
     * @return array<int, array{
     *     0: string,
     *     1: string,
     *     2: string,
     *     3: string
     * }>
     */
    public function consoleRows(): array
    {
        return array_map(
            static fn (ModuleManifest $module): array => [
                $module->name(),
                $module->slug(),
                $module->version(),
                $module->enabled()
                    ? 'Enabled'
                    : 'Disabled',
            ],
            $this->modules(),
        );
    }
}