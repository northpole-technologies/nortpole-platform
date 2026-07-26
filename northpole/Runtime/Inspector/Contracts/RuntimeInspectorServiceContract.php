<?php

declare(strict_types=1);

namespace Northpole\Runtime\Inspector\Contracts;

use Northpole\Runtime\Inspection\RuntimeInspectionReference;
use Northpole\Runtime\Inspector\RuntimeInspectorResult;

interface RuntimeInspectorServiceContract
{
    public function inspect(
        RuntimeInspectionReference $reference,
    ): RuntimeInspectorResult;
}
