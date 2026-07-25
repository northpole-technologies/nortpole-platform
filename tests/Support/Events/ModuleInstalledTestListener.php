<?php

declare(strict_types=1);

namespace Tests\Support\Events;

use Northpole\Runtime\Events\Contracts\ModuleEventContract;
use Northpole\Runtime\Events\Contracts\ModuleEventListenerContract;

final class ModuleInstalledTestListener implements ModuleEventListenerContract
{
    public static array $events = [];

    public function handle(
        ModuleEventContract $event,
    ): void {
        self::$events[] = $event;
    }
}
