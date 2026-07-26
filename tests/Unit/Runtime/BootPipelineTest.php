<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime;

use Northpole\Runtime\Contracts\BootStageContract;

use Northpole\Runtime\Lifecycle\BootContext;
use Northpole\Runtime\Lifecycle\BootPipeline;
use Northpole\Runtime\Lifecycle\StageRegistry;





use Tests\Support\CreatesRuntime;
use Tests\TestCase;

final class BootPipelineTest extends TestCase
{
    use CreatesRuntime;
    public function test_pipeline_executes_stages_by_priority_for_enabled_modules(): void
    {
        $runtime = $this->createRuntime();

        $runtime->discover();

        $executionLog = [];

        $laterStage = $this->createStage(
            name: 'later',
            priority: 200,
            executionLog: $executionLog,
        );

        $earlierStage = $this->createStage(
            name: 'earlier',
            priority: 100,
            executionLog: $executionLog,
        );

        $pipeline = new BootPipeline;

        $pipeline
            ->add($laterStage)
            ->add($earlierStage)
            ->boot($runtime);

        $enabledSlugs = array_keys(
            $runtime->enabledModules(),
        );

        $expected = [];

        foreach ($enabledSlugs as $slug) {
            $expected[] = "earlier:{$slug}";
        }

        foreach ($enabledSlugs as $slug) {
            $expected[] = "later:{$slug}";
        }

        $this->assertSame(
            2,
            $pipeline->count(),
        );

        $this->assertSame(
            ['earlier', 'later'],
            array_map(
                static fn (BootStageContract $stage): string => $stage->name(),
                $pipeline->stages(),
            ),
        );

        $this->assertSame(
            $expected,
            $executionLog,
        );
    }

    public function test_pipeline_uses_the_supplied_stage_registry(): void
    {
        $registry = new StageRegistry;

        $pipeline = new BootPipeline($registry);

        $stage = $this->createStage(
            name: 'config',
            priority: 50,
        );

        $pipeline->add($stage);

        $this->assertSame(
            $registry,
            $pipeline->registry(),
        );

        $this->assertTrue(
            $registry->has('config'),
        );

        $this->assertSame(
            $stage,
            $registry->get('config'),
        );
    }

    public function test_pipeline_rejects_duplicate_stage_names(): void
    {
        $pipeline = new BootPipeline;

        $pipeline->add(
            $this->createStage(
                name: 'config',
                priority: 50,
            )
        );

        $this->expectException(
            \InvalidArgumentException::class,
        );

        $this->expectExceptionMessage(
            'Runtime boot stage [config] is already registered.',
        );

        $pipeline->add(
            $this->createStage(
                name: 'config',
                priority: 100,
            )
        );
    }


    /**
     * @param  array<int, string>  $executionLog
     */
    private function createStage(
        string $name,
        int $priority,
        array &$executionLog = [],
    ): BootStageContract {
        return new class($name, $priority, $executionLog) implements BootStageContract
        {
            /**
             * @param  array<int, string>  $executionLog
             */
            public function __construct(
                private readonly string $stageName,
                private readonly int $stagePriority,
                private array &$executionLog,
            ) {}

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
                $this->executionLog[] = sprintf(
                    '%s:%s',
                    $this->name(),
                    $context->module()->slug(),
                );
            }
        };
    }
}
