<?php

declare(strict_types=1);

namespace Northpole\Lifecycle;

use App\Models\MarketplaceModule;
use App\Models\Organisation;
use App\Models\OrganisationModule;
use App\Support\Tenancy\TenantContext;
use LogicException;
use Northpole\Lifecycle\Enums\LifecycleOperation;

final class ModuleLifecycleManager
{
    public function __construct(
        private readonly LifecyclePipeline $pipeline,
        private readonly TenantContext $tenantContext,
    ) {}

    public function install(
        MarketplaceModule $module
    ): OrganisationModule {
        return $this->execute(
            LifecycleOperation::Install,
            $module
        );
    }

    public function enable(
        MarketplaceModule $module
    ): OrganisationModule {
        return $this->execute(
            LifecycleOperation::Enable,
            $module
        );
    }

    public function disable(
        MarketplaceModule $module
    ): OrganisationModule {
        return $this->execute(
            LifecycleOperation::Disable,
            $module
        );
    }

    public function uninstall(
        MarketplaceModule $module
    ): OrganisationModule {
        return $this->execute(
            LifecycleOperation::Uninstall,
            $module
        );
    }

    private function execute(
        LifecycleOperation $operation,
        MarketplaceModule $module
    ): OrganisationModule {
        $context = new LifecycleContext(
            $operation,
            $module,
            $this->organisation()
        );

        $context = $this->pipeline->run(
            $context
        );

        $installation = $context->installation();

        if ($installation === null) {
            throw new LogicException(
                sprintf(
                    'The [%s] lifecycle operation completed without an installation.',
                    $operation->value
                )
            );
        }

        return $installation;
    }

    private function organisation(): Organisation
    {
        $organisation = $this->tenantContext->organisation();

        if ($organisation === null) {
            throw new LogicException(
                'An active organisation is required for module lifecycle operations.'
            );
        }

        return $organisation;
    }
}
