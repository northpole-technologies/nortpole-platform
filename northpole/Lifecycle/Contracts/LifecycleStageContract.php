<?php

declare(strict_types=1);

namespace Northpole\Lifecycle\Contracts;

use Northpole\Lifecycle\LifecycleContext;

interface LifecycleStageContract
{
    /**
     * Human-readable and unique stage name.
     */
    public function name(): string;

    /**
     * Lower numbers execute first.
     */
    public function priority(): int;

    /**
     * Determine whether this stage applies to the current operation.
     */
    public function supports(
        LifecycleContext $context
    ): bool;

    /**
     * Execute the lifecycle stage.
     */
    public function handle(
        LifecycleContext $context
    ): void;
}
