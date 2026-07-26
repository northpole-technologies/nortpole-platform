<?php

declare(strict_types=1);

namespace Northpole\Runtime\Graph;

use Northpole\Runtime\Graph\Contracts\RuntimeGraphRendererContract;

final class RuntimeGraphMermaidRenderer implements RuntimeGraphRendererContract
{
    public function render(
        RuntimeGraph $graph,
    ): string {
        $lines = [
            '# NorthPole Runtime Graph',
            '',
            'Generated from the live NorthPole typed runtime graph.',
            '',
            '```mermaid',
            'flowchart LR',
        ];

        foreach ($graph->nodes() as $node) {
            $lines[] = $this->renderNode(
                $node,
            );
        }

        foreach ($graph->edges() as $edge) {
            $lines[] = $this->renderEdge(
                $edge,
            );
        }

        $lines[] = '```';
        $lines[] = '';

        return implode(
            PHP_EOL,
            $lines,
        );
    }

    private function renderNode(
        RuntimeGraphNode $node,
    ): string {
        $nodeId = $this->nodeId(
            $node->id,
        );

        $label = $this->escapeLabel(
            $node->label,
        );

        return match ($node->type) {
            'command',
            'query',
            'agent' => sprintf(
                '    %s(["%s"])',
                $nodeId,
                $label,
            ),

            'event' => sprintf(
                '    %s{{"%s"}}',
                $nodeId,
                $label,
            ),

            default => sprintf(
                '    %s["%s"]',
                $nodeId,
                $label,
            ),
        };
    }

    private function renderEdge(
        RuntimeGraphEdge $edge,
    ): string {
        return sprintf(
            '    %s -->|%s| %s',
            $this->nodeId(
                $edge->source,
            ),
            $this->escapeEdgeLabel(
                $edge->label,
            ),
            $this->nodeId(
                $edge->target,
            ),
        );
    }

    private function nodeId(
        string $value,
    ): string {
        return 'node_'.substr(
            hash(
                'sha256',
                trim($value),
            ),
            0,
            16,
        );
    }

    private function escapeLabel(
        string $value,
    ): string {
        return str_replace(
            [
                '"',
                "\r",
                "\n",
            ],
            [
                '\"',
                ' ',
                ' ',
            ],
            trim($value),
        );
    }

    private function escapeEdgeLabel(
        string $value,
    ): string {
        return str_replace(
            [
                '|',
                "\r",
                "\n",
            ],
            [
                '/',
                ' ',
                ' ',
            ],
            trim($value),
        );
    }
}
