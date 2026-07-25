<?php

declare(strict_types=1);

namespace Northpole\Runtime\Lifecycle;

use Northpole\Runtime\Contracts\BootStageContract;
use Northpole\Runtime\Notifications\ModuleNotificationRegistrar;

final class NotificationStage implements BootStageContract
{
    public function __construct(
        private readonly ModuleNotificationRegistrar $registrar,
    ) {}

    public function name(): string
    {
        return 'notifications';
    }

    public function priority(): int
    {
        return 660;
    }

    public function boot(BootContext $context): void
    {
        $module = $context->module();

        $this->registrar->register(
            module: $module->slug(),
            notifications: $module->notifications(),
        );
    }
}