<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use Northpole\Runtime\Graph\Contracts\RuntimeGraphBuilderContract;
use Northpole\Runtime\Graph\RuntimeGraphBuilder;
use Tests\TestCase;

final class RuntimeGraphBuilderBindingTest extends TestCase
{
    public function test_graph_builder_contract_resolves_to_the_runtime_graph_builder(): void
    {
        $builder = $this->app->make(
            RuntimeGraphBuilderContract::class,
        );

        $this->assertInstanceOf(
            RuntimeGraphBuilder::class,
            $builder,
        );
    }

    public function test_graph_builder_contract_is_registered_as_a_singleton(): void
    {
        $first = $this->app->make(
            RuntimeGraphBuilderContract::class,
        );

        $second = $this->app->make(
            RuntimeGraphBuilderContract::class,
        );

        $this->assertSame(
            $first,
            $second,
        );
    }
}
