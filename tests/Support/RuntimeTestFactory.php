<?php

declare(strict_types=1);

namespace Tests\Support;

use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Runtime;

final class RuntimeTestFactory
{
    public static function runtime(
        ?string $modulesPath = null,
        ?ModuleRepository $repository = null,
    ): Runtime {
        $repository ??= new ModuleRepository;

        return new Runtime(
            new ModuleDiscovery(
                new ModuleFinder,
                new ManifestLoader,
                $repository,
            ),
            $repository,
            new ModuleDependencyResolver,
            $modulesPath ?? base_path('modules'),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function manifest(
        array $data = [],
        ?string $path = null,
        ?string $manifestPath = null,
    ): ModuleManifest {
        $slug = self::normaliseSlug(
            $data['slug'] ?? 'runtime-test',
        );

        $path ??= base_path(
            'modules/'.str($slug)
                ->studly()
                ->toString(),
        );

        $manifestPath ??= $path
            .DIRECTORY_SEPARATOR
            .'module.json';

        return new ModuleManifest(
            data: array_replace(
                [
                    'name' => str($slug)
                        ->replace('-', ' ')
                        ->title()
                        ->toString(),
                    'slug' => $slug,
                    'version' => '1.0.0',
                    'enabled' => true,
                    'dependencies' => [],
                ],
                $data,
            ),
            path: $path,
            manifestPath: $manifestPath,
        );
    }

    private static function normaliseSlug(
        mixed $slug,
    ): string {
        if (! is_string($slug)) {
            return 'runtime-test';
        }

        $slug = trim($slug);

        return $slug !== ''
            ? $slug
            : 'runtime-test';
    }
}