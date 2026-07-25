<?php

declare(strict_types=1);

namespace Northpole\Runtime\Repair;

use InvalidArgumentException;

final class RepairResult
{
    /**
     * @var array<int, RepairRecommendation>
     */
    private array $recommendations = [];

    /**
     * @param  iterable<int, RepairRecommendation>  $recommendations
     */
    public function __construct(
        iterable $recommendations = [],
    ) {
        $this->addMany($recommendations);
    }

    public function add(
        RepairRecommendation $recommendation,
    ): self {
        $this->recommendations[] = $recommendation;

        return $this;
    }

    /**
     * @param  iterable<int, RepairRecommendation>  $recommendations
     */
    public function addMany(
        iterable $recommendations,
    ): self {
        foreach ($recommendations as $recommendation) {
            if (! $recommendation instanceof RepairRecommendation) {
                throw new InvalidArgumentException(
                    'Repair results may only contain repair recommendations.',
                );
            }

            $this->add($recommendation);
        }

        return $this;
    }

    /**
     * @return array<int, RepairRecommendation>
     */
    public function all(): array
    {
        $recommendations = $this->recommendations;

        usort(
            $recommendations,
            static fn (
                RepairRecommendation $first,
                RepairRecommendation $second,
            ): int =>
                $second->severity()->weight()
                <=>
                $first->severity()->weight(),
        );

        return $recommendations;
    }

    public function count(): int
    {
        return count($this->recommendations);
    }

    public function isEmpty(): bool
    {
        return $this->recommendations === [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (
                RepairRecommendation $recommendation,
            ): array => $recommendation->toArray(),
            $this->all(),
        );
    }
}
