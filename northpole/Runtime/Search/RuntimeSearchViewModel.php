<?php

declare(strict_types=1);

namespace Northpole\Runtime\Search;

use Northpole\Runtime\Metadata\RuntimeMetadataService;

final class RuntimeSearchViewModel
{
    public function __construct(
        private readonly RuntimeSearchService $search,
        private readonly RuntimeMetadataService $metadata,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function data(
        string $query,
        ?string $module = null,
    ): array {
        $query = trim($query);
        $module = $this->normaliseModule($module);

        $matches = $this->search->search(
            $query,
            $module,
        );

        return [
            'query' => $query,
            'selectedModule' => $module,
            'moduleOptions' => $this->moduleOptions(),
            'matches' => $matches,
            'groups' => $this->groupMatches($matches),
            'summary' => [
                'matches' => count($matches),
                'modules' => count(
                    array_unique(
                        array_column(
                            $matches,
                            'module',
                        ),
                    ),
                ),
                'registries' => count(
                    array_unique(
                        array_column(
                            $matches,
                            'registry',
                        ),
                    ),
                ),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function moduleOptions(): array
    {
        $options = [];

        foreach ($this->metadata->modules() as $module) {
            $slug = $module['slug'] ?? null;
            $name = $module['name'] ?? null;

            if (
                ! is_string($slug)
                || ! is_string($name)
            ) {
                continue;
            }

            $options[$slug] = $name;
        }

        asort($options);

        return $options;
    }

    /**
     * @param array<int, array{
     *     registry: string,
     *     module: string,
     *     key: string,
     *     value: mixed
     * }> $matches
     *
     * @return array<string, array<string, array<int, array{
     *     registry: string,
     *     module: string,
     *     key: string,
     *     value: mixed
     * }>>>
     */
    private function groupMatches(
        array $matches,
    ): array {
        $groups = [];

        foreach ($matches as $match) {
            $groups[$match['module']]
                [$match['registry']][] = $match;
        }

        ksort($groups);

        foreach ($groups as &$registries) {
            ksort($registries);
        }

        unset($registries);

        return $groups;
    }

    private function normaliseModule(
        ?string $module,
    ): ?string {
        if ($module === null) {
            return null;
        }

        $module = trim($module);

        return $module === ''
            ? null
            : strtolower($module);
    }
}