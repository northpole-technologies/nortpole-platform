<?php

declare(strict_types=1);

namespace Northpole\Console\Support;

use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

final class ModuleScaffolder
{
    /**
     * @var list<string>
     */
    private const DIRECTORIES = [
        'Providers',
        'Http/Controllers',
        'Routes',
        'Resources/Views',
        'Config',
        'Database/Migrations',
        'Tests',
    ];

    /**
     * @var array<string, string>
     */
    private const FILES = [
        'module.json.stub' => 'module.json',
        'provider.stub' => 'Providers/{{Module}}ServiceProvider.php',
        'web.stub' => 'Routes/web.php',
        'api.stub' => 'Routes/api.php',
        'blade.stub' => 'Resources/Views/index.blade.php',
        'readme.stub' => 'README.md',
    ];

    public function __construct(
        private readonly StubWriter $stubWriter,
        private readonly string $modulesPath,
        private readonly string $stubsPath,
    ) {
    }

    public function scaffold(
        string $requestedName,
        bool $overwrite = false,
    ): string {
        $moduleName = $this->normaliseModuleName($requestedName);
        $modulePath = $this->modulePath($moduleName);

        $this->ensureModuleCanBeCreated(
            modulePath: $modulePath,
            overwrite: $overwrite,
        );

        $this->createModuleDirectories($modulePath);

        $replacements = $this->replacementsFor($moduleName);

        foreach (self::FILES as $stub => $destination) {
            $this->stubWriter->write(
                stubPath: $this->stubsPath.DIRECTORY_SEPARATOR.$stub,
                destinationPath: $modulePath.DIRECTORY_SEPARATOR
                    .$this->renderPath($destination, $replacements),
                replacements: $replacements,
                overwrite: $overwrite,
            );
        }

        return $modulePath;
    }

    private function normaliseModuleName(string $requestedName): string
    {
        $requestedName = trim($requestedName);

        if ($requestedName === '') {
            throw new InvalidArgumentException(
                'The module name cannot be empty.',
            );
        }

        if (! preg_match('/^[A-Za-z0-9 _-]+$/', $requestedName)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The module name [%s] contains invalid characters.',
                    $requestedName,
                ),
            );
        }

        $moduleName = Str::studly($requestedName);

        if ($moduleName === '' || ! preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $moduleName)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The module name [%s] is invalid.',
                    $requestedName,
                ),
            );
        }

        return $moduleName;
    }

    private function modulePath(string $moduleName): string
    {
        return rtrim(
            $this->modulesPath,
            '\\/',
        ).DIRECTORY_SEPARATOR.$moduleName;
    }

    private function ensureModuleCanBeCreated(
        string $modulePath,
        bool $overwrite,
    ): void {
        if (is_dir($modulePath) && ! $overwrite) {
            throw new RuntimeException(
                sprintf(
                    'Module directory [%s] already exists.',
                    $modulePath,
                ),
            );
        }

        if (is_file($modulePath)) {
            throw new RuntimeException(
                sprintf(
                    'A file already exists at module path [%s].',
                    $modulePath,
                ),
            );
        }
    }

    private function createModuleDirectories(string $modulePath): void
    {
        $directories = array_merge(
            [$modulePath],
            array_map(
                static fn (string $directory): string =>
                    $modulePath.DIRECTORY_SEPARATOR.$directory,
                self::DIRECTORIES,
            ),
        );

        foreach ($directories as $directory) {
            if (is_dir($directory)) {
                continue;
            }

            $created = mkdir(
                directory: $directory,
                permissions: 0755,
                recursive: true,
            );

            if (! $created && ! is_dir($directory)) {
                throw new RuntimeException(
                    sprintf(
                        'Unable to create module directory [%s].',
                        $directory,
                    ),
                );
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private function replacementsFor(string $moduleName): array
    {
        return [
            'Module' => $moduleName,
            'ModuleSlug' => Str::kebab($moduleName),
            'ModuleTitle' => Str::headline($moduleName),
            'ModuleNamespace' => 'Modules\\'.$moduleName,
        ];
    }

    /**
     * @param array<string, string> $replacements
     */
    private function renderPath(
        string $path,
        array $replacements,
    ): string {
        foreach ($replacements as $placeholder => $replacement) {
            $path = str_replace(
                search: '{{'.$placeholder.'}}',
                replace: $replacement,
                subject: $path,
            );
        }

        return str_replace(
            ['/', '\\'],
            DIRECTORY_SEPARATOR,
            $path,
        );
    }
}
