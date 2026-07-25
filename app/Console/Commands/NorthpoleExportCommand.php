<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Northpole\Runtime\Metadata\RuntimeMetadataService;
use Throwable;

final class NorthpoleExportCommand extends Command
{
    protected $signature = 'northpole:export
                            {--path=docs/runtime-export.json : Export output path}';

    protected $description =
        'Export live NorthPole runtime metadata as machine-readable JSON';

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
                $this->encodeExport(),
            );
        } catch (Throwable $exception) {
            $this->error(
                sprintf(
                    'NorthPole runtime export could not be generated: %s',
                    $exception->getMessage(),
                ),
            );

            return self::FAILURE;
        }

        $this->info('NorthPole runtime export generated.');
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
                'docs/runtime-export.json',
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

    /**
     * @return array<string, mixed>
     */
    private function roles(): array
    {
        $roles = [];

        foreach ($this->metadata->modules() as $module) {
            $slug = trim(
                (string) ($module['slug'] ?? ''),
            );

            $definitions = $module['roles'] ?? [];

            if (
                $slug === ''
                || ! is_array($definitions)
                || $definitions === []
            ) {
                continue;
            }

            $roles[$slug] = $definitions;
        }

        ksort(
            $roles,
        );

        return $roles;
    }
    private function encodeExport(): string
    {
        return json_encode(
            $this->exportData(),
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_THROW_ON_ERROR,
        ).PHP_EOL;
    }

    /**
     * @return array<string, mixed>
     */
    private function exportData(): array
    {
        return [
            'generated_at' => now()->toIso8601String(),
            'runtime' => [
                'summary' => $this->metadata->summary(),
                'modules' => $this->metadata->modules(),
                'commands' => $this->metadata->commands(),
                'queries' => $this->metadata->queries(),
                'events' => $this->metadata->events(),
                'permissions' => $this->metadata->permissions(),
                'roles' => $this->roles(),
                'capabilities' => $this->metadata->capabilities(),
                'navigation' => $this->metadata->navigation(),
                'notifications' => $this->metadata->notifications(),
                'scheduled_jobs' => $this->metadata->scheduledJobs(),
            ],
        ];
    }
}