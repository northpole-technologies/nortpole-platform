<?php

declare(strict_types=1);

namespace Northpole\Runtime\Events\Subscribers;

use Northpole\Runtime\Events\Contracts\ModuleEventContract;
use Northpole\Runtime\Events\Contracts\ModuleEventListenerContract;

final class ModuleInstalledSubscriber implements ModuleEventListenerContract
{
    public function handle(
        ModuleEventContract $event,
    ): void {
        if ($event->name() !== 'module.installed') {
            return;
        }

        //
        // Platform reaction point.
        //
        // Future responsibilities:
        // - create audit entry
        // - initialise module resources
        // - refresh runtime state
        // - trigger platform notifications
        //
    }
}
