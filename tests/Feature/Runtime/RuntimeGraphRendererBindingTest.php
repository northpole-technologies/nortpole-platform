<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use Northpole\Runtime\Graph\Contracts\RuntimeGraphRendererContract;
use Northpole\Runtime\Graph\RuntimeGraphMermaidRenderer;
use Tests\TestCase;

final class RuntimeGraphRendererBindingTest extends TestCase
{
    public function test_graph_renderer_contract_resolves_to_the_mermaid_renderer(): void
    {
        $renderer = $this->app->make(
            RuntimeGraphRendererContract::class,
        );

        $this->assertInstanceOf(
            RuntimeGraphMermaidRenderer::class,
            $renderer,
        );
    }

    public function test_graph_renderer_contract_is_registered_as_a_singleton(): void
    {
        $first = $this->app->make(
            RuntimeGraphRendererContract::class,
        );

        $second = $this->app->make(
            RuntimeGraphRendererContract::class,
        );

        $this->assertSame(
            $first,
            $second,
        );
    }
}
