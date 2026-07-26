<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Graph;

use InvalidArgumentException;
use Northpole\Runtime\Graph\RuntimeGraphNode;
use PHPUnit\Framework\TestCase;

final class RuntimeGraphNodeTest extends TestCase
{
    public function test_it_serialises_a_runtime_graph_node(): void
    {
        $node = new RuntimeGraphNode(
            id: 'module:crm',
            type: 'module',
            label: 'CRM',
            module: 'crm',
            metadata: [
                'enabled' => true,
            ],
        );

        $this->assertSame(
            [
                'id' => 'module:crm',
                'type' => 'module',
                'label' => 'CRM',
                'module' => 'crm',
                'metadata' => [
                    'enabled' => true,
                ],
            ],
            $node->toArray(),
        );
    }

    public function test_it_rejects_an_empty_id(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        new RuntimeGraphNode(
            id: ' ',
            type: 'module',
            label: 'CRM',
        );
    }

    public function test_it_rejects_an_empty_type(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        new RuntimeGraphNode(
            id: 'module:crm',
            type: ' ',
            label: 'CRM',
        );
    }

    public function test_it_rejects_an_empty_label(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        new RuntimeGraphNode(
            id: 'module:crm',
            type: 'module',
            label: ' ',
        );
    }
}
