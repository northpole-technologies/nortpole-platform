<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Northpole\Runtime\Metadata\RuntimeMetadataService;
use Throwable;

final class NorthpoleGraphCommand extends Command
{
    protected $signature = 'northpole:graph
                            {--path=docs/runtime-graph.md : Graph output path}';

    protected $description =
        'Generate a Mermaid diagram from live NorthPole runtime metadata';

    public function __construct(
        private readonly RuntimeMetadataService $metadata,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $path = $this->resolveOutputPath();

        try {
            File::ensureDirectoryExists(
                dirname($path),
            );

            File::put(
                $path,
                $this->renderGraph(),
            );
        } catch (Throwable $exception) {
            $this->error(
                sprintf(
                    'NorthPole runtime graph could not be generated: %s',
                    $exception->getMessage(),
                ),
            );

            return self::FAILURE;
        }

        $this->info('NorthPole runtime graph generated.');
        $this->line('Path: '.$path);

        return self::SUCCESS;
    }

    private function resolveOutputPath(): string
    {
        $path = trim(
            (string) $this->option('path'),
        );

        if ($path === '') {
            return base_path(
                'docs/runtime-graph.md',
            );
        }

        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return base_path(
            trim(
                $path,
                '/\\',
            ),
        );
    }

    private function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if (
            str_starts_with($path, '/')
            || str_starts_with($path, '\\')
        ) {
            return true;
        }

