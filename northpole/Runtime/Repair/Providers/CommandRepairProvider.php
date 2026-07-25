<?php

declare(strict_types=1);

namespace Northpole\Runtime\Repair\Providers;

use Northpole\Runtime\Commands\Contracts\ModuleCommandHandlerContract;

final class CommandRepairProvider extends
    AbstractHandlerRepairProvider
{
    protected function type(): string
    {
        return 'command';
    }

    protected function displayName(): string
    {
        return 'command';
    }

    protected function contract(): string
    {
        return ModuleCommandHandlerContract::class;
    }
}