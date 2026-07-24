<?php

declare(strict_types=1);

namespace Northpole\Lifecycle\Exceptions;

use RuntimeException;

final class ModuleDependencyValidationException extends RuntimeException
{
    public static function unavailable(
        string $module,
        string $dependency
    ): self {
        return new self(
            sprintf(
                'Module [%s] requires dependency [%s], but it is not available in the marketplace.',
                $module,
                $dependency
            )
        );
    }

    public static function notInstalled(
        string $module,
        string $dependency
    ): self {
        return new self(
            sprintf(
                'Module [%s] requires dependency [%s] to be installed.',
                $module,
                $dependency
            )
        );
    }

    public static function notEnabled(
        string $module,
        string $dependency
    ): self {
        return new self(
            sprintf(
                'Module [%s] requires dependency [%s] to be enabled.',
                $module,
                $dependency
            )
        );
    }

    public static function requiredBy(
        string $module,
        string $dependant,
        string $operation
    ): self {
        return new self(
            sprintf(
                'Module [%s] cannot be %s because it is required by module [%s].',
                $module,
                $operation,
                $dependant
            )
        );
    }

    public static function selfReference(
        string $module
    ): self {
        return new self(
            sprintf(
                'Module [%s] cannot depend on itself.',
                $module
            )
        );
    }
}