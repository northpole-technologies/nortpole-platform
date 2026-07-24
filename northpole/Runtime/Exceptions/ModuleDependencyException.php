<?php

declare(strict_types=1);

namespace Northpole\Runtime\Exceptions;

use RuntimeException;

final class ModuleDependencyException extends RuntimeException
{
    public static function missing(
        string $module,
        string $dependency,
    ): self {
        return new self(
            sprintf(
                'Module [%s] requires missing dependency [%s].',
                $module,
                $dependency,
            ),
        );
    }

    public static function disabled(
        string $module,
        string $dependency,
    ): self {
        return new self(
            sprintf(
                'Module [%s] requires disabled dependency [%s].',
                $module,
                $dependency,
            ),
        );
    }

    /**
     * @param array<int, string> $cycle
     */
    public static function circular(array $cycle): self
    {
        return new self(
            sprintf(
                'Circular module dependency detected: %s.',
                implode(' -> ', $cycle),
            ),
        );
    }
}