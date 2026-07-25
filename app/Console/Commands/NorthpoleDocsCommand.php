<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Northpole\Runtime\Metadata\RuntimeMetadataService;
use Throwable;

final class NorthpoleDocsCommand extends Command
{
    protected $signature = 'northpole:docs
                            {--path= : Documentation output directory}';

    protected $description =
        'Generate Markdown documentation from live NorthPole runtime metadata';

    public function __construct(
        private readonly RuntimeMetadataService $metadata,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $path = $this->resolveOutputPath();

        try {
            File::ensureDirectoryExists($path);

            $documents = [
                'README.md' => $this->renderIndex(),
                'runtime.md' => $this->renderRuntime(),
                'modules.md' => $this->renderModules(),
                'commands.md' => $this->renderMapDocumentation(
                    '# NorthPole Commands',
                    'Commands registered by discovered NorthPole modules.',
                    'Command',
                    'Handler',
                    $this->metadata->commands(),
                ),
                'queries.md' => $this->renderMapDocumentation(
                    '# NorthPole Queries',
                    'Queries registered by discovered NorthPole modules.',
                    'Query',
                    'Handler',
                    $this->metadata->queries(),
                ),
                'events.md' => $this->renderEvents(),
                'permissions.md' => $this->renderListDocumentation(
                    '# NorthPole Permissions',
                    'Permissions contributed by discovered NorthPole modules.',
                    $this->metadata->permissions(),
                ),
                'capabilities.md' => $this->renderListDocumentation(
                    '# NorthPole Capabilities',
                    'Capabilities contributed by discovered NorthPole modules.',
                    $this->metadata->capabilities(),
                ),
            ];

            foreach ($documents as $filename => $contents) {
                File::put(
                    $path.DIRECTORY_SEPARATOR.$filename,
                    $contents,
                );
            }
        } catch (Throwable $exception) {
            $this->error(
                sprintf(
                    'NorthPole documentation could not be generated: %s',
                    $exception->getMessage(),
                ),
            );

            return self::FAILURE;
        }

        $this->info('NorthPole documentation generated.');
        $this->line('Path: '.$path);

        return self::SUCCESS;
    }

