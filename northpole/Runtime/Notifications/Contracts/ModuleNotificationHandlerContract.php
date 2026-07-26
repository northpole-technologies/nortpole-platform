<?php

declare(strict_types=1);

namespace Northpole\Runtime\Notifications\Contracts;

interface ModuleNotificationHandlerContract
{
    public function handle(
        ModuleNotificationContract $notification,
    ): mixed;
}