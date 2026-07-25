<?php

declare(strict_types=1);

namespace Northpole\Runtime\Repair;

use Closure;
use InvalidArgumentException;
use Northpole\Runtime\Validation\ValidationIssue;
use Northpole\Runtime\Validation\ValidationResult;

final class RuntimeRepairEngine
{
    /**
     * @var array<
     *     string,
     *     Closure(ValidationIssue): RepairRecommendation|null
     * >
     */
    private array $resolvers = [];

    public function register(
        string $issueCode,
        Closure $resolver,
    ): self {
        $issueCode = trim($issueCode);

        if ($issueCode === '') {
            throw new InvalidArgumentException(
                'A repair resolver must have an issue code.',
            );
        }

        if (isset($this->resolvers[$issueCode])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Repair resolver [%s] is already registered.',
                    $issueCode,
                ),
            );
        }

        $this->resolvers[$issueCode] = $resolver;

        return $this;
    }

    public function has(
        string $issueCode,
    ): bool {
        return isset(
            $this->resolvers[trim($issueCode)],
        );
    }

    public function count(): int
    {
        return count($this->resolvers);
    }

    public function recommend(
        ValidationResult $validationResult,
    ): RepairResult {
        $result = new RepairResult;

        foreach ($validationResult->all() as $issue) {
            $resolver = $this->resolvers[
                $issue->code()
            ] ?? null;

            if ($resolver === null) {
                continue;
            }

            $recommendation = $resolver($issue);

            if ($recommendation === null) {
                continue;
            }

            $result->add($recommendation);
        }

        return $result;
    }

    public function clear(): self
    {
        $this->resolvers = [];

        return $this;
    }
}
