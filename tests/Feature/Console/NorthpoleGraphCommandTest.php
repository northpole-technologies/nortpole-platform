<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class NorthpoleGraphCommandTest extends TestCase
{
    private string $graphPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->graphPath = storage_path(
            'framework/testing/northpole-graph.md',
        );

        File::delete(
            $this->graphPath,
        );
    }

    protected function tearDown(): void
    {
        File::delete(
            $this->graphPath,
        );

        parent::tearDown();
    }

    public function test_it_generates_a_mermaid_runtime_graph(): void
    {
        $this->artisan('northpole:graph', [
            '--path' => $this->graphPath,
        ])
            ->expectsOutputToContain(
                'NorthPole runtime graph generated.',
            )
            ->assertSuccessful();

        $this->assertFileExists(
            $this->graphPath,
        );

        $contents = File::get(
            $this->graphPath,
        );

        $this->assertStringContainsString(
            '# NorthPole Runtime Graph',
            $contents,
        );

        $this->assertStringContainsString(
            '```mermaid',
            $contents,
        );

        $this->assertStringContainsString(
            'flowchart LR',
            $contents,
        );
    }

    public function test_generated_graph_contains_discovered_modules(): void
    {
        $this->artisan('northpole:graph', [
            '--path' => $this->graphPath,
        ])->assertSuccessful();

        $contents = File::get(
            $this->graphPath,
        );

        $this->assertStringContainsString(
            'CRM',
            $contents,
        );

        $this->assertStringContainsString(
            'SantaBuddy',
            $contents,
        );
    }

    public function test_generated_graph_contains_command_relationships(): void
    {
        $this->artisan('northpole:graph', [
            '--path' => $this->graphPath,
        ])->assertSuccessful();

        $contents = File::get(
            $this->graphPath,
        );

        $this->assertStringContainsString(
            'crm.customer.create',
            $contents,
        );

        $this->assertStringContainsString(
            'CreateCustomerHandler',
            $contents,
        );
    }

    public function test_generated_graph_contains_query_relationships(): void
    {
        $this->artisan('northpole:graph', [
            '--path' => $this->graphPath,
        ])->assertSuccessful();

        $contents = File::get(
            $this->graphPath,
        );

        $this->assertStringContainsString(
            'crm.customer.find',
            $contents,
        );

        $this->assertStringContainsString(
            'FindCustomerHandler',
            $contents,
        );
    }
}