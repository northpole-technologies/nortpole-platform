<?php

declare(strict_types=1);

namespace Northpole\Runtime\Health;

use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Runtime;

final readonly class RuntimeHealthService
{
    public function __construct(
        private Runtime $runtime,
    ) {}

    public function report(): RuntimeHealth
    {
        return $this->assess(
            $this->runtime->modules(),
        );
    }

    /**
     * @param array<string, ModuleManifest> $modules
     */
    public function assess(array $modules): RuntimeHealth
    {
        $moduleHealth = [];

        foreach ($modules as $module) {
            $moduleHealth[] = $this->assessModule(
                $module,
                $modules,
            );
        }

        usort(
            $moduleHealth,
            static fn (
                ModuleHealth $left,
                ModuleHealth $right,
            ): int => $left->slug() <=> $right->slug(),
        );

        $score = $this->runtimeScore(
            $moduleHealth,
        );

        return new RuntimeHealth(
            status: $this->statusForScore($score),
            score: $score,
            modules: $moduleHealth,
        );
    }

    /**
     * @param array<string, ModuleManifest> $modules
     */
    private function assessModule(
        ModuleManifest $module,
        array $modules,
    ): ModuleHealth {
        $dependencies = $module->dependencies();

        $missingDependencies = array_values(
            array_filter(
                $dependencies,
                static fn (string $dependency): bool =>
                    ! array_key_exists($dependency, $modules),
            ),
        );

        $checks = [
            [
                'key' => 'manifest',
                'label' => 'Manifest valid',
                'healthy' => true,
                'message' => null,
            ],
            [
                'key' => 'module_path',
                'label' => 'Module path available',
                'healthy' => is_dir($module->path()),
                'message' => is_dir($module->path())
                    ? null
                    : sprintf(
                        'Module path [%s] does not exist.',
                        $module->path(),
                    ),
            ],
            [
                'key' => 'enabled',
                'label' => 'Module enabled',
                'healthy' => $module->enabled(),
                'message' => $module->enabled()
                    ? null
                    : 'Module is currently disabled.',
            ],
            [
                'key' => 'dependencies',
                'label' => 'Dependencies available',
                'healthy' => $missingDependencies === [],
                'message' => $missingDependencies === []
                    ? null
                    : sprintf(
                        'Missing dependencies: %s.',
                        implode(', ', $missingDependencies),
                    ),
            ],
        ];

        $score = $this->checksScore(
            $checks,
        );

        return new ModuleHealth(
            slug: $module->slug(),
            name: $module->name(),
            status: $this->statusForScore($score),
            score: $score,
            checks: $checks,
        );
    }

    /**
     * @param array<int, array{
     *     key: string,
     *     label: string,
     *     healthy: bool,
     *     message: string|null
     * }> $checks
     */
    private function checksScore(array $checks): int
    {
        if ($checks === []) {
            return 100;
        }

        $healthyChecks = count(
            array_filter(
                $checks,
                static fn (array $check): bool =>
                    $check['healthy'],
            ),
        );

        return (int) round(
            ($healthyChecks / count($checks)) * 100,
        );
    }

    /**
     * @param array<int, ModuleHealth> $modules
     */
    private function runtimeScore(array $modules): int
    {
        if ($modules === []) {
            return 100;
        }

        $total = array_reduce(
            $modules,
            static fn (
                int $score,
                ModuleHealth $module,
            ): int => $score + $module->score(),
            0,
        );

        return (int) round(
            $total / count($modules),
        );
    }

    private function statusForScore(int $score): string
    {
        return match (true) {
            $score === 100 => 'healthy',
            $score >= 75 => 'degraded',
            default => 'unhealthy',
        };
    }
}