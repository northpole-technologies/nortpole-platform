<?php

declare(strict_types=1);

namespace Northpole\Runtime\Repair\Contracts;

use Northpole\Runtime\Repair\RepairRecommendation;
use Northpole\Runtime\Validation\ValidationIssue;

interface RepairProviderContract
{
    /**
     * @return array<int, string>
     */
    public function issueCodes(): array;

    public function supports(
        ValidationIssue $issue,
    ): bool;

    public function recommend(
        ValidationIssue $issue,
    ): ?RepairRecommendation;
}