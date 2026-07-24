<?php

namespace Northpole\Runtime\Lifecycle;

use Northpole\Runtime\Contracts\ModuleManifestContract;
use RuntimeException;

final class ModuleResources
{
    private bool $providerResolved = false;

    private ?string $resolvedProvider = null;

    /**
     * @var array<string, string>|null
     */
    private ?array $resolvedConfiguration = null;

    /**
     * @var array<string, string>|null
     */
    private ?array $resolvedRoutes = null;

    private bool $viewsResolved = false;

    private ?string $resolvedViewsPath = null;

    private bool $migrationsResolved = false;

    private ?string $resolvedMigrationsPath = null;

    public function __construct(
        private readonly ModuleManifestContract $module,
    ) {
    }

    public function module(): ModuleManifestContract
    {
        return $this->module;
    }

    public function hasProvider(): bool
    {
        $this->resolveProvider();

        return $this->resolvedProvider !== null;
    }

    public function provider(): string
    {
        $this->resolveProvider();

        if ($this->resolvedProvider === null) {
            throw new RuntimeException(
                sprintf(
                    'Module "%s" does not define a service provider.',
                    $this->module->slug()
                )
            );
        }

        return $this->resolvedProvider;
    }

    public function hasConfiguration(): bool
    {
        return $this->configuration() !== [];
    }

    /**
     * @return array<string, string>
     */
    public function configuration(): array
    {
        if ($this->resolvedConfiguration !== null) {
            return $this->resolvedConfiguration;
        }

        $configuration = [];

        foreach ($this->module->configuration() as $key => $relativePath) {
            $key = trim((string) $key);
            $relativePath = trim((string) $relativePath);

            if ($key === '' || $relativePath === '') {
                continue;
            }

            $configuration[$key] = $this->absolutePath($relativePath);
        }

        return $this->resolvedConfiguration = $configuration;
    }

    public function configurationPath(string $key): string
    {
        $configuration = $this->configuration();

        if (! isset($configuration[$key])) {
            throw new RuntimeException(
                sprintf(
                    'Module "%s" does not define configuration key "%s".',
                    $this->module->slug(),
                    $key
                )
            );
        }

        return $configuration[$key];
    }

    public function hasRoutes(): bool
    {
        return $this->routes() !== [];
    }

    public function hasWebRoutes(): bool
    {
        return isset($this->routes()['web']);
    }

    public function hasApiRoutes(): bool
    {
        return isset($this->routes()['api']);
    }

    /**
     * @return array<string, string>
     */
    public function routes(): array
    {
        if ($this->resolvedRoutes !== null) {
            return $this->resolvedRoutes;
        }

        $routes = [];

        foreach ($this->module->routes() as $type => $relativePath) {
            $type = strtolower(trim((string) $type));
            $relativePath = trim((string) $relativePath);

            if ($type === '' || $relativePath === '') {
                continue;
            }

            $routes[$type] = $this->absolutePath($relativePath);
        }

        return $this->resolvedRoutes = $routes;
    }

    public function webRoutesPath(): string
    {
        return $this->routePath('web');
    }

    public function apiRoutesPath(): string
    {
        return $this->routePath('api');
    }

    public function routePath(string $type): string
    {
        $type = strtolower(trim($type));
        $routes = $this->routes();

        if (! isset($routes[$type])) {
            throw new RuntimeException(
                sprintf(
                    'Module "%s" does not define "%s" routes.',
                    $this->module->slug(),
                    $type
                )
            );
        }

        return $routes[$type];
    }

    public function hasViews(): bool
    {
        $this->resolveViewsPath();

        return $this->resolvedViewsPath !== null;
    }

    public function viewsPath(): string
    {
        $this->resolveViewsPath();

        if ($this->resolvedViewsPath === null) {
            throw new RuntimeException(
                sprintf(
                    'Module "%s" does not define a views directory.',
                    $this->module->slug()
                )
            );
        }

        return $this->resolvedViewsPath;
    }

    public function viewsNamespace(): string
    {
        return $this->module->slug();
    }

    public function hasMigrations(): bool
    {
        $this->resolveMigrationsPath();

        return $this->resolvedMigrationsPath !== null;
    }

    public function migrationsPath(): string
    {
        $this->resolveMigrationsPath();

        if ($this->resolvedMigrationsPath === null) {
            throw new RuntimeException(
                sprintf(
                    'Module "%s" does not define a migrations directory.',
                    $this->module->slug()
                )
            );
        }

        return $this->resolvedMigrationsPath;
    }

    private function resolveProvider(): void
    {
        if ($this->providerResolved) {
            return;
        }

        $provider = $this->module->provider();

        $provider = is_string($provider)
            ? trim($provider)
            : '';

        $this->resolvedProvider = $provider !== ''
            ? $provider
            : null;

        $this->providerResolved = true;
    }

    private function resolveViewsPath(): void
    {
        if ($this->viewsResolved) {
            return;
        }

        $viewsPath = $this->module->viewsPath();

        $viewsPath = is_string($viewsPath)
            ? trim($viewsPath)
            : '';

        $this->resolvedViewsPath = $viewsPath !== ''
            ? $this->absolutePath($viewsPath)
            : null;

        $this->viewsResolved = true;
    }

    private function resolveMigrationsPath(): void
    {
        if ($this->migrationsResolved) {
            return;
        }

        $migrationsPath = $this->module->migrationsPath();

        $migrationsPath = is_string($migrationsPath)
            ? trim($migrationsPath)
            : '';

        $this->resolvedMigrationsPath = $migrationsPath !== ''
            ? $this->absolutePath($migrationsPath)
            : null;

        $this->migrationsResolved = true;
    }

    private function absolutePath(string $path): string
    {
        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return rtrim(
            $this->module->path(),
            DIRECTORY_SEPARATOR
        ).DIRECTORY_SEPARATOR.ltrim(
            str_replace(
                ['/', '\\'],
                DIRECTORY_SEPARATOR,
                $path
            ),
            DIRECTORY_SEPARATOR
        );
    }

    private function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if ($path[0] === '/' || $path[0] === '\\') {
            return true;
        }

        return preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1;
    }
}