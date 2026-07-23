<?php

namespace Northpole\Core\Runtime;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class ModuleRuntime
{
    /**
     * Modules that have successfully booted during this request.
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $bootedModules = [];

    public function __construct(
        protected Application $app,
    ) {
    }

    /**
     * Boot every enabled module supplied to the runtime.
     *
     * @param array<int, array<string, mixed>> $modules
     * @return array<string, array<string, mixed>>
     */
    public function bootEnabledModules(array $modules): array
    {
        foreach ($modules as $module) {
            if (! $this->isEnabled($module)) {
                continue;
            }

            $this->boot($module);
        }

        return $this->bootedModules;
    }

    /**
     * Boot one module from its manifest data.
     *
     * @param array<string, mixed> $module
     */
    public function boot(array $module): void
    {
        $this->validateManifest($module);

        $slug = $this->moduleSlug($module);

        if ($this->hasBooted($slug)) {
            return;
        }

        $basePath = $this->resolveBasePath($module);

        $this->registerProvider($module);
        $this->loadConfiguration($module, $basePath);
        $this->loadViews($module, $basePath);
        $this->loadRoutes($module, $basePath);
        $this->loadMigrations($module, $basePath);

        $this->bootedModules[$slug] = [
            ...$module,
            'slug' => $slug,
            'base_path' => $basePath,
            'booted_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Determine whether a module is enabled.
     *
     * A missing enabled value defaults to true for backwards compatibility.
     *
     * @param array<string, mixed> $module
     */
    public function isEnabled(array $module): bool
    {
        return (bool) ($module['enabled'] ?? true);
    }

    public function hasBooted(string $slug): bool
    {
        return array_key_exists($slug, $this->bootedModules);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bootedModules(): array
    {
        return $this->bootedModules;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findBootedModule(string $slug): ?array
    {
        return $this->bootedModules[$slug] ?? null;
    }

    /**
     * @param array<string, mixed> $module
     */
    protected function validateManifest(array $module): void
    {
        if (! isset($module['name']) || ! is_string($module['name'])) {
            throw new InvalidArgumentException(
                'A module manifest must contain a valid name.'
            );
        }

        if (trim($module['name']) === '') {
            throw new InvalidArgumentException(
                'A module manifest name cannot be empty.'
            );
        }

        if (isset($module['provider']) && ! is_string($module['provider'])) {
            throw new InvalidArgumentException(
                "The provider for module [{$module['name']}] must be a class name."
            );
        }

        if (isset($module['routes']) && ! is_array($module['routes'])) {
            throw new InvalidArgumentException(
                "The routes definition for module [{$module['name']}] must be an array."
            );
        }
    }

    /**
     * @param array<string, mixed> $module
     */
    protected function resolveBasePath(array $module): string
    {
        $configuredPath = $module['base_path'] ?? null;

        if (is_string($configuredPath) && trim($configuredPath) !== '') {
            $basePath = $this->normalisePath($configuredPath);
        } else {
            $folder = $module['folder'] ?? $module['name'];

            if (! is_string($folder) || trim($folder) === '') {
                throw new InvalidArgumentException(
                    "Module [{$module['name']}] has no valid folder."
                );
            }

            $basePath = base_path('modules/'.$folder);
        }

        if (! File::isDirectory($basePath)) {
            throw new RuntimeException(
                "Module directory does not exist: {$basePath}"
            );
        }

        return $basePath;
    }

    /**
     * @param array<string, mixed> $module
     */
    protected function registerProvider(array $module): void
    {
        $provider = $module['provider'] ?? null;

        if (! is_string($provider) || trim($provider) === '') {
            return;
        }

        if (! class_exists($provider)) {
            throw new RuntimeException(
                "Module provider [{$provider}] could not be found."
            );
        }

        $this->app->register($provider);
    }

    /**
     * @param array<string, mixed> $module
     */
    protected function loadConfiguration(array $module, string $basePath): void
    {
        $configuration = $module['config'] ?? null;

        if ($configuration === null) {
            return;
        }

        if (is_string($configuration)) {
            $configuration = [
                $this->moduleSlug($module) => $configuration,
            ];
        }

        if (! is_array($configuration)) {
            throw new InvalidArgumentException(
                "The config definition for module [{$module['name']}] is invalid."
            );
        }

        foreach ($configuration as $key => $relativePath) {
            if (! is_string($key) || ! is_string($relativePath)) {
                continue;
            }

            $configPath = $this->joinPath($basePath, $relativePath);

            if (! File::isFile($configPath)) {
                continue;
            }

            $values = require $configPath;

            if (! is_array($values)) {
                throw new RuntimeException(
                    "Module config file [{$configPath}] must return an array."
                );
            }

            $existing = config($key, []);

            config()->set(
                $key,
                array_replace_recursive(
                    is_array($existing) ? $existing : [],
                    $values,
                )
            );
        }
    }

    /**
     * @param array<string, mixed> $module
     */
    protected function loadViews(array $module, string $basePath): void
    {
        $relativePath = $module['views'] ?? null;

        if (! is_string($relativePath) || trim($relativePath) === '') {
            return;
        }

        $viewsPath = $this->joinPath($basePath, $relativePath);

        if (! File::isDirectory($viewsPath)) {
            return;
        }

        view()->addNamespace(
            $this->moduleSlug($module),
            $viewsPath,
        );
    }

    /**
     * @param array<string, mixed> $module
     */
    protected function loadRoutes(array $module, string $basePath): void
    {
        $routes = $module['routes'] ?? [];

        if (! is_array($routes)) {
            return;
        }

        $webRoute = $routes['web'] ?? null;

        if (is_string($webRoute) && trim($webRoute) !== '') {
            $webPath = $this->joinPath($basePath, $webRoute);

            if (File::isFile($webPath)) {
                Route::middleware('web')->group($webPath);
            }
        }

        $apiRoute = $routes['api'] ?? null;

        if (is_string($apiRoute) && trim($apiRoute) !== '') {
            $apiPath = $this->joinPath($basePath, $apiRoute);

            if (File::isFile($apiPath)) {
                Route::middleware('api')
                    ->prefix('api')
                    ->group($apiPath);
            }
        }
    }

    /**
     * @param array<string, mixed> $module
     */
    protected function loadMigrations(array $module, string $basePath): void
    {
        $relativePath = $module['migrations'] ?? null;

        if (! is_string($relativePath) || trim($relativePath) === '') {
            return;
        }

        $migrationPath = $this->joinPath($basePath, $relativePath);

        if (! File::isDirectory($migrationPath)) {
            return;
        }

        $this->app
            ->make('migrator')
            ->path($migrationPath);
    }

    /**
     * @param array<string, mixed> $module
     */
    protected function moduleSlug(array $module): string
    {
        $slug = $module['slug'] ?? null;

        if (is_string($slug) && trim($slug) !== '') {
            return str($slug)->slug()->toString();
        }

        return str((string) $module['name'])
            ->slug()
            ->toString();
    }

    protected function joinPath(string $basePath, string $relativePath): string
    {
        return rtrim($basePath, DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR
            .ltrim(
                str_replace(
                    ['/', '\\'],
                    DIRECTORY_SEPARATOR,
                    $relativePath,
                ),
                DIRECTORY_SEPARATOR,
            );
    }

    protected function normalisePath(string $path): string
    {
        $normalised = str_replace(
            ['/', '\\'],
            DIRECTORY_SEPARATOR,
            trim($path),
        );

        if ($this->isAbsolutePath($normalised)) {
            return $normalised;
        }

        return base_path($normalised);
    }

    protected function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if ($path[0] === DIRECTORY_SEPARATOR) {
            return true;
        }

        return preg_match('/^[A-Za-z]:\\\\/', $path) === 1;
    }
}