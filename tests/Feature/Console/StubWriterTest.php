<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Northpole\Console\Support\StubWriter;
use RuntimeException;
use Tests\TestCase;

final class StubWriterTest extends TestCase
{
    private string $testDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDirectory = storage_path(
            'framework/testing/northpole-stub-writer',
        );

        $this->deleteDirectory($this->testDirectory);

        mkdir(
            directory: $this->testDirectory,
            permissions: 0755,
            recursive: true,
        );
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->testDirectory);

        parent::tearDown();
    }

    public function test_it_writes_a_stub_to_the_destination(): void
    {
        $stubPath = $this->testDirectory.'/example.stub';
        $destinationPath = $this->testDirectory.'/generated/example.php';

        file_put_contents(
            $stubPath,
            '<?php class {{Module}}ServiceProvider {}',
        );

        $writer = new StubWriter;

        $writer->write(
            stubPath: $stubPath,
            destinationPath: $destinationPath,
            replacements: [
                'Module' => 'Inventory',
            ],
        );

        $this->assertFileExists($destinationPath);

        $this->assertSame(
            '<?php class InventoryServiceProvider {}',
            file_get_contents($destinationPath),
        );
    }

    public function test_it_creates_missing_destination_directories(): void
    {
        $stubPath = $this->testDirectory.'/example.stub';

        $destinationPath = $this->testDirectory
            .'/one/two/three/generated.php';

        file_put_contents(
            $stubPath,
            'Generated module: {{Module}}',
        );

        $writer = new StubWriter;

        $writer->write(
            stubPath: $stubPath,
            destinationPath: $destinationPath,
            replacements: [
                'Module' => 'CRM',
            ],
        );

        $this->assertFileExists($destinationPath);

        $this->assertSame(
            'Generated module: CRM',
            file_get_contents($destinationPath),
        );
    }

    public function test_it_replaces_multiple_placeholders(): void
    {
        $stubPath = $this->testDirectory.'/example.stub';
        $destinationPath = $this->testDirectory.'/generated.txt';

        file_put_contents(
            $stubPath,
            '{{Module}} uses the {{Namespace}} namespace.',
        );

        $writer = new StubWriter;

        $writer->write(
            stubPath: $stubPath,
            destinationPath: $destinationPath,
            replacements: [
                'Module' => 'HomeDoctor',
                'Namespace' => 'Modules\\HomeDoctor',
            ],
        );

        $this->assertSame(
            'HomeDoctor uses the Modules\\HomeDoctor namespace.',
            file_get_contents($destinationPath),
        );
    }

    public function test_it_refuses_to_overwrite_an_existing_file_by_default(): void
    {
        $stubPath = $this->testDirectory.'/example.stub';
        $destinationPath = $this->testDirectory.'/existing.php';

        file_put_contents($stubPath, 'New content');
        file_put_contents($destinationPath, 'Existing content');

        $writer = new StubWriter;

        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage(
            sprintf(
                'Destination file [%s] already exists.',
                $destinationPath,
            ),
        );

        $writer->write(
            stubPath: $stubPath,
            destinationPath: $destinationPath,
        );
    }

    public function test_it_can_overwrite_an_existing_file_when_allowed(): void
    {
        $stubPath = $this->testDirectory.'/example.stub';
        $destinationPath = $this->testDirectory.'/existing.php';

        file_put_contents($stubPath, 'Replacement content');
        file_put_contents($destinationPath, 'Existing content');

        $writer = new StubWriter;

        $writer->write(
            stubPath: $stubPath,
            destinationPath: $destinationPath,
            overwrite: true,
        );

        $this->assertSame(
            'Replacement content',
            file_get_contents($destinationPath),
        );
    }

    public function test_it_throws_an_exception_when_the_stub_is_missing(): void
    {
        $stubPath = $this->testDirectory.'/missing.stub';
        $destinationPath = $this->testDirectory.'/generated.php';

        $writer = new StubWriter;

        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage(
            sprintf(
                'Stub file [%s] does not exist.',
                $stubPath,
            ),
        );

        $writer->write(
            stubPath: $stubPath,
            destinationPath: $destinationPath,
        );
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

            $path = $directory.DIRECTORY_SEPARATOR.$item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);

                continue;
            }

            unlink($path);
        }

        rmdir($directory);
    }
}
