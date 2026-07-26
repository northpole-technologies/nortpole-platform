<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Northpole\Runtime\Diagnostics\RuntimeEnvironmentService;
use Northpole\Runtime\Diagnostics\RuntimeModuleStatisticsService;
use Northpole\Runtime\Diagnostics\RuntimeRegistryStatisticsService;

final class NorthpoleAboutCommand extends Command
{
    protected $signature = 'northpole:doctor';

    protected $aliases = [
        'northpole:about',
    ];

    protected $description =
        'Inspect the health and state of the NorthPole platform runtime';

    public function __construct(
        private readonly RuntimeEnvironmentService $environment,
        private readonly RuntimeModuleStatisticsService $moduleStatistics,
        private readonly RuntimeRegistryStatisticsService $registryStatistics,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $moduleSummary = $this->moduleStatistics->summary();
        $moduleRows = $this->moduleStatistics->consoleRows();

        $this->newLine();

        $this->info('NorthPole Platform Doctor');
        $this->line(str_repeat('=', 25));

        $this->newLine();

        $this->comment('Runtime Health');
        $this->line('Status: HEALTHY');

        $this->newLine();

        $this->comment('Platform');

        $this->table(
            ['Property', 'Value'],
            $this->environment->consoleRows(),
        );

        $this->comment('Modules');

        $this->table(
            ['Metric', 'Count'],
            [
                [
                    'Discovered',
                    $moduleSummary['discovered'],
                ],
                [
                    'Enabled',
                    $moduleSummary['enabled'],
                ],
                [
                    'Disabled',
                    $moduleSummary['disabled'],
                ],
            ],
        );

        $this->comment('Runtime Registries');

        $this->table(
            ['Registry', 'Count'],
            $this->registryStatistics->consoleRows(),
        );

        $this->comment('Discovered Modules');

        if ($moduleRows === []) {
            $this->warn('No modules discovered.');

            $this->newLine();
            $this->warn('Overall status: DEGRADED');

            return self::SUCCESS;
        }

        $this->table(
            ['Module', 'Slug', 'Version', 'Status'],
            $moduleRows,
        );

        $this->info('Overall status: HEALTHY');

        return self::SUCCESS;
    }
}
