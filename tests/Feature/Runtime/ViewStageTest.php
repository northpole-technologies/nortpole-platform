<?php

namespace Tests\Feature\Runtime;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Northpole\Runtime\Contracts\ModuleManifestContract;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Lifecycle\BootContext;
use Northpole\Runtime\Lifecycle\ViewStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Runtime;
use Northpole\Runtime\Support\ApplicationAdapter;
use Tests\TestCase;

final class ViewStageTest extends TestCase
{
    public function test_it_registers_a_module_view_namespace(): void
    {
        $stage = new ViewStage(
            new ApplicationAdapter($this->app)
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock(
                    'resources/views'
                )
            )
        );

        $this->assertTrue(
            $this->views()->exists(
                'runtime-test::greeting'
            )
        );
    }

    public function test_it_renders_a_namespaced_module_view(): void
    {
        $stage = new ViewStage(
            new ApplicationAdapter($this->app)
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock(
                    'resources/views'
                )
            )
        );

        $rendered = $this->views()
            ->make(
                'runtime-test::greeting',
                [
                    'name' => 'NorthPole',
                ]
            )
            ->render();

        $this->assertStringContainsString(
            '<h1>Hello NorthPole</h1>',
            $rendered
        );

        $this->assertStringContainsString(
            'Rendered from the Runtime test module.',
            $rendered
        );
    }

    public function test_it_skips_modules_without_views(): void
    {
        $stage = new ViewStage(
            new ApplicationAdapter($this->app)
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock(null)
            )
        );

        $this->assertFalse(
            $this->views()->exists(
                'runtime-test::greeting'
            )
        );
    }

    private function views(): ViewFactory
    {
        /** @var ViewFactory $views */
        $views = $this->app->make('view');

        return $views;
    }

    private function createManifestMock(
        ?string $viewsPath
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
            ->method('viewsPath')
            ->willReturn($viewsPath);

        return $manifest;
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
