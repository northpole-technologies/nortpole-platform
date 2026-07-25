<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Northpole\Console\Support\ModuleScaffolder;
use Throwable;

final class MakeModuleCommand extends Command
{
    protected $signature = 'northpole:make-module
                            {name : The module name}
                            {--force : Overwrite an existing module}';

    protected $description = 'Create a new NorthPole platform module';

    public function __construct(
        private readonly ModuleScaffolder $scaffolder,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $modulePath = $this->scaffolder->scaffold(
                requestedName: (string) $this->argument('name'),
                overwrite: (bool) $this->option('force'),
            );

            $this->newLine();

            $this->info('NorthPole module created successfully.');

            $this->line("Location: {$modulePath}");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
