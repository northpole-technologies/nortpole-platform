<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Inspection;

use Northpole\Runtime\Inspection\RuntimeInspectionReference;
use Northpole\Runtime\Inspection\RuntimeInspectionResult;
use PHPUnit\Framework\TestCase;

final class RuntimeInspectionResultTest extends TestCase
{
    public function test_it_exposes_reference_values(): void
    {
        $result = new RuntimeInspectionResult(
            reference: new RuntimeInspectionReference(
                registry: 'commands',
                module: 'crm',
                key: 'crm.customer.create',
            ),
            value: [
                'handler' => 'CreateCustomerHandler',
            ],
        );

        self::assertSame(
            'commands',
            $result->registry(),
        );

        self::assertSame(
            'crm',
            $result->module(),
        );

        self::assertSame(
            'crm.customer.create',
            $result->key(),
        );
    }

    public function test_it_serialises_to_an_array(): void
    {
        $result = new RuntimeInspectionResult(
            reference: new RuntimeInspectionReference(
                registry: 'commands',
                module: 'crm',
                key: 'crm.customer.create',
            ),
            value: 'CreateCustomerHandler',
        );

        self::assertSame(
            [
                'reference' => [
                    'registry' => 'commands',
                    'module' => 'crm',
                    'key' => 'crm.customer.create',
                ],
                'registry' => 'commands',
                'module' => 'crm',
                'key' => 'crm.customer.create',
                'value' => 'CreateCustomerHandler',
            ],
            $result->toArray(),
        );
    }
}
