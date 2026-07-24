<?php

namespace Tests\Feature\Runtime;

use Northpole\Runtime\Contracts\ModuleManifestContract;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Lifecycle\BootContext;
use Northpole\Runtime\Lifecycle\ProviderStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Runtime;
use Northpole\Runtime\Support\ApplicationAdapter;
use Tests\Fixtures\Runtime\RecordingServiceProvider;
use Tests\TestCase;

final class ProviderStageTest extends TestCase
{
    public function test_it_registers_a_module_service_provider(): void
    {
        $runtime = $this->createRuntime();

        $manifest = $this->createMock(ModuleManifestContract::class);

        $manifest
            ->expects($this->once())
            ->method('provider')
            ->willReturn(RecordingServiceProvider::class);

        $stage = new ProviderStage(
            new ApplicationAdapter($this->app)
        );

        $stage->boot(
            new BootContext(
                $runtime,
                $manifest
            )
        );

        $this->assertTrue(
            $this->app->bound(RecordingServiceProvider::BINDING)
        );

        $this->assertSame(
            'provider-registered',
            $this->app->make(RecordingServiceProvider::BINDING)
        );
    }

    public function test_it_skips_a_module_without_a_service_provider(): void
    {
        $runtime = $this->createRuntime();

        $manifest = $this->createMock(ModuleManifestContract::class);

        $manifest
            ->expects($this->once())
            ->method('provider')
            ->willReturn(null);

        $stage = new ProviderStage(
            new ApplicationAdapter($this->app)
        );

        $stage->boot(
            new BootContext(
                $runtime,
                $manifest
            )
        );

        $this->assertFalse(
            $this->app->bound(RecordingServiceProvider::BINDING)
        );
    }

    public function test_it_skips_an_empty_service_provider_name(): void
    {
        $runtime = $this->createRuntime();

        $manifest = $this->createMock(ModuleManifestContract::class);

        $manifest
            ->expects($this->once())
            ->method('provider')
            ->willReturn('   ');

        $stage = new ProviderStage(
            new ApplicationAdapter($this->app)
        );

        $stage->boot(
            new BootContext(
                $runtime,
                $manifest
            )
        );

        $this->assertFalse(
            $this->app->bound(RecordingServiceProvider::BINDING)
        );
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
            new ModuleDependencyResolver(),
            base_path('modules')
        );
    }
}
