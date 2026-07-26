<?php

declare(strict_types=1);

namespace Northpole\Runtime\Graph;

use InvalidArgumentException;

final class RuntimeGraph
{
    /**
     * @var array<string, RuntimeGraphNode>
     */
    private array $nodes = [];

    /**
     * @var array<string, RuntimeGraphEdge>
     */
    private array $edges = [];

    public function addNode(
        RuntimeGraphNode $node,
    ): self {
        $nodeId = trim($node->id);

        if (
            isset($this->nodes[$nodeId])
            && $this->nodes[$nodeId]->toArray() !== $node->toArray()
        ) {
            throw new InvalidArgumentException(
                "Runtime graph node [{$nodeId}] is already registered.",
            );
        }

        $this->nodes[$nodeId] = $node;

        return $this;
    }

    public function addEdge(
        RuntimeGraphEdge $edge,
    ): self {
        $edgeKey = $edge->key();

        if (
            isset($this->edges[$edgeKey])
            && $this->edges[$edgeKey]->toArray() !== $edge->toArray()
        ) {
            throw new InvalidArgumentException(
                "Runtime graph edge [{$edgeKey}] is already registered.",
            );
        }

        $this->edges[$edgeKey] = $edge;

        return $this;
    }

    /**
     * @return array<int, RuntimeGraphNode>
     */
    public function nodes(): array
    {
        $nodes = array_values(
            $this->nodes,
        );

        usort(
            $nodes,
            static fn (
                RuntimeGraphNode $left,
                RuntimeGraphNode $right,
            ): int => [
                $left->type,
                $left->module ?? '',
                $left->label,
                $left->id,
            ] <=> [
                $right->type,
                $right->module ?? '',
                $right->label,
                $right->id,
            ],
        );

        return $nodes;
    }

    /**
     * @return array<int, RuntimeGraphEdge>
     */
    public function edges(): array
    {
        $edges = array_values(
            $this->edges,
        );

        usort(
            $edges,
            static fn (
                RuntimeGraphEdge $left,
                RuntimeGraphEdge $right,
            ): int => [
                $left->source,
                $left->type,
                $left->target,
            ] <=> [
                $right->source,
                $right->type,
                $right->target,
            ],
        );

        return $edges;
    }

    public function node(
        string $id,
    ): ?RuntimeGraphNode {
        $normalisedId = trim($id);

        if ($normalisedId === '') {
            return null;
        }

        return $this->nodes[$normalisedId] ?? null;
    }

    public function hasNode(
        string $id,
    ): bool {
        return $this->node($id) instanceof RuntimeGraphNode;
    }

    /**
     * @return array{
     *     nodes: array<int, array<string, mixed>>,
     *     edges: array<int, array<string, mixed>>,
     *     summary: array{
     *         nodes: int,
     *         edges: int,
     *         node_types: array<string, int>,
     *         edge_types: array<string, int>
     *     }
     * }
     */
    public function toArray(): array
    {
        $nodes = array_map(
            static fn (RuntimeGraphNode $node): array => $node->toArray(),
            $this->nodes(),
        );

        $edges = array_map(
            static fn (RuntimeGraphEdge $edge): array => $edge->toArray(),
            $this->edges(),
        );

        return [
            'nodes' => $nodes,
            'edges' => $edges,
            'summary' => [
                'nodes' => count($nodes),
                'edges' => count($edges),
                'node_types' => $this->countNodeTypes(),
                'edge_types' => $this->countEdgeTypes(),
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function countNodeTypes(): array
    {
        $types = [];

        foreach ($this->nodes as $node) {
            $types[$node->type] =
                ($types[$node->type] ?? 0) + 1;
        }

        ksort($types);

        return $types;
    }

    /**
     * @return array<string, int>
     */
    private function countEdgeTypes(): array
    {
        $types = [];

        foreach ($this->edges as $edge) {
            $types[$edge->type] =
                ($types[$edge->type] ?? 0) + 1;
        }

        ksort($types);

        return $types;
    }
}
