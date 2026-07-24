<?php

namespace Tests\Unit\Runtime;

use Illuminate\Support\Facades\File;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Tests\TestCase;

class ModuleFoundationTest extends TestCase
{
    private string $temporaryModulesPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryModulesPath = storage_path(
            'framework/testing/northpole-runtime-v2'
        );

        File::deleteDirectory($this->temporaryModulesPath);

        File::makeDirectory(
            $this->temporaryModulesPath,
            0755,
            true
        );
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->temporaryModulesPath);

        parent::tearDown();
    }

    public function test_module_foundation_discovers_loads_and_stores_a_module(): void
    {
        $modulePath = $this->temporaryModulesPath
            .DIRECTORY_SEPARATOR
            .'SantaBuddy';

        File::makeDirectory(
            $modulePath,
            0755,
            true
        );

        File::put(
            $modulePath.DIRECTORY_SEPARATOR.'module.json',
            json_encode(
                [
                    'name' => 'SantaBuddy',
                    'slug' => 'santa-buddy',
                    'version' => '1.0.0',
                    'description' => 'NorthPole Santa assistant module.',
                    'provider' => 'Modules\\SantaBuddy\\SantaBuddyServiceProvider',
                    'enabled' => true,
                    'dependencies' => [
                        'notifications',
                    ],
                    'routes' => [
                        'web' => 'routes/web.php',
                        'api' => 'routes/api.php',
                    ],
                    'views' => 'resources/views',
                    'migrations' => 'database/migrations',
                    'config' => [
                        'santa-buddy' => 'config/santa-buddy.php',
                    ],
                    'permissions' => [
                        'santa-buddy.view',
                        'santa-buddy.manage',
                    ],
                    'navigation' => [
                        [
                            'label' => 'SantaBuddy',
                            'route' => 'santa-buddy.index',
                        ],
                    ],
                    'capabilities' => [
                        'chat',
                        'gift-planning',
                    ],
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );

        $finder = new ModuleFinder();
        $loader = new ManifestLoader();
        $repository = new ModuleRepository();

        $moduleDirectories = $finder->find(
            $this->temporaryModulesPath
        );

        $this->assertCount(1, $moduleDirectories);
        $this->assertSame($modulePath, $moduleDirectories[0]);

        foreach ($moduleDirectories as $directory) {
            $repository->add(
                $loader->load($directory)
            );
        }

        $this->assertSame(1, $repository->count());
        $this->assertTrue($repository->has('santa-buddy'));

        $module = $repository->get('santa-buddy');

        $this->assertInstanceOf(ModuleManifest::class, $module);
        $this->assertSame('SantaBuddy', $module->name());
        $this->assertSame('santa-buddy', $module->slug());
        $this->assertSame('1.0.0', $module->version());
        $this->assertTrue($module->enabled());

        $this->assertSame(
            ['notifications'],
            $module->dependencies()
        );

        $this->assertSame($modulePath, $module->path());

        $this->assertSame(
            $modulePath.DIRECTORY_SEPARATOR.'module.json',
            $module->manifestPath()
        );

        $this->assertSame(
            [
                'web' => 'routes/web.php',
                'api' => 'routes/api.php',
            ],
            $module->routes()
        );

        $this->assertSame(
            ['santa-buddy' => 'config/santa-buddy.php'],
            $module->configuration()
        );

        $this->assertArrayHasKey(
            'santa-buddy',
            $repository->enabled()
        );
    }
}