        return preg_match(
            '/^[A-Za-z]:[\/\\\\]/',
            $path,
        ) === 1;
    }

    private function renderGraph(): string
    {
        $lines = [
            '# NorthPole Runtime Graph',
            '',
            'Generated from the live NorthPole runtime metadata.',
            '',
            '```mermaid',
            'flowchart LR',
        ];

        $moduleNodes = [];

        foreach ($this->metadata->modules() as $module) {
            $slug = (string) $module['slug'];
            $name = (string) $module['name'];
            $moduleNode = $this->nodeId(
                'module_'.$slug,
            );

            $moduleNodes[$slug] = $moduleNode;

            $lines[] = sprintf(
                '    %s["%s"]',
                $moduleNode,
                $this->escapeLabel($name),
            );
        }

        $this->appendDependencies(
            $lines,
            $moduleNodes,
        );

        $this->appendCommandRelationships(
            $lines,
            $moduleNodes,
        );

        $this->appendQueryRelationships(
            $lines,
            $moduleNodes,
        );

        $this->appendEventRelationships(
            $lines,
            $moduleNodes,
        );

        $lines[] = '```';
        $lines[] = '';

        return implode(
            PHP_EOL,
            $lines,
        );
    }

    /**
     * @param array<int, string> $lines
     * @param array<string, string> $moduleNodes
     */
    private function appendDependencies(
        array &$lines,
        array $moduleNodes,
    ): void {
        foreach ($this->metadata->modules() as $module) {
            $slug = (string) $module['slug'];
            $sourceNode = $moduleNodes[$slug] ?? null;

            if ($sourceNode === null) {
                continue;
            }

            $dependencies = $module['dependencies'] ?? [];

            foreach ($dependencies as $dependency => $constraint) {
                $dependencySlug = is_int($dependency)
                    ? (string) $constraint
                    : (string) $dependency;

                $dependencyNode = $moduleNodes[$dependencySlug] ?? null;

                if ($dependencyNode === null) {
                    continue;
                }

                $lines[] = sprintf(
                    '    %s -->|depends on| %s',
                    $sourceNode,
                    $dependencyNode,
                );
            }
        }
    }

    /**
     * @param array<int, string> $lines
     * @param array<string, string> $moduleNodes
     */
    private function appendCommandRelationships(
        array &$lines,
        array $moduleNodes,
    ): void {
        foreach ($this->metadata->commands() as $module => $commands) {
            $moduleNode = $moduleNodes[$module] ?? null;

            if ($moduleNode === null) {
                continue;
            }

            foreach ($commands as $command => $handler) {
                $commandNode = $this->nodeId(
                    'command_'.$command,
                );

                $handlerNode = $this->nodeId(
                    'command_handler_'.$handler,
                );

                $lines[] = sprintf(
                    '    %s(["%s"])',
                    $commandNode,
                    $this->escapeLabel($command),
                );

                $lines[] = sprintf(
                    '    %s["%s"]',
                    $handlerNode,
                    $this->escapeLabel(
                        $this->classBasename($handler),
                    ),
                );

                $lines[] = sprintf(
                    '    %s -->|command| %s',
                    $moduleNode,
                    $commandNode,
                );

                $lines[] = sprintf(
                    '    %s -->|handled by| %s',
                    $commandNode,
                    $handlerNode,
                );
            }
        }
    }

    /**
     * @param array<int, string> $lines
     * @param array<string, string> $moduleNodes
     */
    private function appendQueryRelationships(
        array &$lines,
        array $moduleNodes,
    ): void {
        foreach ($this->metadata->queries() as $module => $queries) {
            $moduleNode = $moduleNodes[$module] ?? null;

            if ($moduleNode === null) {
                continue;
            }

            foreach ($queries as $query => $handler) {
                $queryNode = $this->nodeId(
                    'query_'.$query,
                );

                $handlerNode = $this->nodeId(
                    'query_handler_'.$handler,
                );

                $lines[] = sprintf(
                    '    %s(["%s"])',
                    $queryNode,
                    $this->escapeLabel($query),
                );

                $lines[] = sprintf(
                    '    %s["%s"]',
                    $handlerNode,
                    $this->escapeLabel(
                        $this->classBasename($handler),
                    ),
                );

                $lines[] = sprintf(
                    '    %s -->|query| %s',
                    $moduleNode,
                    $queryNode,
                );

                $lines[] = sprintf(
                    '    %s -->|handled by| %s',
                    $queryNode,
                    $handlerNode,
                );
            }
        }
    }

    /**
     * @param array<int, string> $lines
     * @param array<string, string> $moduleNodes
     */
    private function appendEventRelationships(
        array &$lines,
        array $moduleNodes,
    ): void {
        foreach ($this->metadata->events() as $module => $events) {
            $moduleNode = $moduleNodes[$module] ?? null;

            if ($moduleNode === null) {
                continue;
            }

            foreach ($events['publishes'] ?? [] as $event) {
                $eventNode = $this->nodeId(
                    'event_'.$event,
                );

                $lines[] = sprintf(
                    '    %s{{"%s"}}',
                    $eventNode,
                    $this->escapeLabel(
                        (string) $event,
                    ),
                );

                $lines[] = sprintf(
                    '    %s -->|publishes| %s',
                    $moduleNode,
                    $eventNode,
                );
            }

            foreach ($events['subscribes'] ?? [] as $event => $listeners) {
                $eventNode = $this->nodeId(
                    'event_'.$event,
                );

                $lines[] = sprintf(
                    '    %s{{"%s"}}',
                    $eventNode,
                    $this->escapeLabel(
                        (string) $event,
                    ),
                );

                foreach ($listeners as $listener) {
                    $listenerNode = $this->nodeId(
                        'listener_'.$listener,
                    );

                    $lines[] = sprintf(
                        '    %s["%s"]',
                        $listenerNode,
                        $this->escapeLabel(
                            $this->classBasename(
                                (string) $listener,
                            ),
                        ),
                    );

                    $lines[] = sprintf(
                        '    %s -->|subscribed by| %s',
                        $eventNode,
                        $listenerNode,
                    );

                    $lines[] = sprintf(
                        '    %s -->|listener| %s',
                        $moduleNode,
                        $listenerNode,
                    );
                }
            }
        }
    }

    private function nodeId(string $value): string
    {
        return 'node_'.substr(
            hash(
                'sha256',
                $value,
            ),
            0,
            16,
        );
    }

    private function classBasename(string $class): string
    {
        $position = strrpos(
            $class,
            '\\',
        );

        if ($position === false) {
            return $class;
        }

        return substr(
            $class,
            $position + 1,
        );
    }

    private function escapeLabel(string $value): string
    {
        return str_replace(
            ['"', PHP_EOL],
            ['\"', ' '],
            $value,
        );
    }
}