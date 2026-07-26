<?php

declare(strict_types=1);

namespace Northpole\Runtime\Relationships\Contracts;

use Northpole\Runtime\Inspection\RuntimeInspectionReference;
use Northpole\Runtime\Relationships\RuntimeRelationship;

interface RuntimeRelationshipResolverContract
{
    /**
     * @return array<int, RuntimeRelationship>
     */
    public function relationshipsFor(
        RuntimeInspectionReference $reference,
        mixed $metadata,
    ): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function dependenciesFor(
        RuntimeInspectionReference $reference,
    ): array;
}
