<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Northpole\Runtime\Capabilities\CapabilityRegistry;
use Northpole\Runtime\Commands\ModuleCommandRegistry;
use Northpole\Runtime\Configuration\ModuleConfigurationRegistry;
use Northpole\Runtime\Events\ModuleEventRegistry;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistry;
use Northpole\Runtime\Lifecycle\StageRegistry;
use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Navigation\NavigationRegistry;
use Northpole\Runtime\Notifications\ModuleNotificationRegistry;
use Northpole\Runtime\Permissions\PermissionRegistry;
use Northpole\Runtime\Queries\ModuleQueryRegistry;
use Northpole\Runtime\Runtime;
use Tests\TestCase;

final class NorthpoleAboutCommandTest extends TestCase
{
    public function test_it_displays_northpole_runtime_health(): void
    {
        $this->artisan('northpole:doctor')
            ->expectsOutputToContain(
                'NorthPole Platform Doctor',
            )
            ->expectsOutputToContain(
                'Runtime Health',
            )
            ->expectsOutputToContain(
                'Status: HEALTHY',
            )
            ->expectsOutputToContain(
                'Platform',
            )
            ->expectsOutputToContain(
                'Environment',
            )
            ->expectsOutputToContain(
                'Laravel',
            )
            ->expectsOutputToContain(
                'PHP',
            )
            ->expectsOutputToContain(
                'Peak memory',
            )
            ->expectsOutputToContain(
                'Modules',
            )
            ->expectsOutputToContain(
                'Discovered',
            )
            ->expectsOutputToContain(
                'Enabled',
            )
            ->expectsOutputToContain(
                'Disabled',
            )
            ->expectsOutputToContain(
                'Runtime Registries',
            )
            ->expectsOutputToContain(
                'Boot stages',
            )
            ->expectsOutputToContain(
                'Capabilities',
            )
            ->expectsOutputToContain(
                'Commands',
            )
            ->expectsOutputToContain(
                'Queries',
            )
            ->expectsOutputToContain(
                'Event listeners',
            )
            ->expectsOutputToContain(
                'Navigation items',
            )
            ->expectsOutputToContain(
                'Permissions',
            )
            ->expectsOutputToContain(
                'Configuration',
            )
            ->expectsOutputToContain(
                'Notifications',
            )
            ->expectsOutputToContain(
                'Scheduled jobs',
            )
            ->expectsOutputToContain(
                'Overall status: HEALTHY',
            )
            ->assertSuccessful();
    }
    public function test_it_displays_discovered_modules(): void
    {
        $runtime = $this->app->make(
            Runtime::class,
        );

        $command = $this->artisan(
            'northpole:doctor',
        );

        $command->expectsOutputToContain(
            'Discovered Modules',
        );

        foreach ($runtime->modules() as $module) {
            $command->expectsOutputToContain(
                $module->name(),
            );
        }

        $command->assertSuccessful();
    }

    public function test_about_remains_an_alias_for_doctor(): void
    {
        $this->artisan('northpole:about')
            ->expectsOutputToContain(
                'NorthPole Platform Doctor',
            )
            ->expectsOutputToContain(
                'Overall status: HEALTHY',
            )
            ->assertSuccessful();
    }
}