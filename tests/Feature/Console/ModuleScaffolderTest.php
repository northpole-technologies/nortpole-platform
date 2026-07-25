<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use InvalidArgumentException;
use Northpole\Console\Support\ModuleScaffolder;
use Northpole\Console\Support\StubWriter;
use RuntimeException;
use Tests\TestCase;

final class ModuleScaffolderTest extends TestCase
{
    private string $testDirectory;

    private string $modulesPath;

    private string $stubsPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDirectory = storage_path(
            'framework/testing/northpole-module-scaffolder',
        );

        $this->modulesPath = $this->testDirectory.'/modules';
        $this->stubsPath = $this->testDirectory.'/stubs';

        $this->deleteDirectory($this->testDirectory);

        mkdir(
            directory: $this->modulesPath,
            permissions: 0755,
            recursive: true,
        );

        mkdir(
            directory: $this->stubsPath,
            permissions: 0755,
            recursive: true,
        );

        $this->createTestStubs();
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->testDirectory);

        parent::tearDown();
    }

    public function test_it_creates_a_complete_module_structure(): void
    {
        $scaffolder = $this->scaffolder();

        $modulePath = $scaffolder->scaffold('Inventory');

        $this->assertSame(
            str_replace(
                '\\',
                '/',
                $this->modulesPath.'/Inventory',
            ),
            str_replace(
                '\\',
                '/',
                $modulePath,
            ),
        );

        $expectedDirectories = [
            'Providers',
            'Http/Controllers',
            'Routes',
            'Resources/Views',
            'Config',
            'Database/Migrations',
            'Tests',
        ];

        foreach ($expectedDirectories as $directory) {
            $this->assertDirectoryExists(
                $modulePath
                    .DIRECTORY_SEPARATOR
                    .str_replace(
                        '/',
                        DIRECTORY_SEPARATOR,
                        $directory,
                    ),
            );
        }

        $expectedFiles = [
            'module.json',
            'Providers/InventoryServiceProvider.php',
            'Routes/web.php',
            'Routes/api.php',
            'Resources/Views/index.blade.php',
            'README.md',
        ];

        foreach ($expectedFiles as $file) {
            $this->assertFileExists(
                $modulePath
                    .DIRECTORY_SEPARATOR
                    .str_replace(
                        '/',
                        DIRECTORY_SEPARATOR,
                        $file,
                    ),
            );
        }
    }

    public function test_it_normalises_the_requested_module_name(): void
    {
        $scaffolder = $this->scaffolder();

        $modulePath = $scaffolder->scaffold(
            'customer support',
        );

        $this->assertStringEndsWith(
            DIRECTORY_SEPARATOR.'CustomerSupport',
            $modulePath,
        );

        $providerPath = $modulePath
            .DIRECTORY_SEPARATOR.'Providers'
            .DIRECTORY_SEPARATOR.'CustomerSupportServiceProvider.php';

        $this->assertFileExists($providerPath);

        $this->assertStringContainsString(
            'CustomerSupport',
            (string) file_get_contents($providerPath),
        );
    }

    public function test_it_replaces_all_supported_placeholders(): void
    {
        $scaffolder = $this->scaffolder();

        $modulePath = $scaffolder->scaffold('Home Doctor');

        $manifest = (string) file_get_contents(
            $modulePath.DIRECTORY_SEPARATOR.'module.json',
        );

        $this->assertStringContainsString(
            '"name": "HomeDoctor"',
            $manifest,
        );

        $this->assertStringContainsString(
            '"slug": "home-doctor"',
            $manifest,
        );

        $this->assertStringContainsString(
            '"title": "Home Doctor"',
            $manifest,
        );

        $provider = (string) file_get_contents(
            $modulePath
                .DIRECTORY_SEPARATOR.'Providers'
                .DIRECTORY_SEPARATOR.'HomeDoctorServiceProvider.php',
        );

        $this->assertStringContainsString(
            'namespace Modules\\HomeDoctor\\Providers;',
            $provider,
        );
    }

    public function test_it_refuses_to_replace_an_existing_module_by_default(): void
    {
        $scaffolder = $this->scaffolder();

        $scaffolder->scaffold('Inventory');

        $modulePath = $this->modulesPath
            .DIRECTORY_SEPARATOR.'Inventory';

        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage(
            sprintf(
                'Module directory [%s] already exists.',
                $modulePath,
            ),
        );

        $scaffolder->scaffold('Inventory');
    }

    public function test_it_can_overwrite_generated_files_when_requested(): void
    {
        $scaffolder = $this->scaffolder();

        $modulePath = $scaffolder->scaffold('Inventory');

        $readmePath = $modulePath
            .DIRECTORY_SEPARATOR.'README.md';

        file_put_contents(
            $readmePath,
            'Changed content',
        );

        $scaffolder->scaffold(
            requestedName: 'Inventory',
            overwrite: true,
        );

        $this->assertSame(
            '# Inventory',
            file_get_contents($readmePath),
        );
    }

    public function test_it_rejects_an_empty_module_name(): void
    {
        $scaffolder = $this->scaffolder();

        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage(
            'The module name cannot be empty.',
        );

        $scaffolder->scaffold('   ');
    }

    public function test_it_rejects_unsafe_module_names(): void
    {
        $scaffolder = $this->scaffolder();

        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage(
            'The module name [../../Dangerous] contains invalid characters.',
        );

        $scaffolder->scaffold('../../Dangerous');
    }

    private function scaffolder(): ModuleScaffolder
    {
        return new ModuleScaffolder(
            stubWriter: new StubWriter,
            modulesPath: $this->modulesPath,
            stubsPath: $this->stubsPath,
        );
    }

    private function createTestStubs(): void
    {
        $stubs = [
            'module.json.stub' => <<<'STUB'
{
    "name": "{{Module}}",
    "slug": "{{ModuleSlug}}",
    "title": "{{ModuleTitle}}"
}
STUB,
            'provider.stub' => <<<'STUB'
<?php

namespace {{ModuleNamespace}}\Providers;

final class {{Module}}ServiceProvider
{
}
STUB,
            'web.stub' => <<<'STUB'
<?php

// {{ModuleTitle}} web routes.
STUB,
            'api.stub' => <<<'STUB'
<?php

// {{ModuleTitle}} API routes.
STUB,
            'blade.stub' => <<<'STUB'
<h1>{{ModuleTitle}}</h1>
STUB,
            'readme.stub' => <<<'STUB'
# {{ModuleTitle}}
STUB,
        ];

        foreach ($stubs as $filename => $contents) {
            file_put_contents(
                $this->stubsPath.DIRECTORY_SEPARATOR.$filename,
                $contents,
            );
        }
    }

    private function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = scandir($directory);

        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory
                .DIRECTORY_SEPARATOR
                .$item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);

                continue;
            }

            unlink($path);
        }

        rmdir($directory);
    }
}
