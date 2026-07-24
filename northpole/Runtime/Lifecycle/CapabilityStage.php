<?php

declare(strict_types=1);

namespace Northpole\Runtime\Lifecycle;

use InvalidArgumentException;
use Northpole\Runtime\Capabilities\Capability;
use Northpole\Runtime\Capabilities\CapabilityRegistry;
use Northpole\Runtime\Contracts\BootStageContract;

final class CapabilityStage implements BootStageContract
{
    public function __construct(
        private readonly CapabilityRegistry $registry,
    ) {
    }

    public function name(): string
    {
        return 'capabilities';
    }

    public function priority(): int
    {
        return 500;
    }

    public function boot(BootContext $context): void
    {
        $module = $context->module();

        foreach ($module->capabilities() as $capability) {
            if (
                ! is_string($capability)
                || trim($capability) === ''
            ) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Capabilities for module [%s] must be non-empty strings.',
                        $module->slug()
                    )
                );
            }

            $this->registry->add(
                Capability::fromString(
                    $module->slug(),
                    $capability,
                )
            );
        }
    }
}