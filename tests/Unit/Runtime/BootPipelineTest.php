<?php

namespace Tests\Unit\Runtime;

use Northpole\Runtime\Contracts\BootStageContract;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Lifecycle\BootContext;
use Northpole\Runtime\Lifecycle\BootPipeline;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Runtime;
use Tests\TestCase;

class BootPipelineTest extends TestCase
{
    public function test_pipeline_executes_stages_by_priority_for_enabled_modules(): void
    {
        $repository = new ModuleRepository();

        $runtime = new Runtime(
            new ModuleDiscovery(
                new ModuleFinder(),
                new ManifestLoader(),
                $repository
            ),
            $repository,
            base_path('modules')
        );

        $runtime->discover();

        $executionLog = [];

        $laterStage = new class($executionLog) implements BootStageContract
        {
            /**
             * @param array<int, string> $executionLog
             */
            public function __construct(
                private array &$executionLog
            ) {
            }

            public function name(): string
            {
                return 'later';
            }

            public function priority(): int
            {
                return 200;
            }

            public function boot(BootContext $context): void
            {
                $this->executionLog[] = sprintf(
                    '%s:%s',
                    $this->name(),
                    $context->module()->slug()
                );
            }
        };

        $earlierStage = new class($executionLog) implements BootStageContract
        {
            /**
             * @param array<int, string> $executionLog
             */
            public function __construct(
                private array &$executionLog
            ) {
            }

            public function name(): string
            {
                return 'earlier';
            }

            public function priority(): int
            {
                return 100;
            }

            public function boot(BootContext $context): void
            {
                $this->executionLog[] = sprintf(
                    '%s:%s',
                    $this->name(),
                    $context->module()->slug()
                );
            }
        };

        $pipeline = new BootPipeline();

        $pipeline
            ->add($laterStage)
            ->add($earlierStage)
            ->boot($runtime);

        $enabledSlugs = array_keys(
            $runtime->enabledModules()
        );

        $expected = [];

        foreach ($enabledSlugs as $slug) {
            $expected[] = "earlier:{$slug}";
        }

        foreach ($enabledSlugs as $slug) {
            $expected[] = "later:{$slug}";
        }

        $this->assertSame(2, $pipeline->count());
        $this->assertSame(
            ['earlier', 'later'],
            array_map(
                static fn (BootStageContract $stage): string =>
                    $stage->name(),
                $pipeline->stages()
            )
        );
        $this->assertSame($expected, $executionLog);
    }
}