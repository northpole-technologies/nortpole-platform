<?php

namespace Tests\Feature\Runtime;

use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Routing\Router;
use Northpole\Runtime\Contracts\ModuleManifestContract;
use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Lifecycle\BootContext;
use Northpole\Runtime\Lifecycle\RouteStage;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Runtime;
use Northpole\Runtime\Support\ApplicationAdapter;
use Tests\TestCase;

final class RouteStageTest extends TestCase
{
    public function test_it_registers_web_routes(): void
    {
        $stage = new RouteStage(
            new ApplicationAdapter($this->app)
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    'web' => 'routes/web.php',
                ])
            )
        );

        $route = $this->router()
            ->getRoutes()
            ->getByName('runtime-test.web');

        $this->assertInstanceOf(
            LaravelRoute::class,
            $route
        );

        $this->assertSame(
            'runtime-test/web',
            $route->uri()
        );

        $this->assertContains(
            'web',
            $route->gatherMiddleware()
        );

        $this->get('/runtime-test/web')
            ->assertOk()
            ->assertJson([
                'route' => 'web',
            ]);
    }

    public function test_it_registers_api_routes_with_api_prefix(): void
    {
        $stage = new RouteStage(
            new ApplicationAdapter($this->app)
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    'api' => 'routes/api.php',
                ])
            )
        );

        $route = $this->router()
            ->getRoutes()
            ->getByName('runtime-test.api');

        $this->assertInstanceOf(
            LaravelRoute::class,
            $route
        );

        $this->assertSame(
            'api/runtime-test/api',
            $route->uri()
        );

        $this->assertContains(
            'api',
            $route->gatherMiddleware()
        );

        $this->getJson('/api/runtime-test/api')
            ->assertOk()
            ->assertJson([
                'route' => 'api',
            ]);
    }

    public function test_it_registers_web_and_api_routes_together(): void
    {
        $stage = new RouteStage(
            new ApplicationAdapter($this->app)
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([
                    'web' => 'routes/web.php',
                    'api' => 'routes/api.php',
                ])
            )
        );

        $routes = $this->router()->getRoutes();

        $this->assertNotNull(
            $routes->getByName('runtime-test.web')
        );

        $this->assertNotNull(
            $routes->getByName('runtime-test.api')
        );
    }

    public function test_it_skips_modules_without_routes(): void
    {
        $stage = new RouteStage(
            new ApplicationAdapter($this->app)
        );

        $stage->boot(
            new BootContext(
                $this->createRuntime(),
                $this->createManifestMock([])
            )
        );

        $routes = $this->router()->getRoutes();

        $this->assertNull(
            $routes->getByName('runtime-test.web')
        );

        $this->assertNull(
            $routes->getByName('runtime-test.api')
        );
    }

    private function router(): Router
    {
        /** @var Router $router */
        $router = $this->app->make('router');

        return $router;
    }

    private function createManifestMock(
        array $routes
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
            ->method('routes')
            ->willReturn($routes);

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
