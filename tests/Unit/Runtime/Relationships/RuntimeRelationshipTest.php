<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Relationships;

use InvalidArgumentException;
use Northpole\Runtime\Inspection\RuntimeInspectionReference;
use Northpole\Runtime\Relationships\RuntimeRelationship;
use PHPUnit\Framework\TestCase;

final class RuntimeRelationshipTest extends TestCase
{
    public function test_it_serialises_a_runtime_relationship(): void
    {
        $target = new RuntimeInspectionReference(
            registry: 'permissions',
            module: 'crm',
            key: 'crm.customers.view',
        );

        $relationship = new RuntimeRelationship(
            type: 'requires',
            label: 'Requires permission',
            value: 'crm.customers.view',
            target: $target,
            metadata: [
                'source' => 'manifest',
            ],
        );

        self::assertSame(
            [
                'type' => 'requires',
                'label' => 'Requires permission',
                'value' => 'crm.customers.view',
                'target' => [
                    'registry' => 'permissions',
                    'module' => 'crm',
                    'key' => 'crm.customers.view',
                ],
                'metadata' => [
                    'source' => 'manifest',
                ],
            ],
            $relationship->toArray(),
        );
    }

    public function test_it_rejects_an_empty_type(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        new RuntimeRelationship(
            type: ' ',
            label: 'Example',
            value: 'example',
        );
    }

    public function test_it_rejects_an_empty_label(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        new RuntimeRelationship(
            type: 'example',
            label: ' ',
            value: 'example',
        );
    }

    public function test_it_rejects_an_empty_value(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        new RuntimeRelationship(
            type: 'example',
            label: 'Example',
            value: ' ',
        );
    }
}
