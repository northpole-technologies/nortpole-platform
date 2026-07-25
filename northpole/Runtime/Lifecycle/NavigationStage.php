<?php

declare(strict_types=1);

namespace Northpole\Runtime\Lifecycle;

use InvalidArgumentException;
use Northpole\Runtime\Contracts\BootStageContract;
use Northpole\Runtime\Navigation\NavigationItem;
use Northpole\Runtime\Navigation\NavigationRegistry;

final class NavigationStage implements BootStageContract
{
    public function __construct(
        private readonly NavigationRegistry $registry,
    ) {}

    public function name(): string
    {
        return 'navigation';
    }

    public function priority(): int
    {
        return 600;
    }

    public function boot(BootContext $context): void
    {
        $module = $context->module();

        foreach ($module->navigation() as $definition) {
            if (! is_array($definition)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Navigation entries for module [%s] must be objects.',
                        $module->slug()
                    )
                );
            }

            $this->registry->add(
                NavigationItem::fromArray(
                    $module->slug(),
                    $definition,
                )
            );
        }
    }
}
