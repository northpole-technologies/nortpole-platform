<?php

declare(strict_types=1);

namespace Northpole\Runtime\Diagnostics;

use Northpole\Runtime\Health\ModuleHealth;
use Northpole\Runtime\Health\RuntimeHealthService;
use Northpole\Runtime\Repair\RepairResult;
use Northpole\Runtime\Repair\RuntimeRepairEngine;
use Northpole\Runtime\Validation\RuntimeValidationEngine;
use Northpole\Runtime\Validation\ValidationResult;

final class RuntimeDiagnosticsService
{
    public const SCHEMA_VERSION = '1.1';

    public function __construct(
        private readonly RuntimeHealthService $healthService,
        private readonly RuntimeValidationEngine $validationEngine,
        private readonly RuntimeRepairEngine $repairEngine,
        private readonly RuntimeRegistryStatisticsService $registryStatistics,
    ) {}

    /**
     * @return array{
     *     runtimeHealth: object,
     *     validationResult: ValidationResult,
     *     repairResult: RepairResult,
     *     health: array{
     *         status: string,
     *         score: int,
     *         modules: array<int, array<string, mixed>>
     *     },
     *     validation: array{
     *         status: string,
     *         rules: int,
     *         issues: int,
     *         errors: int,
     *         warnings: int,
     *         information: int,
     *         results: array<int, array<string, mixed>>
     *     },
     *     repairs: array{
     *         status: string,
     *         providers: int,
     *         recommendations: int,
     *         results: array<int, array<string, mixed>>
     *     }
     * }
     */
    public function inspect(): array
    {
        $runtimeHealth = $this->healthService->report();
        $validationResult = $this->validationEngine->validate();
        $repairResult = $this->repairEngine->recommend(
            $validationResult,
        );

        return [
            'runtimeHealth' => $runtimeHealth,
            'validationResult' => $validationResult,
            'repairResult' => $repairResult,
            'health' => [
                'status' => $runtimeHealth->status(),
                'score' => $runtimeHealth->score(),
                'modules' => array_map(
                    static fn (
                        ModuleHealth $module,
                    ): array => [
                        'slug' => $module->slug(),
                        'status' => $module->status(),
                        'score' => $module->score(),
                        'checks' => $module->checks(),
                    ],
                    $runtimeHealth->modules(),
                ),
            ],
            'validation' => [
                'status' => $validationResult->passes()
                    ? 'passed'
                    : 'failed',
                'rules' => $this->validationEngine->count(),
                'issues' => $validationResult->count(),
                'errors' => $validationResult->errorCount(),
                'warnings' => $validationResult->warningCount(),
                'information' => $validationResult->infoCount(),
                'results' => $validationResult->toArray(),
            ],
            'repairs' => [
                'status' => $repairResult->isEmpty()
                    ? 'clear'
                    : 'recommended',
                'providers' => $this->repairEngine
                    ->providerCount(),
                'recommendations' => $repairResult->count(),
                'results' => $repairResult->toArray(),
            ],
            'registries' => [
                'total' => $this->registryStatistics->total(),
                'counts' => $this->registryStatistics->counts(),
            ],
        ];
    }

    /**
     * @return array{
     *     health: array<string, mixed>,
     *     validation: array<string, mixed>,
     *     repairs: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        $diagnostics = $this->inspect();

        return [
            'health' => $diagnostics['health'],
            'validation' => $diagnostics['validation'],
            'repairs' => $diagnostics['repairs'],
            'registries' => $diagnostics['registries'],
        ];
    }

    /**
     * @return array{
     *     schema_version: string,
     *     generated_at: string,
     *     runtime: array{
     *         environment: string,
     *         php_version: string,
     *         framework_version: string
     *     },
     *     data: array{
     *         health: array<string, mixed>,
     *         validation: array<string, mixed>,
     *         repairs: array<string, mixed>
     *     }
     * }
     */
    public function toResponseArray(): array
    {
        return [
            'schema_version' => self::SCHEMA_VERSION,
            'generated_at' => now()->toIso8601String(),
            'runtime' => [
                'environment' => app()->environment(),
                'php_version' => PHP_VERSION,
                'framework_version' => app()->version(),
            ],
            'data' => $this->toArray(),
        ];
    }
}
