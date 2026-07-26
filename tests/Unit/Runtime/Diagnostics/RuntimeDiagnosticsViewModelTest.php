<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Diagnostics;

use Northpole\Runtime\Diagnostics\RuntimeDiagnosticsViewModel;
use Tests\TestCase;

final class RuntimeDiagnosticsViewModelTest extends TestCase
{
    public function test_it_builds_runtime_diagnostics_data(): void
    {
        $data = app(
            RuntimeDiagnosticsViewModel::class,
        )->data();

        self::assertSame(
            [
                'runtimeHealth',
                'validationResult',
                'validationIssues',
                'repairResult',
                'repairRecommendations',
                'repairSummary',
                'validationSummary',
                'summary',
                'registryCounts',
                'moduleIssues',
            ],
            array_keys($data),
        );

        self::assertSame(
            $data['validationResult']->toArray(),
            $data['validationIssues'],
        );

        self::assertSame(
            $data['repairResult']->toArray(),
            $data['repairRecommendations'],
        );

        self::assertSame(
            [
                'status',
                'recommendations',
                'providers',
            ],
            array_keys(
                $data['repairSummary'],
            ),
        );

        self::assertContains(
            $data['repairSummary']['status'],
            [
                'clear',
                'recommended',
            ],
        );

        self::assertSame(
            $data['repairResult']->count(),
            $data['repairSummary']['recommendations'],
        );

        self::assertIsInt(
            $data['repairSummary']['providers'],
        );

        self::assertSame(
            [
                'status',
                'rules',
                'issues',
                'errors',
                'warnings',
                'information',
            ],
            array_keys(
                $data['validationSummary'],
            ),
        );

        self::assertContains(
            $data['validationSummary']['status'],
            [
                'passed',
                'failed',
            ],
        );

        self::assertSame(
            $data['validationResult']->count(),
            $data['validationSummary']['issues'],
        );

        self::assertSame(
            $data['validationResult']->errorCount(),
            $data['validationSummary']['errors'],
        );

        self::assertSame(
            $data['validationResult']->warningCount(),
            $data['validationSummary']['warnings'],
        );

        self::assertSame(
            $data['validationResult']->infoCount(),
            $data['validationSummary']['information'],
        );

        self::assertSame(
            [
                'status',
                'score',
                'modules',
                'healthyModules',
                'issues',
                'bootStages',
            ],
            array_keys(
                $data['summary'],
            ),
        );

        self::assertSame(
            $data['runtimeHealth']->status(),
            $data['summary']['status'],
        );

        self::assertSame(
            $data['runtimeHealth']->score(),
            $data['summary']['score'],
        );

        self::assertSame(
            count(
                $data['runtimeHealth']->modules(),
            ),
            $data['summary']['modules'],
        );

        self::assertIsInt(
            $data['summary']['healthyModules'],
        );

        self::assertIsInt(
            $data['summary']['issues'],
        );

        self::assertIsInt(
            $data['summary']['bootStages'],
        );

        self::assertIsArray(
            $data['registryCounts'],
        );

        self::assertIsArray(
            $data['moduleIssues'],
        );

        foreach ($data['moduleIssues'] as $issue) {
            self::assertSame(
                [
                    'module',
                    'check',
                    'message',
                ],
                array_keys($issue),
            );
        }
    }
}