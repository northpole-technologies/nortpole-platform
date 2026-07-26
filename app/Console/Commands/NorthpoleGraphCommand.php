<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Northpole\Runtime\Graph\Contracts\RuntimeGraphBuilderContract;
use Northpole\Runtime\Graph\Contracts\RuntimeGraphRendererContract;
use Throwable;

final class NorthpoleGraphCommand extends Command
{
    protected $signature = 'northpole:graph
                            {--path=docs/runtime-graph.md : Graph output path}';

    protected $description =
        'Generate a Mermaid diagram from the live NorthPole runtime graph';

    public function __construct(
        private readonly RuntimeGraphBuilderContract $graphBuilder,
        private readonly RuntimeGraphRendererContract $graphRenderer,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $path = $this->resolveOutputPath();

        try {
            $graph = $this->graphBuilder->build();

            $output = $this->graphRenderer->render(
                $graph,
            );

            File::ensureDirectoryExists(
                dirname($path),
            );

            File::put(
                $path,
                $output,
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

    private function isAbsolutePath(
        string $path,
    ): bool {
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
}
