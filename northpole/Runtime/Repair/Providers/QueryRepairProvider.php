<?php

declare(strict_types=1);

namespace Northpole\Runtime\Repair\Providers;

use Northpole\Runtime\Queries\Contracts\ModuleQueryHandlerContract;

final class QueryRepairProvider extends
    AbstractHandlerRepairProvider
{
    protected function type(): string
    {
        return 'query';
    }

    protected function displayName(): string
    {
        return 'query';
    }

    protected function contract(): string
    {
        return ModuleQueryHandlerContract::class;
    }
}