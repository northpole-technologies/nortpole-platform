<?php

declare(strict_types=1);

namespace Northpole\Runtime\Queries;

use DateTimeImmutable;
use InvalidArgumentException;
use Northpole\Runtime\Queries\Contracts\ModuleQueryContract;

final class ModuleQuery implements ModuleQueryContract
{
    private readonly DateTimeImmutable $requestedAt;

    /**
     * @param  array<string, mixed>  $parameters
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        private readonly string $name,
        private readonly string $sourceModule,
        private readonly array $parameters = [],
        private readonly array $metadata = [],
        ?DateTimeImmutable $requestedAt = null,
    ) {
        $this->requestedAt = $requestedAt
            ?? new DateTimeImmutable;

        $this->validate();
    }

    private function validate(): void
    {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'A module query name cannot be empty.'
            );
        }

        if (trim($this->sourceModule) === '') {
            throw new InvalidArgumentException(
                'A module query source module cannot be empty.'
            );
        }
    }

    public function name(): string
    {
        return trim($this->name);
    }

    public function sourceModule(): string
    {
        return trim($this->sourceModule);
    }

    /**
     * @return array<string, mixed>
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    public function requestedAt(): DateTimeImmutable
    {
        return $this->requestedAt;
    }
}
