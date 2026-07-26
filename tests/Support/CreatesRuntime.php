<?php

declare(strict_types=1);

namespace Tests\Support;

use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Modules\ModuleRepository;
use Northpole\Runtime\Runtime;

trait CreatesRuntime
{
    protected function createRuntime(
        ?string $modulesPath = null,
        ?ModuleRepository $repository = null,
    ): Runtime {
        return RuntimeTestFactory::runtime(
            modulesPath: $modulesPath,
            repository: $repository,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function createManifest(
        array $data = [],
        ?string $path = null,
        ?string $manifestPath = null,
    ): ModuleManifest {
        return RuntimeTestFactory::manifest(
            data: $data,
            path: $path,
            manifestPath: $manifestPath,
        );
    }
}