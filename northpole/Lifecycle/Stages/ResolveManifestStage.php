<?php

declare(strict_types=1);

namespace Northpole\Lifecycle\Stages;

use Northpole\Lifecycle\Contracts\LifecycleStageContract;
use Northpole\Lifecycle\Exceptions\ModuleManifestNotFoundException;
use Northpole\Lifecycle\LifecycleContext;
use Northpole\Runtime\Runtime;

final class ResolveManifestStage implements LifecycleStageContract
{
    public function __construct(
        private readonly Runtime $runtime
    ) {
    }

    public function name(): string
    {
        return 'resolve_manifest';
    }

    public function priority(): int
    {
        return 150;
    }

    public function supports(
        LifecycleContext $context
    ): bool {
        return true;
    }

    public function handle(
        LifecycleContext $context
    ): void {
        $moduleKey = trim(
            (string) $context->module()->key
        );

        $manifest = $this->runtime->module(
            $moduleKey
        );

        if ($manifest === null) {
            throw ModuleManifestNotFoundException::forModule(
                $moduleKey
            );
        }

        $context->setManifest($manifest);
    }
}