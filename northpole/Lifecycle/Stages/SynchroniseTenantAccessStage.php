<?php

declare(strict_types=1);

namespace Northpole\Lifecycle\Stages;

use Northpole\Lifecycle\Contracts\LifecycleStageContract;
use Northpole\Lifecycle\Enums\LifecycleOperation;
use Northpole\Lifecycle\LifecycleContext;
use Northpole\Runtime\Synchronisation\TenantAccessSynchroniser;

final class SynchroniseTenantAccessStage implements LifecycleStageContract
{
    public function __construct(
        private readonly TenantAccessSynchroniser $synchroniser,
    ) {
    }

    public function name(): string
    {
        return 'synchronise-tenant-access';
    }

    public function priority(): int
    {
        return 300;
    }

    public function supports(
        LifecycleContext $context
    ): bool {
        return in_array(
            $context->operation(),
            [
                LifecycleOperation::Install,
                LifecycleOperation::Enable,
            ],
            true,
        );
    }

    public function handle(
        LifecycleContext $context
    ): void {
        $this->synchroniser->synchronise(
            $context->organisation()
        );
    }
}