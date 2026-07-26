<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Inspection;

use InvalidArgumentException;
use Northpole\Runtime\Inspection\RuntimeInspectionReference;
use PHPUnit\Framework\TestCase;

final class RuntimeInspectionReferenceTest extends TestCase
{
    public function test_it_normalises_reference_values(): void
    {
        $reference = new RuntimeInspectionReference(
            registry: ' Commands ',
            module: ' CRM ',
            key: ' crm.customer.create ',
        );

        self::assertSame(
            'commands',
            $reference->registry,
        );

        self::assertSame(
            'crm',
            $reference->module,
        );

        self::assertSame(
            'crm.customer.create',
            $reference->key,
        );
    }

    public function test_it_creates_a_stable_identifier(): void
    {
        $reference = new RuntimeInspectionReference(
            registry: 'commands',
            module: 'crm',
            key: 'crm.customer.create',
        );

        self::assertSame(
            'commands:crm:crm.customer.create',
            $reference->identifier(),
        );
    }

    public function test_it_serialises_to_an_array(): void
    {
        $reference = new RuntimeInspectionReference(
            registry: 'queries',
            module: 'crm',
            key: 'crm.customers.list',
        );

        self::assertSame(
            [
                'registry' => 'queries',
                'module' => 'crm',
                'key' => 'crm.customers.list',
            ],
            $reference->toArray(),
        );
    }

    public function test_it_rejects_an_empty_registry(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        new RuntimeInspectionReference(
            registry: '',
            module: 'crm',
            key: 'crm.customer.create',
        );
    }

    public function test_it_rejects_an_empty_module(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        new RuntimeInspectionReference(
            registry: 'commands',
            module: '',
            key: 'crm.customer.create',
        );
    }

    public function test_it_rejects_an_empty_key(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        new RuntimeInspectionReference(
            registry: 'commands',
            module: 'crm',
            key: '',
        );
    }
}
