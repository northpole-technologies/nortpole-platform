<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Northpole\Runtime\Diagnostics\RuntimeDoctorViewModel;

final class NorthpoleAboutCommand extends Command
{
    protected $signature = 'northpole:doctor';

    protected $aliases = [
        'northpole:about',
    ];

    protected $description =
        'Inspect the health and state of the NorthPole platform runtime';

    public function __construct(
        private readonly RuntimeDoctorViewModel $viewModel,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $data = $this->viewModel->data();

        $this->newLine();

        $this->info('NorthPole Platform Doctor');
        $this->line(str_repeat('=', 25));

        $this->newLine();

        $this->comment('Runtime Health');
        $this->line(
            'Status: '.$data['healthStatus'],
        );

        $this->newLine();

        $this->comment('Platform');

        $this->table(
            ['Property', 'Value'],
            $data['platformRows'],
        );

        $this->comment('Modules');

        $this->table(
            ['Metric', 'Count'],
            $data['moduleSummaryRows'],
        );

        $this->comment('Runtime Registries');

        $this->table(
            ['Registry', 'Count'],
            $data['registryRows'],
        );

        $this->comment('Discovered Modules');

        if ($data['moduleRows'] === []) {
            $this->warn('No modules discovered.');

            $this->newLine();

            $this->warn(
                'Overall status: '.$data['overallStatus'],
            );

            return self::SUCCESS;
        }

        $this->table(
            ['Module', 'Slug', 'Version', 'Status'],
            $data['moduleRows'],
        );

        if ($data['overallStatus'] === 'HEALTHY') {
            $this->info(
                'Overall status: '.$data['overallStatus'],
            );

            return self::SUCCESS;
        }

        $this->warn(
            'Overall status: '.$data['overallStatus'],
        );

        return self::SUCCESS;
    }
}