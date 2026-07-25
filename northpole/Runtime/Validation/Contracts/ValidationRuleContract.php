<?php

declare(strict_types=1);

namespace Northpole\Runtime\Validation\Contracts;

use Northpole\Runtime\Validation\ValidationResult;

interface ValidationRuleContract
{
    public function name(): string;

    public function validate(): ValidationResult;
}