<?php

declare(strict_types=1);

namespace Tests\Unit\Lifecycle;

use App\Models\MarketplaceModule;
use App\Models\Organisation;
use Illuminate\Database\ConnectionInterface;
use Northpole\Lifecycle\Contracts\LifecycleStageContract;
use Northpole\Lifecycle\Enums\LifecycleOperation;
use Northpole\Lifecycle\LifecycleContext;
use Northpole\Lifecycle\LifecyclePipeline;
use Northpole\Lifecycle\LifecycleStageRegistry;
use PHPUnit\Framework\TestCase;

final class LifecyclePipelineTest extends TestCase
{
    public function test_it_runs_supported_stages_in_priority_order(): void
    {
        $executionOrder = [];

        $registry = new LifecycleStageRegistry();

        $registry->registerMany([
            $this->stage(
                'second',
                200,
                $executionOrder
            ),
            $this->stage(
                'first',
                100,
                $executionOrder
            ),
        ]);

        $pipeline = new LifecyclePipeline(
            $registry,
            $this->database()
        );

        $context = $this->context();

        $result = $pipeline->run($context);

        $this->assertSame(
            $context,
            $result
        );

        $this->assertSame(
            [
                'first',
                'second',
            ],
            $executionOrder
        );
    }

    public function test_it_skips_unsupported_stages(): void
    {
        $executionOrder = [];

        $registry = new LifecycleStageRegistry();

        $registry->registerMany([
            $this->stage(
                'supported',
                100,
                $executionOrder,
                true
            ),
            $this->stage(
                'unsupported',
                200,
                $executionOrder,
                false
            ),
        ]);

        $pipeline = new LifecyclePipeline(
            $registry,
            $this->database()
        );

        $pipeline->run(
            $this->context()
        );

        $this->assertSame(
            [
                'supported',
            ],
            $executionOrder
        );
    }

    public function test_stages_can_share_context_attributes(): void
    {
        $registry = new LifecycleStageRegistry();

        $registry->registerMany([
            new class implements LifecycleStageContract {
                public function name(): string
                {
                    return 'write';
                }

                public function priority(): int
                {
                    return 100;
                }

                public function supports(
                    LifecycleContext $context
                ): bool {
                    return true;
                }

                public function handle(
                    LifecycleContext $context
                ): void {
                    $context->set(
                        'message',
                        'NorthPole'
                    );
                }
            },
            new class implements LifecycleStageContract {
                public function name(): string
                {
                    return 'read';
                }

                public function priority(): int
                {
                    return 200;
                }

                public function supports(
                    LifecycleContext $context
                ): bool {
                    return true;
                }

                public function handle(
                    LifecycleContext $context
                ): void {
                    $context->set(
                        'result',
                        $context->get('message')
                            .' Platform'
                    );
                }
            },
        ]);

        $pipeline = new LifecyclePipeline(
            $registry,
            $this->database()
        );

        $context = $pipeline->run(
            $this->context()
        );

        $this->assertSame(
            'NorthPole Platform',
            $context->get('result')
        );
    }

    public function test_it_exposes_registered_stages(): void
    {
        $executionOrder = [];

        $registry = new LifecycleStageRegistry();

        $stage = $this->stage(
            'install',
            100,
            $executionOrder
        );

        $pipeline = new LifecyclePipeline(
            $registry,
            $this->database()
        );

        $pipeline->add($stage);

        $this->assertSame(
            [
                $stage,
            ],
            $pipeline->stages()
        );

        $this->assertSame(
            1,
            $pipeline->count()
        );

        $this->assertSame(
            $registry,
            $pipeline->registry()
        );
    }

    public function test_it_runs_the_pipeline_inside_a_database_transaction(): void
    {
        $transactionWasCalled = false;

        $database = $this->createMock(
            ConnectionInterface::class
        );

        $database
            ->expects($this->once())
            ->method('transaction')
            ->willReturnCallback(
                static function (
                    callable $callback
                ) use (
                    &$transactionWasCalled
                ): mixed {
                    $transactionWasCalled = true;

                    return $callback();
                }
            );

        $pipeline = new LifecyclePipeline(
            new LifecycleStageRegistry(),
            $database
        );

        $pipeline->run(
            $this->context()
        );

        $this->assertTrue(
            $transactionWasCalled
        );
    }

    private function context(): LifecycleContext
    {
        $module = new MarketplaceModule([
            'key' => 'santa-buddy',
            'name' => 'SantaBuddy',
            'version' => '1.0.0',
        ]);

        $organisation = new Organisation([
            'name' => 'NorthPole Technologies',
            'slug' => 'northpole-technologies',
        ]);

        return new LifecycleContext(
            LifecycleOperation::Install,
            $module,
            $organisation
        );
    }

    /**
     * @param array<int, string> $executionOrder
     */
    private function stage(
        string $name,
        int $priority,
        array &$executionOrder,
        bool $supported = true
    ): LifecycleStageContract {
        return new class(
            $name,
            $priority,
            $executionOrder,
            $supported
        ) implements LifecycleStageContract {
            /**
             * @param array<int, string> $executionOrder
             */
            public function __construct(
                private readonly string $stageName,
                private readonly int $stagePriority,
                private array &$executionOrder,
                private readonly bool $supported,
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
                return $this->supported;
            }

            public function handle(
                LifecycleContext $context
            ): void {
                $this->executionOrder[] = $this->stageName;
            }
        };
    }

    private function database(): ConnectionInterface
    {
        $database = $this->createMock(
            ConnectionInterface::class
        );

        $database
            ->method('transaction')
            ->willReturnCallback(
                static fn (
                    callable $callback
                ): mixed => $callback()
            );

        return $database;
    }
}