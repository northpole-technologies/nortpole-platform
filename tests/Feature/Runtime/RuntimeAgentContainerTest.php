<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use Northpole\Runtime\Agents\ModuleAgentBus;
use Northpole\Runtime\Agents\ModuleAgentRegistrar;
use Northpole\Runtime\Agents\ModuleAgentRegistry;
use Northpole\Runtime\Lifecycle\StageRegistry;
use Tests\TestCase;

final class RuntimeAgentContainerTest extends TestCase
{
    public function test_agent_registry_is_a_singleton(): void
    {
        self::assertSame(
            $this->app->make(
                ModuleAgentRegistry::class
            ),
            $this->app->make(
                ModuleAgentRegistry::class
            )
        );
    }

    public function test_agent_registrar_is_a_singleton(): void
    {
        self::assertSame(
            $this->app->make(
                ModuleAgentRegistrar::class
            ),
            $this->app->make(
                ModuleAgentRegistrar::class
            )
        );
    }

    public function test_agent_bus_is_a_singleton(): void
    {
        self::assertSame(
            $this->app->make(
                ModuleAgentBus::class
            ),
            $this->app->make(
                ModuleAgentBus::class
            )
        );
    }

    public function test_agent_handler_stage_is_registered_last(): void
    {
        $stages = $this->app
            ->make(StageRegistry::class)
            ->sorted();

        self::assertSame(
            'agent-handlers',
            $stages[array_key_last($stages)]->name()
        );

        self::assertSame(
            720,
            $stages[array_key_last($stages)]->priority()
        );
    }
}