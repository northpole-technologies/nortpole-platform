<?php

declare(strict_types=1);

namespace Northpole\Lifecycle\Exceptions;

use RuntimeException;

final class ModuleManifestNotFoundException extends RuntimeException
{
    public static function forModule(string $moduleKey): self
    {
        return new self(
            sprintf(
                'The runtime manifest for module [%s] could not be found.',
                $moduleKey
            )
        );
    }
}