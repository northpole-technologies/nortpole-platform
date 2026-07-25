<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class NorthpoleDocsCommandTest extends TestCase
{
    private string $documentationPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->documentationPath = storage_path(
            'framework/testing/northpole-docs',
        );

        File::deleteDirectory(
            $this->documentationPath,
        );
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(
            $this->documentationPath,
        );

        parent::tearDown();
    }

    public function test_it_generates_runtime_documentation(): void
    {
        $this->artisan('northpole:docs', [
            '--path' => $this->documentationPath,
        ])
            ->expectsOutputToContain(
                'NorthPole documentation generated.',
            )
            ->assertSuccessful();

        $expectedFiles = [
            'README.md',
            'runtime.md',
            'modules.md',
            'commands.md',
            'queries.md',
            'events.md',
            'permissions.md',
            'capabilities.md',
        ];

        foreach ($expectedFiles as $file) {
            $this->assertFileExists(
                $this->documentationPath
                    .DIRECTORY_SEPARATOR
                    .$file,
            );
        }
    }

    public function test_generated_command_documentation_uses_runtime_metadata(): void
    {
        $this->artisan('northpole:docs', [
            '--path' => $this->documentationPath,
        ])->assertSuccessful();

        $contents = File::get(
            $this->documentationPath
                .DIRECTORY_SEPARATOR
                .'commands.md',
        );

        $this->assertStringContainsString(
            '# NorthPole Commands',
            $contents,
        );

        $this->assertStringContainsString(
            'crm.customer.create',
            $contents,
        );

        $this->assertStringContainsString(
            'Modules\\CRM\\Commands\\CreateCustomerHandler',
            $contents,
        );
    }

    public function test_generated_query_documentation_uses_runtime_metadata(): void
    {
        $this->artisan('northpole:docs', [
            '--path' => $this->documentationPath,
        ])->assertSuccessful();

        $contents = File::get(
            $this->documentationPath
                .DIRECTORY_SEPARATOR
                .'queries.md',
        );

        $this->assertStringContainsString(
            '# NorthPole Queries',
            $contents,
        );

        $this->assertStringContainsString(
            'crm.customer.find',
            $contents,
        );

        $this->assertStringContainsString(
            'Modules\\CRM\\Queries\\FindCustomerHandler',
            $contents,
        );
    }

    public function test_it_does_not_overwrite_architecture_documentation(): void
    {
        File::ensureDirectoryExists(
            $this->documentationPath,
        );

        $architecturePath = $this->documentationPath
            .DIRECTORY_SEPARATOR
            .'architecture.md';

        File::put(
            $architecturePath,
            'Hand-written architecture documentation.',
        );

        $this->artisan('northpole:docs', [
            '--path' => $this->documentationPath,
        ])->assertSuccessful();

        $this->assertSame(
            'Hand-written architecture documentation.',
            File::get($architecturePath),
        );
    }
}
