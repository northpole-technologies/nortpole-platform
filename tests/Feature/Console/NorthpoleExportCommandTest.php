<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class NorthpoleExportCommandTest extends TestCase
{
    private string $exportPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exportPath = storage_path(
            'framework/testing/northpole-runtime.json',
        );

        File::delete(
            $this->exportPath,
        );
    }

    protected function tearDown(): void
    {
        File::delete(
            $this->exportPath,
        );

        parent::tearDown();
    }

    public function test_it_exports_runtime_metadata_as_json(): void
    {
        $this->artisan('northpole:export', [
            '--path' => $this->exportPath,
        ])
            ->expectsOutputToContain(
                'NorthPole runtime export generated.',
            )
            ->assertSuccessful();

        $this->assertFileExists(
            $this->exportPath,
        );

        $contents = File::get(
            $this->exportPath,
        );

        $decoded = json_decode(
            $contents,
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertIsArray(
            $decoded,
        );

        $this->assertArrayHasKey(
            'runtime',
            $decoded,
        );

        $this->assertArrayHasKey(
            'generated_at',
            $decoded,
        );
    }

    public function test_export_contains_runtime_summary_and_modules(): void
    {
        $this->generateExport();

        $export = $this->readExport();

        $this->assertArrayHasKey(
            'summary',
            $export['runtime'],
        );

        $this->assertArrayHasKey(
            'modules',
            $export['runtime'],
        );

        $moduleSlugs = array_column(
            $export['runtime']['modules'],
            'slug',
        );

        $this->assertContains(
            'crm',
            $moduleSlugs,
        );

        $this->assertContains(
            'santa-buddy',
            $moduleSlugs,
        );
    }

    public function test_export_contains_command_and_query_metadata(): void
    {
        $this->generateExport();

        $runtime = $this->readExport()['runtime'];

        $this->assertSame(
            'Modules\CRM\Commands\CreateCustomerHandler',
            $runtime['commands']['crm']['crm.customer.create'],
        );

        $this->assertSame(
            'Modules\CRM\Queries\FindCustomerHandler',
            $runtime['queries']['crm']['crm.customer.find'],
        );
    }

    public function test_export_contains_runtime_contributions(): void
    {
        $this->generateExport();

        $runtime = $this->readExport()['runtime'];

        foreach ([
            'events',
            'permissions',
            'roles',
            'capabilities',
            'navigation',
            'notifications',
            'scheduled_jobs',
        ] as $key) {
            $this->assertArrayHasKey(
                $key,
                $runtime,
            );
        }
    }

    public function test_export_is_pretty_printed(): void
    {
        $this->generateExport();

        $contents = File::get(
            $this->exportPath,
        );

        $this->assertStringContainsString(
            '    "generated_at"',
            $contents,
        );

        $this->assertStringEndsWith(
            PHP_EOL,
            $contents,
        );
    }

    private function generateExport(): void
    {
        $this->artisan('northpole:export', [
            '--path' => $this->exportPath,
        ])->assertSuccessful();
    }

    /**
     * @return array<string, mixed>
     */
    private function readExport(): array
    {
        return json_decode(
            File::get(
                $this->exportPath,
            ),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}