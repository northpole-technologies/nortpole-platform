<?php

declare(strict_types=1);

namespace Northpole\Runtime\Inspection\Contracts;

use Northpole\Runtime\Inspection\RuntimeInspectionReference;
use Northpole\Runtime\Inspection\RuntimeInspectionResult;

interface RuntimeInspectionServiceContract
{
    public function inspect(
        RuntimeInspectionReference $reference,
    ): ?RuntimeInspectionResult;
}
