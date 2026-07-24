<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use Northpole\Runtime\Lifecycle\BootPipeline;
use Northpole\Runtime\Lifecycle\StageRegistry;
use Tests\TestCase;

final class RuntimeKernelTest extends TestCase
{
    public function test_stage_registry_is_a_singleton(): void
    {
        $first = $this->app->make(StageRegistry::class);
        $second = $this->app->make(StageRegistry::class);

        $this->assertSame(
            $first,
            $second,
        );
    }

    public function test_boot_pipeline_uses_the_application_stage_registry(): void
    {
        $registry = $this->app->make(StageRegistry::class);
        $pipeline = $this->app->make(BootPipeline::class);

        $this->assertSame(
            $registry,
            $pipeline->registry(),
        );
    }

    public function test_default_runtime_stages_are_registered(): void
    {
        $registry = $this->app->make(StageRegistry::class);

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
            ],
            array_map(
                static fn ($stage): string => $stage->name(),
                $registry->sorted(),
            ),
        );

        $this->assertSame(
            8,
            $registry->count(),
        );
    }
}