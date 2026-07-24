<?php

declare(strict_types=1);

namespace Northpole\Lifecycle\Stages;

use App\Models\MarketplaceModule;
use App\Models\OrganisationModule;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Collection;
use LogicException;
use Northpole\Lifecycle\Contracts\LifecycleStageContract;
use Northpole\Lifecycle\Enums\LifecycleOperation;
use Northpole\Lifecycle\Exceptions\ModuleDependencyValidationException;
use Northpole\Lifecycle\Exceptions\ModuleManifestNotFoundException;
use Northpole\Lifecycle\LifecycleContext;
use Northpole\Runtime\Manifest\ModuleManifest;
use Northpole\Runtime\Runtime;

final class ValidateDependenciesStage implements LifecycleStageContract
{
    public function __construct(
        private readonly Runtime $runtime,
        private readonly TenantContext $tenantContext,
    ) {
    }

    public function name(): string
    {
        return 'validate-dependencies';
    }

    public function priority(): int
    {
        return 175;
    }

    public function supports(
        LifecycleContext $context
    ): bool {
        return in_array(
            $context->operation(),
            [
                LifecycleOperation::Install,
                LifecycleOperation::Enable,
                LifecycleOperation::Disable,
                LifecycleOperation::Uninstall,
            ],
            true
        );
    }

    public function handle(
        LifecycleContext $context
    ): void {
        match ($context->operation()) {
            LifecycleOperation::Install,
            LifecycleOperation::Enable => $this->validateRequirements(
                $context
            ),

            LifecycleOperation::Disable => $this->validateDependants(
                $context,
                enabledOnly: true
            ),

            LifecycleOperation::Uninstall => $this->validateDependants(
                $context,
                enabledOnly: false
            ),

            default => null,
        };
    }

    private function validateRequirements(
        LifecycleContext $context
    ): void {
        $manifest = $this->manifest($context);
        $moduleSlug = $manifest->slug();

        $dependencies = $manifest->dependencies();

        sort($dependencies);

        foreach ($dependencies as $dependencySlug) {
            if ($dependencySlug === $moduleSlug) {
                throw ModuleDependencyValidationException::selfReference(
                    $moduleSlug
                );
            }

            $dependencyModule = MarketplaceModule::query()
                ->where('key', $dependencySlug)
                ->where('is_active', true)
                ->first();

            if ($dependencyModule === null) {
                throw ModuleDependencyValidationException::unavailable(
                    $moduleSlug,
                    $dependencySlug
                );
            }

            $installation = $this->installation(
                $context,
                $dependencyModule
            );

            if ($installation === null) {
                throw ModuleDependencyValidationException::notInstalled(
                    $moduleSlug,
                    $dependencySlug
                );
            }

            if (! $installation->is_enabled) {
                throw ModuleDependencyValidationException::notEnabled(
                    $moduleSlug,
                    $dependencySlug
                );
            }
        }
    }

    private function validateDependants(
        LifecycleContext $context,
        bool $enabledOnly
    ): void {
        $targetManifest = $this->manifest($context);
        $targetSlug = $targetManifest->slug();

        foreach (
            $this->installedModules(
                $context,
                $enabledOnly
            ) as $installation
        ) {
            $marketplaceModule = $installation->marketplaceModule;

            if ($marketplaceModule === null) {
                continue;
            }

            if (
                $marketplaceModule->getKey()
                === $context->module()->getKey()
            ) {
                continue;
            }

            $dependantManifest = $this->runtime->module(
                (string) $marketplaceModule->key
            );

            if ($dependantManifest === null) {
                throw ModuleManifestNotFoundException::forModule(
                    (string) $marketplaceModule->key
                );
            }

            if (
                ! in_array(
                    $targetSlug,
                    $dependantManifest->dependencies(),
                    true
                )
            ) {
                continue;
            }

            throw ModuleDependencyValidationException::requiredBy(
                $targetSlug,
                $dependantManifest->slug(),
                $context->operation() === LifecycleOperation::Disable
                    ? 'disabled'
                    : 'uninstalled'
            );
        }
    }

    private function manifest(
        LifecycleContext $context
    ): ModuleManifest {
        $manifest = $context->manifest();

        if ($manifest === null) {
            throw new LogicException(
                'The module manifest must be resolved before dependency validation.'
            );
        }

        return $manifest;
    }

    private function installation(
        LifecycleContext $context,
        MarketplaceModule $module
    ): ?OrganisationModule {
        return $this->tenantContext->withoutTenancy(
            fn (): ?OrganisationModule => OrganisationModule::query()
                ->where(
                    'organisation_id',
                    $context->organisation()->getKey()
                )
                ->where(
                    'marketplace_module_id',
                    $module->getKey()
                )
                ->first()
        );
    }

    /**
     * @return Collection<int, OrganisationModule>
     */
    private function installedModules(
        LifecycleContext $context,
        bool $enabledOnly
    ): Collection {
        return $this->tenantContext->withoutTenancy(
            function () use (
                $context,
                $enabledOnly
            ): Collection {
                return OrganisationModule::query()
                    ->with('marketplaceModule')
                    ->where(
                        'organisation_id',
                        $context->organisation()->getKey()
                    )
                    ->when(
                        $enabledOnly,
                        fn ($query) => $query->where(
                            'is_enabled',
                            true
                        )
                    )
                    ->get();
            }
        );
    }
}