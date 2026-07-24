<?php

namespace Northpole\Runtime\Discovery;

use Northpole\Runtime\Manifest\ManifestLoader;
use Northpole\Runtime\Modules\ModuleFinder;
use Northpole\Runtime\Modules\ModuleRepository;

final class ModuleDiscovery
{
    public function __construct(
        private readonly ModuleFinder $finder,
        private readonly ManifestLoader $loader,
        private readonly ModuleRepository $repository,
    ) {
    }

    public function discover(string $modulesPath): ModuleRepository
    {
        foreach ($this->finder->find($modulesPath) as $modulePath) {
            $this->repository->add(
                $this->loader->load($modulePath)
            );
        }

        return $this->repository;
    }
}