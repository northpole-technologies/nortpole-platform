<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Inspector;

use Northpole\Runtime\Inspection\RuntimeInspectionReference;
use Northpole\Runtime\Inspection\RuntimeInspectionResult;
use Northpole\Runtime\Inspector\RuntimeInspectorResult;
use PHPUnit\Framework\TestCase;

final class RuntimeInspectorResultTest extends TestCase
{
    public function test_it_exposes_an_inspected_registration(): void
    {
        $reference = new RuntimeInspectionReference(
            registry: 'commands',
            module: 'crm',
            key: 'crm.customer.create',
        );

        $inspection = new RuntimeInspectionResult(
            reference: $reference,
            value: 'ExampleHandler',
        );

        $result = new RuntimeInspectorResult(
            reference: $reference,
            inspection: $inspection,
            source: [
                'class' => 'ExampleHandler',
                'file' => '/runtime/ExampleHandler.php',
            ],
        );

        self::assertTrue(
            $result->found(),
        );

        self::assertSame(
            'ExampleHandler',
            $result->metadata(),
        );

        self::assertSame(
            [
                'class' => 'ExampleHandler',
                'file' => '/runtime/ExampleHandler.php',
            ],
            $result->source,
        );

        self::assertSame(
            [],
            $result->relationships,
        );

        self::assertSame(
            [],
            $result->dependencies,
        );

        self::assertSame(
            [],
            $result->warnings,
        );
    }

    public function test_it_represents_a_missing_registration(): void
    {
        $reference = new RuntimeInspectionReference(
            registry: 'commands',
            module: 'crm',
            key: 'crm.command.missing',
        );

        $result = new RuntimeInspectorResult(
            reference: $reference,
            inspection: null,
        );

        self::assertFalse(
            $result->found(),
        );

        self::assertNull(
            $result->metadata(),
        );

        self::assertSame(
            [
                'found' => false,
                'reference' => [
                    'registry' => 'commands',
                    'module' => 'crm',
                    'key' => 'crm.command.missing',
                ],
                'metadata' => null,
                'source' => [
                    'class' => null,
                    'file' => null,
                ],
                'relationships' => [],
                'dependencies' => [],
                'warnings' => [],
            ],
            $result->toArray(),
        );
    }
}
