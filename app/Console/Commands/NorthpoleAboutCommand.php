<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Northpole\Runtime\Capabilities\CapabilityRegistry;
use Northpole\Runtime\Commands\ModuleCommandRegistry;
use Northpole\Runtime\Events\ModuleEventRegistry;
use Northpole\Runtime\Lifecycle\StageRegistry;
use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Navigation\NavigationRegistry;
use Northpole\Runtime\Permissions\PermissionRegistry;
use Northpole\Runtime\Queries\ModuleQueryRegistry;
use Northpole\Runtime\Runtime;

final class NorthpoleAboutCommand extends Command
{
    protected $signature = 'northpole:about';

    protected $description =
        'Display information about the NorthPole platform runtime';

    public function __construct(
        private readonly Runtime $runtime,
        private readonly StageRegistry $stageRegistry,
        private readonly CapabilityRegistry $capabilityRegistry,
        private readonly ModuleCommandRegistry $commandRegistry,
        private readonly ModuleQueryRegistry $queryRegistry,
        private readonly ModuleEventRegistry $eventRegistry,
        private readonly NavigationRegistry $navigationRegistry,
        private readonly PermissionRegistry $permissionRegistry,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $modules = $this->runtime->modules();

        $enabledModuleCount = count(
            array_filter(
                $modules,
                static fn (ModuleManifest $module): bool => $module->enabled(),
            ),
        );

        $this->newLine();

        $this->info('NorthPole Platform');
        $this->line(str_repeat('=', 18));

        $this->newLine();

        $this->comment('Runtime');
        $this->line('Status: Healthy');

        $this->newLine();

        $this->comment('Registries');
        $this->line(
            'Modules discovered: '.$this->runtime->count(),
        );
        $this->line(
            'Modules enabled: '.$enabledModuleCount,
        );
        $this->line(
            'Boot stages: '.$this->stageRegistry->count(),
        );
        $this->line(
            'Capabilities: '.$this->capabilityRegistry->count(),
        );
        $this->line(
            'Commands: '.$this->commandRegistry->count(),
        );
        $this->line(
            'Queries: '.$this->queryRegistry->count(),
        );
        $this->line(
            'Event listeners: '.$this->eventRegistry->count(),
        );
        $this->line(
            'Navigation items: '.$this->navigationRegistry->count(),
        );
        $this->line(
            'Permissions: '.$this->permissionRegistry->count(),
        );

        $this->newLine();

        $this->comment('Modules');

        if ($modules === []) {
            $this->warn('No modules discovered.');

            return self::SUCCESS;
        }

        foreach ($modules as $module) {
            $this->line(
                sprintf(
                    '%s | Slug: %s | Version: %s | Status: %s',
                    $module->name(),
                    $module->slug(),
                    $module->version(),
                    $module->enabled()
                        ? 'Enabled'
                        : 'Disabled',
                ),
            );
        }

        return self::SUCCESS;
    }
}
