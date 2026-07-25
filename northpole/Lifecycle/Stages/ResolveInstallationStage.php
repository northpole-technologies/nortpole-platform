<?php

declare(strict_types=1);

namespace Northpole\Lifecycle\Stages;

use App\Models\OrganisationModule;
use Northpole\Lifecycle\Contracts\LifecycleStageContract;
use Northpole\Lifecycle\Enums\LifecycleOperation;
use Northpole\Lifecycle\LifecycleContext;

final class ResolveInstallationStage implements LifecycleStageContract
{
    public function name(): string
    {
        return 'resolve-installation';
    }

    public function priority(): int
    {
        return 100;
    }

    public function supports(
        LifecycleContext $context
    ): bool {
        return true;
    }

    public function handle(
        LifecycleContext $context
    ): void {
        if (
            $context->operation()
            === LifecycleOperation::Install
        ) {
            $installation = OrganisationModule::withTrashed()
                ->firstOrNew([
                    'organisation_id' => $context
                        ->organisation()
                        ->getKey(),
                    'marketplace_module_id' => $context
                        ->module()
                        ->getKey(),
                ]);

            $context->setInstallation(
                $installation
            );

            return;
        }

        $installation = OrganisationModule::query()
            ->where(
                'marketplace_module_id',
                $context->module()->getKey()
            )
            ->firstOrFail();

        $context->setInstallation(
            $installation
        );
    }
}
