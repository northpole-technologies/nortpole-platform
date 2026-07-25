<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Northpole\Runtime\Capabilities\CapabilityRegistry;
use Northpole\Runtime\Commands\ModuleCommandRegistry;
use Northpole\Runtime\Events\ModuleEventRegistry;
use Northpole\Runtime\Lifecycle\StageRegistry;
use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Navigation\NavigationRegistry;
use Northpole\Runtime\Permissions\PermissionRegistry;
use Northpole\Runtime\Queries\ModuleQueryRegistry;
use Northpole\Runtime\Runtime;
use Tests\TestCase;

final class NorthpoleAboutCommandTest extends TestCase
{
    public function test_it_displays_northpole_runtime_information(): void
    {
        $runtime = $this->app->make(
            Runtime::class,
        );

        $enabledModuleCount = count(
            array_filter(
                $runtime->modules(),
                static fn (ModuleManifest $module): bool => $module->enabled(),
            ),
        );

        $this->artisan('northpole:about')
            ->expectsOutputToContain(
                'NorthPole Platform',
            )
            ->expectsOutputToContain(
                'Runtime',
            )
            ->expectsOutputToContain(
                'Status: Healthy',
            )
            ->expectsOutputToContain(
                'Modules discovered: '.$runtime->count(),
            )
            ->expectsOutputToContain(
                'Modules enabled: '.$enabledModuleCount,
            )
            ->expectsOutputToContain(
                'Boot stages: '.$this->app
                    ->make(StageRegistry::class)
                    ->count(),
            )
            ->expectsOutputToContain(
                'Capabilities: '.$this->app
                    ->make(CapabilityRegistry::class)
                    ->count(),
            )
            ->expectsOutputToContain(
                'Commands: '.$this->app
                    ->make(ModuleCommandRegistry::class)
                    ->count(),
            )
            ->expectsOutputToContain(
                'Queries: '.$this->app
                    ->make(ModuleQueryRegistry::class)
                    ->count(),
            )
            ->expectsOutputToContain(
                'Event listeners: '.$this->app
                    ->make(ModuleEventRegistry::class)
                    ->count(),
            )
            ->expectsOutputToContain(
                'Navigation items: '.$this->app
                    ->make(NavigationRegistry::class)
                    ->count(),
            )
            ->expectsOutputToContain(
                'Permissions: '.$this->app
                    ->make(PermissionRegistry::class)
                    ->count(),
            )
            ->assertSuccessful();
    }

    public function test_it_displays_discovered_modules(): void
    {
        $runtime = $this->app->make(
            Runtime::class,
        );

        $command = $this->artisan(
            'northpole:about',
        );

        $command->expectsOutputToContain(
            'Modules',
        );

        foreach ($runtime->modules() as $module) {
            $command->expectsOutputToContain(
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

        $command->assertSuccessful();
    }
}
