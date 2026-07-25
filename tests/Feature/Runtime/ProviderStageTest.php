<?php

declare(strict_types=1);

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
use Tests\Fixtures\Runtime\SecondaryRecordingServiceProvider;
use Tests\TestCase;

final class ProviderStageTest extends TestCase
{
    public function test_it_registers_a_module_service_provider(): void
    {
        $manifest = $this->createManifest([
            RecordingServiceProvider::class,
        ]);

        $this->bootManifest($manifest);

        $this->assertTrue(
            $this->app->bound(RecordingServiceProvider::BINDING)
        );

        $this->assertSame(
            'provider-registered',
            $this->app->make(RecordingServiceProvider::BINDING)
        );
    }

    public function test_it_registers_multiple_module_service_providers(): void
    {
        $manifest = $this->createManifest([
            RecordingServiceProvider::class,
            SecondaryRecordingServiceProvider::class,
        ]);

        $this->bootManifest($manifest);

        $this->assertSame(
            'provider-registered',
            $this->app->make(RecordingServiceProvider::BINDING)
        );

        $this->assertSame(
            'secondary-provider-registered',
            $this->app->make(
                SecondaryRecordingServiceProvider::BINDING
            )
        );
    }

    public function test_it_registers_duplicate_provider_names_once(): void
    {
        $manifest = $this->createManifest([
            RecordingServiceProvider::class,
            RecordingServiceProvider::class,
        ]);

        $this->bootManifest($manifest);

        $this->assertSame(
            'provider-registered',
            $this->app->make(RecordingServiceProvider::BINDING)
        );
    }

    public function test_it_skips_a_module_without_service_providers(): void
    {
        $manifest = $this->createManifest([]);

        $this->bootManifest($manifest);

        $this->assertFalse(
            $this->app->bound(RecordingServiceProvider::BINDING)
        );

        $this->assertFalse(
            $this->app->bound(
                SecondaryRecordingServiceProvider::BINDING
            )
        );
    }

    /**
     * @param  array<int, string>  $providers
     */
    private function createManifest(
        array $providers
    ): ModuleManifestContract {
        $manifest = $this->createMock(
            ModuleManifestContract::class
        );

        $manifest
            ->expects($this->once())
            ->method('providers')
            ->willReturn($providers);

        return $manifest;
    }

    private function bootManifest(
        ModuleManifestContract $manifest
    ): void {
        $stage = new ProviderStage(
            new ApplicationAdapter($this->app)
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $manifest
            )
        );
    }

    private function createRuntime(): Runtime
    {
        $repository = new ModuleRepository;

        return new Runtime(
            new ModuleDiscovery(
                new ModuleFinder,
                new ManifestLoader,
                $repository
            ),
            $repository,
            new ModuleDependencyResolver,
            base_path('modules')
        );
    }
}