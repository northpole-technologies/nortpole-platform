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

    public function test_it_returns_a_versioned_response_payload(): void
    {
        $payload = app(
            RuntimeDiagnosticsService::class,
        )->toResponseArray();

        self::assertSame(
            [
                'schema_version',
                'generated_at',
                'runtime',
                'data',
            ],
            array_keys($payload),
        );

        self::assertSame(
            RuntimeDiagnosticsService::SCHEMA_VERSION,
            $payload['schema_version'],
        );

        self::assertIsString(
            $payload['generated_at'],
        );

        self::assertNotFalse(
            strtotime($payload['generated_at']),
        );

        self::assertSame(
            [
                'environment',
                'php_version',
                'framework_version',
            ],
            array_keys($payload['runtime']),
        );

        self::assertSame(
            app()->environment(),
            $payload['runtime']['environment'],
        );

        self::assertSame(
            PHP_VERSION,
            $payload['runtime']['php_version'],
        );

        self::assertSame(
            app()->version(),
            $payload['runtime']['framework_version'],
        );

        self::assertSame(
            [
                'health',
                'validation',
                'repairs',
            ],
            array_keys($payload['data']),
        );
    }
}
