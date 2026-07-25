<?php

declare(strict_types=1);

namespace Northpole\Lifecycle\Stages;

use LogicException;
use Northpole\Lifecycle\Contracts\LifecycleStageContract;
use Northpole\Lifecycle\Enums\LifecycleOperation;
use Northpole\Lifecycle\LifecycleContext;
use Northpole\Runtime\Events\ModuleEventBus;

final class InstallStage implements LifecycleStageContract
{
    public function __construct(
        private readonly ModuleEventBus $eventBus,
    ) {
    }

    public function name(): string
    {
        return 'install-module';
    }

    public function priority(): int
    {
        return 200;
    }

    public function supports(
        LifecycleContext $context
    ): bool {
        return $context->operation()
            === LifecycleOperation::Install;
    }

    public function handle(
        LifecycleContext $context
    ): void {
        $installation = $context->installation();

        if ($installation === null) {
            throw new LogicException(
                'The module installation must be resolved before installation.'
            );
        }

        if ($installation->trashed()) {
            $installation->restore();
        }

        $installation->fill([
            'organisation_id' => $context
                ->organisation()
                ->getKey(),
            'marketplace_module_id' => $context
                ->module()
                ->getKey(),
            'is_enabled' => true,
            'installed_at' => now(),
        ]);

        $installation->save();

        $installation = $installation->fresh();

        $context->setInstallation(
            $installation
        );

        $this->eventBus->publish(
            'module.installed',
            $context->module()->name,
            [
                'installation_id' => $installation->getKey(),
                'organisation_id' => $installation->organisation_id,
                'marketplace_module_id' => $installation->marketplace_module_id,
            ],
        );
    }
}

