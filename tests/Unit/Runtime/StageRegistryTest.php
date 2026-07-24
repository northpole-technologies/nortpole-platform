<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\BootStageContract;
use Northpole\Runtime\Lifecycle\BootContext;
use Northpole\Runtime\Lifecycle\StageRegistry;
use Tests\TestCase;

final class StageRegistryTest extends TestCase
{
    public function test_it_registers_and_retrieves_a_stage(): void
    {
        $stage = $this->createStage(
            name: 'config',
            priority: 50,
        );

        $registry = new StageRegistry();

        $registry->register($stage);

        $this->assertTrue(
            $registry->has('config')
        );

        $this->assertSame(
            $stage,
            $registry->get('config')
        );

        $this->assertSame(
            [$stage],
            $registry->all()
        );

        $this->assertSame(
            1,
            $registry->count()
        );

        $this->assertFalse(
            $registry->isEmpty()
        );
    }

    public function test_it_registers_multiple_stages(): void
    {
        $configStage = $this->createStage(
            name: 'config',
            priority: 50,
        );

        $providerStage = $this->createStage(
            name: 'providers',
            priority: 100,
        );

        $registry = new StageRegistry();

        $registry->registerMany([
            $configStage,
            $providerStage,
        ]);

        $this->assertSame(
            2,
            $registry->count()
        );

        $this->assertSame(
            [
                $configStage,
                $providerStage,
            ],
            $registry->all()
        );
    }

    public function test_it_sorts_stages_by_priority(): void
    {
        $laterStage = $this->createStage(
            name: 'later',
            priority: 200,
        );

        $earlierStage = $this->createStage(
            name: 'earlier',
            priority: 100,
        );

        $registry = new StageRegistry();

        $registry->registerMany([
            $laterStage,
            $earlierStage,
        ]);

        $this->assertSame(
            [
                $earlierStage,
                $laterStage,
            ],
            $registry->sorted()
        );
    }

    public function test_it_rejects_an_empty_stage_name(): void
    {
        $stage = $this->createStage(
            name: '   ',
            priority: 100,
        );

        $registry = new StageRegistry();

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Runtime boot stage names cannot be empty.'
        );

        $registry->register($stage);
    }

    public function test_it_rejects_duplicate_stage_names(): void
    {
        $firstStage = $this->createStage(
            name: 'config',
            priority: 50,
        );

        $secondStage = $this->createStage(
            name: 'config',
            priority: 100,
        );

        $registry = new StageRegistry();

        $registry->register($firstStage);

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Runtime boot stage [config] is already registered.'
        );

        $registry->register($secondStage);
    }

    public function test_it_rejects_an_unknown_stage_name(): void
    {
        $registry = new StageRegistry();

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Runtime boot stage [events] is not registered.'
        );

        $registry->get('events');
    }

    public function test_a_new_registry_is_empty(): void
    {
        $registry = new StageRegistry();

        $this->assertTrue(
            $registry->isEmpty()
        );

        $this->assertSame(
            0,
            $registry->count()
        );

        $this->assertSame(
            [],
            $registry->all()
        );

        $this->assertSame(
            [],
            $registry->sorted()
        );
    }

    private function createStage(
        string $name,
        int $priority,
    ): BootStageContract {
        return new class($name, $priority) implements BootStageContract
        {
            public function __construct(
                private readonly string $stageName,
                private readonly int $stagePriority,
            ) {
            }

            public function name(): string
            {
                return $this->stageName;
            }

            public function priority(): int
            {
                return $this->stagePriority;
            }

            public function boot(BootContext $context): void
            {
            }
        };
    }
}