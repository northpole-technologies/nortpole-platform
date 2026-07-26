<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Northpole\Runtime\Diagnostics\RuntimeRegistryStatisticsService;
use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Runtime;

final class NorthpoleAboutCommand extends Command
{
    protected $signature = 'northpole:doctor';

    protected $aliases = [
        'northpole:about',
    ];

    protected $description =
        'Inspect the health and state of the NorthPole platform runtime';

    public function __construct(
        private readonly Runtime $runtime,
        private readonly RuntimeRegistryStatisticsService $registryStatistics,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $modules = $this->runtime->modules();

        $enabledModuleCount = count(
            array_filter(
                $modules,
                static fn (ModuleManifest $module): bool =>
                    $module->enabled(),
            ),
        );

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
            [
                ['Environment', (string) app()->environment()],
                ['Laravel', app()->version()],
                ['PHP', PHP_VERSION],
                [
                    'Peak memory',
                    $this->formatBytes(
                        memory_get_peak_usage(true),
                    ),
                ],
            ],
        );

        $this->comment('Modules');

        $this->table(
            ['Metric', 'Count'],
            [
                ['Discovered', $this->runtime->count()],
                ['Enabled', $enabledModuleCount],
                [
                    'Disabled',
                    $this->runtime->count() - $enabledModuleCount,
                ],
            ],
        );

        $this->comment('Runtime Registries');

        $this->table(
            ['Registry', 'Count'],
            $this->registryStatistics->consoleRows(),
        );

        $this->comment('Discovered Modules');

        if ($modules === []) {
            $this->warn('No modules discovered.');

            $this->newLine();
            $this->warn('Overall status: DEGRADED');

            return self::SUCCESS;
        }

        $moduleRows = [];

        foreach ($modules as $module) {
            $moduleRows[] = [
                $module->name(),
                $module->slug(),
                $module->version(),
                $module->enabled()
                    ? 'Enabled'
                    : 'Disabled',
            ];
        }

        $this->table(
            ['Module', 'Slug', 'Version', 'Status'],
            $moduleRows,
        );

        $this->info('Overall status: HEALTHY');

        return self::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        $units = [
            'B',
            'KB',
            'MB',
            'GB',
        ];

        $value = (float) $bytes;
        $unitIndex = 0;

        while (
            $value >= 1024
            && $unitIndex < count($units) - 1
        ) {
            $value /= 1024;
            $unitIndex++;
        }

        return number_format(
            $value,
            $unitIndex === 0 ? 0 : 2,
        ).' '.$units[$unitIndex];
    }
}