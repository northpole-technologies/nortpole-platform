<?php

namespace Tests\Feature\Runtime;

use Northpole\Runtime\Contracts\ModuleManifestContract;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Lifecycle\BootContext;
use Northpole\Runtime\Lifecycle\ConfigStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Runtime;
use Northpole\Runtime\Support\ApplicationAdapter;
use Tests\TestCase;

final class ConfigStageTest extends TestCase
{
    public function test_it_loads_module_configuration(): void
    {
        $manifest = $this->createManifestMock([
            'runtime-test' => 'config/runtime-test.php',
        ]);

        $stage = new ConfigStage(
            new ApplicationAdapter($this->app)
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $manifest
            )
        );

        $this->assertTrue(
            config('runtime-test.enabled')
        );

        $this->assertSame(
            'module-default',
            config('runtime-test.message')
        );

        $this->assertSame(
            'module-value',
            config('runtime-test.nested.first')
        );
    }

    public function test_application_configuration_overrides_module_defaults(): void
    {
        config()->set('runtime-test', [
            'message' => 'application-override',

            'nested' => [
                'first' => 'application-value',
            ],
        ]);

        $manifest = $this->createManifestMock([
            'runtime-test' => 'config/runtime-test.php',
        ]);

        $stage = new ConfigStage(
            new ApplicationAdapter($this->app)
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $manifest
            )
        );

        $this->assertSame(
            'application-override',
            config('runtime-test.message')
        );

        $this->assertSame(
            'application-value',
            config('runtime-test.nested.first')
        );

        $this->assertSame(
            'module-default',
            config('runtime-test.nested.second')
        );
    }

    public function test_it_skips_modules_without_configuration(): void
    {
        $manifest = $this->createManifestMock([]);

        $stage = new ConfigStage(
            new ApplicationAdapter($this->app)
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $manifest
            )
        );

        $this->assertNull(
            config('runtime-test')
        );
    }

    private function createManifestMock(
        array $configuration
    ): ModuleManifestContract {
        $manifest = $this->createMock(
            ModuleManifestContract::class
        );

        $manifest
            ->method('slug')
            ->willReturn('runtime-test');

        $manifest
            ->method('path')
            ->willReturn(
                base_path('tests/Fixtures/Runtime')
            );

        $manifest
            ->expects($this->once())
            ->method('configuration')
            ->willReturn($configuration);

        return $manifest;
    }

    private function createRuntime(): Runtime
    {
        $repository = new ModuleRepository();

        return new Runtime(
            new ModuleDiscovery(
                new ModuleFinder(),
                new ManifestLoader(),
                $repository
            ),
            $repository,
            base_path('modules')
        );
    }
}