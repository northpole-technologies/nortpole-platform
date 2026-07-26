<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Northpole\Runtime\Metadata\RuntimeMetadataService;

final class RuntimeGraphController extends Controller
{
    public function __construct(
        private readonly RuntimeMetadataService $metadata,
    ) {}

    public function __invoke(): View
    {
        $topology = $this->metadata->graph();

        $nodes = $this->moduleNodes(
            $topology['nodes'],
        );

        $edges = $this->dependencyEdges(
            $topology['edges'],
        );

        return view(
            'dashboard.graph',
            [
                'nodes' => $nodes,
                'edges' => $edges,
                'summary' => [
                    'modules' => count($nodes),
                    'dependencies' => count($edges),
                    'enabled' => count(
                        array_filter(
                            $nodes,
                            static fn (array $node): bool => $node['enabled'],
                        ),
                    ),
                ],
                'topologySummary' => $topology['summary'],
            ],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $topologyNodes
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     version: string,
     *     enabled: bool
     * }>
     */
    private function moduleNodes(
        array $topologyNodes,
    ): array {
        $nodes = [];

        foreach ($topologyNodes as $node) {
            if (($node['type'] ?? null) !== 'module') {
                continue;
            }

            $metadata = $node['metadata'] ?? [];

            if (
                ! is_array($metadata)
                || ($metadata['discovered'] ?? true) === false
            ) {
                continue;
            }

            $slug = $metadata['slug'] ?? null;
            $label = $node['label'] ?? null;

            if (
                ! is_string($slug)
                || trim($slug) === ''
                || ! is_string($label)
                || trim($label) === ''
            ) {
                continue;
            }

            $version = $metadata['version'] ?? 'unknown';

            $nodes[] = [
                'id' => trim($slug),
                'name' => trim($label),
                'version' => is_string($version)
                    ? trim($version)
                    : 'unknown',
                'enabled' => ($metadata['enabled'] ?? false) === true,
            ];
        }

        usort(
            $nodes,
            static fn (array $left, array $right): int => $left['id'] <=> $right['id'],
        );

        return $nodes;
    }

    /**
     * @param  array<int, array<string, mixed>>  $topologyEdges
     * @return array<int, array{
     *     source: string,
     *     target: string,
     *     type: string,
     *     constraint: string
     * }>
     */
    private function dependencyEdges(
        array $topologyEdges,
    ): array {
        $edges = [];

        foreach ($topologyEdges as $edge) {
            if (($edge['type'] ?? null) !== 'depends_on') {
                continue;
            }

            $source = $this->moduleSlug(
                $edge['source'] ?? null,
            );

            $target = $this->moduleSlug(
                $edge['target'] ?? null,
            );

            if (
                $source === null
                || $target === null
            ) {
                continue;
            }

            $metadata = $edge['metadata'] ?? [];
            $constraint = is_array($metadata)
                ? ($metadata['constraint'] ?? '*')
                : '*';

            $edges[] = [
                'source' => $source,
                'target' => $target,
                'type' => 'depends_on',
                'constraint' => is_string($constraint)
                    ? $constraint
                    : '*',
            ];
        }

        usort(
            $edges,
            static fn (array $left, array $right): int => [
                $left['source'],
                $left['target'],
            ] <=> [
                $right['source'],
                $right['target'],
            ],
        );

        return $edges;
    }

    private function moduleSlug(
        mixed $nodeId,
    ): ?string {
        if (
            ! is_string($nodeId)
            || ! str_starts_with(
                $nodeId,
                'module:',
            )
        ) {
            return null;
        }

        $slug = trim(
            substr(
                $nodeId,
                strlen('module:'),
            ),
        );

        return $slug === ''
            ? null
            : $slug;
    }
}
