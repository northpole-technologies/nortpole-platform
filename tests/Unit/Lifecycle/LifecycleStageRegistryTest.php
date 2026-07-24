<?php

declare(strict_types=1);

namespace Tests\Unit\Lifecycle;

use InvalidArgumentException;
use Northpole\Lifecycle\Contracts\LifecycleStageContract;
use Northpole\Lifecycle\LifecycleContext;
use Northpole\Lifecycle\LifecycleStageRegistry;
use PHPUnit\Framework\TestCase;

final class LifecycleStageRegistryTest extends TestCase
{
    public function test_it_registers_lifecycle_stages(): void
    {
        $registry = new LifecycleStageRegistry();

        $stage = $this->stage(
            'validate',
            10
        );

        $registry->register($stage);

        $this->assertTrue(
            $registry->has('validate')
        );

        $this->assertSame(
            $stage,
            $registry->get('validate')
        );

        $this->assertSame(
            1,
            $registry->count()
        );
    }

    public function test_it_orders_stages_by_priority(): void
    {
        $registry = new LifecycleStageRegistry();

        $late = $this->stage(
            'late',
            200
        );

        $early = $this->stage(
            'early',
            10
        );

        $middle = $this->stage(
            'middle',
            100
        );

        $registry->registerMany([
            $late,
            $early,
            $middle,
        ]);

        $this->assertSame(
            [
                $early,
                $middle,
                $late,
            ],
            $registry->sorted()
        );
    }

    public function test_it_rejects_empty_stage_names(): void
    {
        $registry = new LifecycleStageRegistry();

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module lifecycle stage names cannot be empty.'
        );

        $registry->register(
            $this->stage(
                '   ',
                10
            )
        );
    }

    public function test_it_rejects_duplicate_stage_names(): void
    {
        $registry = new LifecycleStageRegistry();

        $registry->register(
            $this->stage(
                'install',
                10
            )
        );

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Module lifecycle stage [install] is already registered.'
        );

        $registry->register(
            $this->stage(
                'install',
                20
            )
        );
    }

    public function test_it_reports_when_empty(): void
    {
        $registry = new LifecycleStageRegistry();

        $this->assertTrue(
            $registry->isEmpty()
        );

        $registry->register(
            $this->stage(
                'install',
                10
            )
        );

        $this->assertFalse(
            $registry->isEmpty()
        );
    }

    private function stage(
        string $name,
        int $priority
    ): LifecycleStageContract {
        return new class(
            $name,
            $priority
        ) implements LifecycleStageContract {
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

            public function supports(
                LifecycleContext $context
            ): bool {
                return true;
            }

            public function handle(
                LifecycleContext $context
            ): void {
            }
        };
    }
}