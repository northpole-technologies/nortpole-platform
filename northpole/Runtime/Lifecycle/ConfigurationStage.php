<?php

declare(strict_types=1);

namespace Northpole\Runtime\Lifecycle;

use Northpole\Runtime\Configuration\ModuleConfigurationRegistrar;
use Northpole\Runtime\Contracts\BootStageContract;

final class ConfigurationStage implements BootStageContract
{
    public function __construct(
        private readonly ModuleConfigurationRegistrar $registrar,
    ) {}

    public function name(): string
    {
        return 'configuration-schema';
    }

    public function priority(): int
    {
        return 650;
    }

    public function boot(BootContext $context): void
    {
        $module = $context->module();

        $this->registrar->register(
            module: $module->slug(),
            settings: $module->settings(),
        );
    }
}