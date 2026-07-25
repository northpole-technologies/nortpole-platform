<?php

namespace Northpole\Runtime\Support;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory as ViewFactory;
use InvalidArgumentException;
use RuntimeException;

final class ApplicationAdapter
{
    public function __construct(
        private readonly Application $application,
    ) {}

    public function application(): Application
    {
        return $this->application;
    }

    public function registerProvider(string $provider): void
    {
        if (! class_exists($provider)) {
            throw new InvalidArgumentException(
                "Module service provider does not exist: {$provider}"
            );
        }

        if (! is_subclass_of($provider, ServiceProvider::class)) {
            throw new InvalidArgumentException(
                'Module provider must extend '
                .ServiceProvider::class
                .": {$provider}"
            );
        }

        $this->application->register($provider);
    }

    public function mergeConfiguration(
        string $key,
        string $path,
    ): void {
        if (! is_file($path)) {
            throw new RuntimeException(
                "Module configuration file does not exist: {$path}"
            );
        }

        $configuration = require $path;

        if (! is_array($configuration)) {
            throw new RuntimeException(
                "Module configuration file must return an array: {$path}"
            );
        }

        /** @var ConfigRepository $repository */
        $repository = $this->application->make('config');

        $existing = $repository->get($key, []);

        if (! is_array($existing)) {
            $existing = [];
        }

        $repository->set(
            $key,
            array_replace_recursive(
                $configuration,
                $existing
            )
        );
    }

    public function registerWebRoutes(string $path): void
    {
        $this->ensureRouteFileExists($path);

        $this->router()
            ->middleware('web')
            ->group($path);

        $this->refreshRouteLookups();
    }

    public function registerApiRoutes(string $path): void
    {
        $this->ensureRouteFileExists($path);

        $this->router()
            ->middleware('api')
            ->prefix('api')
            ->group($path);

        $this->refreshRouteLookups();
    }

    public function registerViews(
        string $namespace,
        string $path,
    ): void {
        $namespace = trim($namespace);

        if ($namespace === '') {
            throw new InvalidArgumentException(
                'Module view namespace cannot be empty.'
            );
        }

        if (! is_dir($path)) {
            throw new RuntimeException(
                "Module views directory does not exist: {$path}"
            );
        }

        $this->viewFactory()
            ->getFinder()
            ->addNamespace($namespace, $path);
    }

    public function registerMigrations(string $path): void
    {
        if (! is_dir($path)) {
            throw new RuntimeException(
                "Module migrations directory does not exist: {$path}"
            );
        }

        $this->migrator()->path($path);
    }

    public function make(string $abstract): mixed
    {
        return $this->application->make($abstract);
    }

    public function bound(string $abstract): bool
    {
        return $this->application->bound($abstract);
    }

    public function basePath(string $path = ''): string
    {
        return $this->application->basePath($path);
    }

    public function configPath(string $path = ''): string
    {
        return $this->application->configPath($path);
    }

    public function resourcePath(string $path = ''): string
    {
        return $this->application->resourcePath($path);
    }

    public function databasePath(string $path = ''): string
    {
        return $this->application->databasePath($path);
    }

    private function router(): Router
    {
        /** @var Router $router */
        $router = $this->application->make('router');

        return $router;
    }

    private function viewFactory(): ViewFactory
    {
        /** @var ViewFactory $factory */
        $factory = $this->application->make('view');

        return $factory;
    }

    private function migrator(): Migrator
    {
        /** @var Migrator $migrator */
        $migrator = $this->application->make('migrator');

        return $migrator;
    }

    private function refreshRouteLookups(): void
    {
        $routes = $this->router()->getRoutes();

        $routes->refreshNameLookups();
        $routes->refreshActionLookups();
    }

    private function ensureRouteFileExists(string $path): void
    {
        if (! is_file($path)) {
            throw new RuntimeException(
                "Module route file does not exist: {$path}"
            );
        }
    }
}
