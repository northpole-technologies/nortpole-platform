<?php

declare(strict_types=1);

namespace Northpole\Lifecycle\Stages;

use LogicException;
use Northpole\Lifecycle\Contracts\LifecycleStageContract;
use Northpole\Lifecycle\Enums\LifecycleOperation;
use Northpole\Lifecycle\LifecycleContext;

final class UninstallStage implements LifecycleStageContract
{
    public function name(): string
    {
        return 'uninstall-module';
    }

    public function priority(): int
    {
        return 200;
    }

    public function supports(
        LifecycleContext $context
    ): bool {
        return $context->operation()
            === LifecycleOperation::Uninstall;
    }

    public function handle(
        LifecycleContext $context
    ): void {
        $installation = $context->installation();

        if ($installation === null) {
            throw new LogicException(
                'The module installation must be resolved before uninstalling.'
            );
        }

        $installation->delete();

        $context->setInstallation(
            $installation
        );
    }
}
