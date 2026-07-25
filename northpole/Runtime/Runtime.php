<?php

declare(strict_types=1);

namespace Northpole\Runtime;

use Northpole\Runtime\Discovery\ModuleDiscovery;
use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Modules\ModuleDependencyResolver;
use Northpole\Runtime\Modules\ModuleRepository;

final class Runtime
{
    public function __construct(
        private readonly ModuleDiscovery $discovery,
        private readonly ModuleRepository $repository,
        private readonly ModuleDependencyResolver $dependencyResolver,
        private readonly string $modulesPath,
    ) {}

    public function discover(): self
    {
        $this->discovery->discover(
            $this->modulesPath,
        );

        return $this;
    }

    /**
     * @return array<string, ModuleManifest>
     */
    public function modules(): array
    {
        return $this->repository->all();
    }

    /**
     * Returns enabled modules in dependency-safe boot order.
     *
     * @return array<string, ModuleManifest>
     */
    public function enabledModules(): array
    {
        return $this->dependencyResolver->resolve(
            $this->repository->all(),
        );
    }

    public function module(string $slug): ?ModuleManifest
    {
        return $this->repository->get($slug);
    }

    public function has(string $slug): bool
    {
        return $this->repository->has($slug);
    }

    public function count(): int
    {
        return $this->repository->count();
    }

    public function modulesPath(): string
    {
        return $this->modulesPath;
    }
}
