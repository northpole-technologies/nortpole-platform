<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use Northpole\Runtime\Commands\ModuleCommandBus;
use Northpole\Runtime\Commands\ModuleCommandRegistrar;
use Northpole\Runtime\Commands\ModuleCommandRegistry;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistrar;
use Northpole\Runtime\Jobs\ModuleScheduledJobRegistry;
use Northpole\Runtime\Lifecycle\BootPipeline;
use Northpole\Runtime\Lifecycle\StageRegistry;
use Northpole\Runtime\Notifications\ModuleNotificationRegistrar;
use Northpole\Runtime\Notifications\ModuleNotificationRegistry;
use Tests\TestCase;

final class RuntimeKernelTest extends TestCase
{
    public function test_stage_registry_is_a_singleton(): void
    {
        $first = $this->app->make(
            StageRegistry::class,
        );

        $second = $this->app->make(
            StageRegistry::class,
        );

        $this->assertSame(
            $first,
            $second,
        );
    }

    public function test_boot_pipeline_uses_the_application_stage_registry(): void
    {
        $registry = $this->app->make(
            StageRegistry::class,
        );

        $pipeline = $this->app->make(
            BootPipeline::class,
        );

        $this->assertSame(
            $registry,
            $pipeline->registry(),
        );
    }

    public function test_default_runtime_stages_are_registered(): void
    {
        $registry = $this->app->make(
            StageRegistry::class,
        );

        $this->assertSame(
            [
                'config',
                'providers',
                'routes',
                'views',
                'migrations',
                'capabilities',
                'permissions',
                'navigation',
                'event-subscribers',
                'notifications',
                'scheduled-jobs',
                'command-handlers',
                'query-handlers',
            ],
            array_map(
                static fn ($stage): string => $stage->name(),
                $registry->sorted(),
            ),
        );

        $this->assertSame(
            13,
            $registry->count(),
        );
    }

    public function test_command_registry_is_a_singleton(): void
    {
        $first = $this->app->make(
            ModuleCommandRegistry::class,
        );

        $second = $this->app->make(
            ModuleCommandRegistry::class,
        );

        $this->assertSame(
            $first,
            $second,
        );
    }

    public function test_command_registrar_uses_the_application_registry(): void
    {
        $registry = $this->app->make(
            ModuleCommandRegistry::class,
        );

        $registrar = $this->app->make(
            ModuleCommandRegistrar::class,
        );

        $this->assertSame(
            $registry,
            $registrar->registry(),
        );
    }

    public function test_command_bus_uses_the_application_registry(): void
    {
        $registry = $this->app->make(
            ModuleCommandRegistry::class,
        );

        $bus = $this->app->make(
            ModuleCommandBus::class,
        );

        $this->assertSame(
            $registry,
            $bus->registry(),
        );
    }

    public function test_notification_registry_is_a_singleton(): void
    {
        $first = $this->app->make(
            ModuleNotificationRegistry::class,
        );

        $second = $this->app->make(
            ModuleNotificationRegistry::class,
        );

        $this->assertSame(
            $first,
            $second,
        );
    }

    public function test_notification_registrar_uses_the_application_registry(): void
    {
        $registry = $this->app->make(
            ModuleNotificationRegistry::class,
        );

        $registrar = $this->app->make(
            ModuleNotificationRegistrar::class,
        );

        $this->assertSame(
            $registry,
            $registrar->registry(),
        );
    }

    public function test_scheduled_job_registry_is_a_singleton(): void
    {
        $first = $this->app->make(
            ModuleScheduledJobRegistry::class,
        );

        $second = $this->app->make(
            ModuleScheduledJobRegistry::class,
        );

        $this->assertSame(
            $first,
            $second,
        );
    }

    public function test_scheduled_job_registrar_uses_the_application_registry(): void
    {
        $registry = $this->app->make(
            ModuleScheduledJobRegistry::class,
        );

        $registrar = $this->app->make(
            ModuleScheduledJobRegistrar::class,
        );

        $this->assertSame(
            $registry,
            $registrar->registry(),
        );
    }
}