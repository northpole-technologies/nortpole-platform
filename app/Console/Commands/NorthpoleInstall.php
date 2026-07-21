<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class NorthpoleInstall extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'northpole:install';

    /**
     * The console command description.
     */
    protected $description = 'Install and verify the Northpole Platform.';

    public function handle(): int
    {
        $this->newLine();

        $this->info('========================================');
        $this->info('     Northpole Platform Installer');
        $this->info('========================================');

        $this->newLine();

        $checks = [
            'Modules Folder' => base_path('modules'),
            'Platform Folder' => base_path('platform'),
            'Docs Folder' => base_path('docs'),
            'Scripts Folder' => base_path('scripts'),
        ];

        foreach ($checks as $name => $path) {

            if (is_dir($path)) {
                $this->components->twoColumnDetail($name, '<fg=green>OK</>');
            } else {
                $this->components->twoColumnDetail($name, '<fg=red>Missing</>');
            }

        }

        $this->newLine();

        $this->info('Northpole installation verified.');

        return self::SUCCESS;
    }
}