    private function resolveOutputPath(): string
    {
        $path = trim(
            (string) $this->option('path'),
        );

        if ($path === '') {
            return base_path('docs');
        }

        if ($this->isAbsolutePath($path)) {
            return rtrim(
                $path,
                DIRECTORY_SEPARATOR,
            );
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

    private function renderIndex(): string
    {
        return $this->markdown([
            '# NorthPole Runtime Documentation',
            '',
            'This documentation is generated from the live NorthPole runtime.',
            '',
            '## Documentation',
            '',
            '- [Runtime](runtime.md)',
            '- [Modules](modules.md)',
            '- [Commands](commands.md)',
            '- [Queries](queries.md)',
            '- [Events](events.md)',
            '- [Permissions](permissions.md)',
            '- [Capabilities](capabilities.md)',
            '',
            'Regenerate these files with:',
            '',
            '```bash',
            'php artisan northpole:docs',
            '```',
        ]);
    }

    private function renderRuntime(): string
    {
        $summary = $this->metadata->summary();

        $modules = $summary['modules'];

        return $this->markdown([
            '# NorthPole Runtime',
            '',
            'Live runtime summary generated from registered metadata.',
            '',
            '## Health',
            '',
            '| Property | Value |',
            '| --- | ---: |',
            '| Status | '.$this->escapeCell(
                strtoupper((string) $summary['status']),
            ).' |',
            '| Modules | '.$modules['total'].' |',
            '| Enabled modules | '.$modules['enabled'].' |',
            '| Disabled modules | '.$modules['disabled'].' |',
            '| Commands | '.$summary['commands'].' |',
            '| Queries | '.$summary['queries'].' |',
            '| Published events | '.$summary['published_events'].' |',
            '| Event subscriptions | '.$summary['event_subscriptions'].' |',
            '| Permissions | '.$summary['permissions'].' |',
            '| Capabilities | '.$summary['capabilities'].' |',
            '| Navigation items | '.$summary['navigation_items'].' |',
            '| Notifications | '.$summary['notifications'].' |',
            '| Scheduled jobs | '.$summary['scheduled_jobs'].' |',
        ]);
    }

    private function renderModules(): string
    {
        $lines = [
            '# NorthPole Modules',
            '',
            'Modules discovered by the NorthPole runtime.',
        ];

        $modules = $this->metadata->modules();

        if ($modules === []) {
            $lines[] = '';
            $lines[] = 'No modules are currently discovered.';

            return $this->markdown($lines);
        }

        foreach ($modules as $module) {
            $lines[] = '';
            $lines[] = '## '.(string) $module['name'];
            $lines[] = '';
            $lines[] = '| Property | Value |';
            $lines[] = '| --- | --- |';
            $lines[] = '| Slug | `'.$this->escapeCode(
                (string) $module['slug'],
            ).'` |';
            $lines[] = '| Version | `'.$this->escapeCode(
                (string) $module['version'],
            ).'` |';
            $lines[] = '| Status | '.(
                (bool) $module['enabled']
                    ? 'Enabled'
                    : 'Disabled'
            ).' |';

            if (
                isset($module['description'])
                && $module['description'] !== null
                && $module['description'] !== ''
            ) {
                $lines[] = '| Description | '.$this->escapeCell(
                    (string) $module['description'],
                ).' |';
            }
        }

        return $this->markdown($lines);
    }

    /**
     * @param array<string, array<string, string>> $groups
     */
    private function renderMapDocumentation(
        string $title,
        string $description,
        string $keyHeading,
        string $valueHeading,
        array $groups,
    ): string {
        $lines = [
            $title,
            '',
            $description,
        ];

        if ($groups === []) {
            $lines[] = '';
            $lines[] = 'No entries are currently registered.';

            return $this->markdown($lines);
        }

        foreach ($groups as $module => $items) {
            $lines[] = '';
            $lines[] = '## '.$this->moduleHeading($module);
            $lines[] = '';
            $lines[] = '| '.$keyHeading.' | '.$valueHeading.' |';
            $lines[] = '| --- | --- |';

            foreach ($items as $key => $value) {
                $lines[] = '| `'.$this->escapeCode($key).'` | `'
                    .$this->escapeCode($value).'` |';
            }
        }

        return $this->markdown($lines);
    }

    /**
     * @param array<string, array<int, string>> $groups
     */
    private function renderListDocumentation(
        string $title,
        string $description,
        array $groups,
    ): string {
        $lines = [
            $title,
            '',
            $description,
        ];

        if ($groups === []) {
            $lines[] = '';
            $lines[] = 'No entries are currently registered.';

            return $this->markdown($lines);
        }

        foreach ($groups as $module => $items) {
            $lines[] = '';
            $lines[] = '## '.$this->moduleHeading($module);
            $lines[] = '';

            foreach ($items as $item) {
                $lines[] = '- `'.$this->escapeCode($item).'`';
            }
        }

        return $this->markdown($lines);
    }

    private function renderEvents(): string
    {
        $lines = [
            '# NorthPole Events',
            '',
            'Events published and subscribed to by discovered modules.',
        ];

        $events = $this->metadata->events();

        if ($events === []) {
            $lines[] = '';
            $lines[] = 'No events are currently registered.';

            return $this->markdown($lines);
        }

        foreach ($events as $module => $eventMetadata) {
            $lines[] = '';
            $lines[] = '## '.$this->moduleHeading($module);
            $lines[] = '';
            $lines[] = '### Published Events';
            $lines[] = '';

            $published = $eventMetadata['publishes'];

            if ($published === []) {
                $lines[] = 'None.';
            } else {
                foreach ($published as $event) {
                    $lines[] = '- `'.$this->escapeCode(
                        (string) $event,
                    ).'`';
                }
            }

            $lines[] = '';
            $lines[] = '### Subscriptions';
            $lines[] = '';

            $subscriptions = $eventMetadata['subscribes'];

            if ($subscriptions === []) {
                $lines[] = 'None.';

                continue;
            }

            foreach ($subscriptions as $event => $listeners) {
                $lines[] = '- `'.$this->escapeCode(
                    (string) $event,
                ).'`';

                foreach ($listeners as $listener) {
                    $lines[] = '  - `'.$this->escapeCode(
                        (string) $listener,
                    ).'`';
                }
            }
        }

        return $this->markdown($lines);
    }

    private function moduleHeading(string $slug): string
    {
        return ucwords(
            str_replace(
                ['-', '_'],
                ' ',
                $slug,
            ),
        );
    }

    /**
     * @param array<int, string> $lines
     */
    private function markdown(array $lines): string
    {
        return implode(
            PHP_EOL,
            $lines,
        ).PHP_EOL;
    }

    private function escapeCell(string $value): string
    {
        return str_replace(
            ['|', PHP_EOL],
            ['\|', ' '],
            $value,
        );
    }

    private function escapeCode(string $value): string
    {
        return str_replace(
            '`',
            '\`',
            $value,
        );
    }
}