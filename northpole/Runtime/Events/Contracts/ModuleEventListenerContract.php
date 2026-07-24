<?php

declare(strict_types=1);

namespace Northpole\Runtime\Events\Contracts;

interface ModuleEventListenerContract
{
    public function handle(
        ModuleEventContract $event,
    ): void;
}