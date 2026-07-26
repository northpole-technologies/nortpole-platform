<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Graph;

use InvalidArgumentException;
use Northpole\Runtime\Graph\RuntimeGraphEdge;
use PHPUnit\Framework\TestCase;

final class RuntimeGraphEdgeTest extends TestCase
{
    public function test_it_serialises_a_runtime_graph_edge(): void
    {
        $edge = new RuntimeGraphEdge(
            source: 'module:crm',
            target: 'command:crm:crm.customer.create',
            type: 'declares',
            label: 'Declares',
            metadata: [
                'registry' => 'commands',
            ],
        );

        $this->assertSame(
            [
                'source' => 'module:crm',
                'target' => 'command:crm:crm.customer.create',
                'type' => 'declares',
                'label' => 'Declares',
                'metadata' => [
                    'registry' => 'commands',
                ],
            ],
            $edge->toArray(),
        );
    }

    public function test_it_creates_a_stable_edge_key(): void
    {
        $edge = new RuntimeGraphEdge(
            source: 'module:crm',
            target: 'module:inventory',
            type: 'depends_on',
            label: 'Depends on',
        );

        $this->assertSame(
            'module:crm:depends_on:module:inventory',
            $edge->key(),
        );
    }

    public function test_it_rejects_an_empty_source(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        new RuntimeGraphEdge(
            source: ' ',
            target: 'module:inventory',
            type: 'depends_on',
            label: 'Depends on',
        );
    }

    public function test_it_rejects_an_empty_target(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        new RuntimeGraphEdge(
            source: 'module:crm',
            target: ' ',
            type: 'depends_on',
            label: 'Depends on',
        );
    }
}
