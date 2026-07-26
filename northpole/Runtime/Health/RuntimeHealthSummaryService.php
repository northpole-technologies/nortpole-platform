<?php

declare(strict_types=1);

namespace Northpole\Runtime\Health;

final class RuntimeHealthSummaryService
{
    public function __construct(
        private readonly RuntimeHealthService $runtimeHealthService,
    ) {}

    /**
     * @return array{
     *     runtimeHealth: mixed,
     *     moduleHealthBySlug: array<string, ModuleHealth>,
     *     healthyModules: int,
     *     issues: array<int, array{
     *         module: string,
     *         check: string,
     *         message: string
     *     }>,
     *     issueCount: int
     * }
     */
    public function inspect(): array
    {
        $runtimeHealth = $this->runtimeHealthService->report();

        $moduleHealthBySlug = [];
        $issues = [];
        $healthyModules = 0;

        foreach ($runtimeHealth->modules() as $moduleHealth) {
            $moduleHealthBySlug[$moduleHealth->slug()] =
                $moduleHealth;

            if ($moduleHealth->status() === 'healthy') {
                $healthyModules++;
            }

            foreach ($moduleHealth->checks() as $check) {
                if ($check['healthy']) {
                    continue;
                }

                $issues[] = [
                    'module' => $moduleHealth->slug(),
                    'check' => $check['name']
                        ?? 'Runtime check',
                    'message' => $check['message']
                        ?? 'The runtime check did not pass.',
                ];
            }
        }

        return [
            'runtimeHealth' => $runtimeHealth,
            'moduleHealthBySlug' => $moduleHealthBySlug,
            'healthyModules' => $healthyModules,
            'issues' => $issues,
            'issueCount' => count($issues),
        ];
    }
}