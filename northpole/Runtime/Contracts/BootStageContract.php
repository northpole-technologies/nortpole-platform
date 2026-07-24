<?php

namespace Northpole\Runtime\Contracts;

use Northpole\Runtime\Lifecycle\BootContext;

interface BootStageContract
{
    /**
     * Human readable stage name.
     */
    public function name(): string;

    /**
     * Lower numbers execute first.
     */
    public function priority(): int;

    /**
     * Execute this stage.
     */
    public function boot(BootContext $context): void;
}