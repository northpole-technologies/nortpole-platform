<?php

declare(strict_types=1);

namespace Northpole\Runtime\Validation;

enum ValidationSeverity: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Error = 'error';

    public function weight(): int
    {
        return match ($this) {
            self::Info => 1,
            self::Warning => 10,
            self::Error => 100,
        };
    }
}