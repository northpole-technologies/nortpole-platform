<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Diagnostics;

use Northpole\Runtime\Diagnostics\RuntimeDiagnosticsService;
use Northpole\Runtime\Repair\RepairResult;
use Northpole\Runtime\Validation\ValidationResult;
use Tests\TestCase;

final class RuntimeDiagnosticsServiceTest extends TestCase
{
    public function test_it_assembles_runtime_diagnostics(): void
    {
        $diagnostics = app(
            RuntimeDiagnosticsService::class,
        )->inspect();

        self::assertIsObject(
            $diagnostics['runtimeHealth'],
        );

        self::assertTrue(
            method_exists(
                $diagnostics['runtimeHealth'],
                'status',
            ),
        );

        self::assertTrue(
            method_exists(
                $diagnostics['runtimeHealth'],
                'score',
            ),
        );

        self::assertTrue(
            method_exists(
                $diagnostics['runtimeHealth'],
                'modules',
            ),
        );

        self::assertInstanceOf(
            ValidationResult::class,
            $diagnostics['validationResult'],
        );

        self::assertInstanceOf(
            RepairResult::class,
            $diagnostics['repairResult'],
        );

        self::assertArrayHasKey(
            'health',
            $diagnostics,
        );

        self::assertArrayHasKey(
            'validation',
            $diagnostics,
        );

        self::assertArrayHasKey(
            'repairs',
            $diagnostics,
        );
    }

    public function test_it_returns_a_machine_readable_payload(): void
    {
        $payload = app(
            RuntimeDiagnosticsService::class,
        )->toArray();

        self::assertSame(
            [
                'health',
                'validation',
                'repairs',
            ],
            array_keys($payload),
        );

        self::assertSame(
            count($payload['validation']['results']),
            $payload['validation']['issues'],
        );

        self::assertSame(
            count($payload['repairs']['results']),
            $payload['repairs']['recommendations'],
        );
    }
}